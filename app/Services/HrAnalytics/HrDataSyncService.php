<?php

namespace App\Services\HrAnalytics;

use App\Models\Employee;
use App\Models\HrDataSource;
use App\Models\HrSyncLog;
use App\Services\HrAnalytics\Fetchers\CsvUrlHrFetcher;
use App\Services\HrAnalytics\Fetchers\ExternalMysqlHrFetcher;
use App\Services\HrAnalytics\Fetchers\JsonApiHrFetcher;
use App\Services\HrAnalytics\Fetchers\LocalCsvFallbackFetcher;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Throwable;

class HrDataSyncService
{
    public function __construct(
        private readonly HrRowNormalizer $normalizer,
        private readonly CsvUrlHrFetcher $csvUrlFetcher,
        private readonly JsonApiHrFetcher $jsonApiFetcher,
        private readonly LocalCsvFallbackFetcher $localCsvFallbackFetcher,
        private readonly ExternalMysqlHrFetcher $externalMysqlFetcher,
    ) {
    }

    public function syncAll(bool $force = false): array
    {
        $sources = HrDataSource::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->get();

        if ($sources->isEmpty()) {
            $this->ensureLocalFallbackSource();
            $sources = HrDataSource::query()->where('is_active', true)->orderBy('id')->get();
        }

        return $sources
            ->filter(fn (HrDataSource $source) => $force || $this->sourceIsDue($source))
            ->map(fn (HrDataSource $source) => $this->syncSource($source))
            ->all();
    }

    public function syncSource(HrDataSource $source, ?int $limit = null): HrSyncLog
    {
        $startedAt = now();
        $log = HrSyncLog::query()->create([
            'source_id' => $source->id,
            'status' => 'running',
            'started_at' => $startedAt,
        ]);

        try {
            $result = $this->persistRows($this->rowIterator($source, $limit), $source);
            $status = $result['total_failed'] > 0 ? 'partial' : 'success';

            $log->update([
                'status' => $status,
                'total_found' => $result['total_found'],
                'total_inserted' => $result['total_inserted'],
                'total_updated' => $result['total_updated'],
                'total_duplicate' => $result['total_duplicate'],
                'total_failed' => $result['total_failed'],
                'error_message' => $result['error_message'],
                'finished_at' => now(),
            ]);

            $source->update([
                'last_synced_at' => now(),
                'last_status' => $status,
                'last_error' => $result['error_message'],
            ]);

            $this->clearDashboardCache();

            Log::info('HR sync completed', [
                'source_id' => $source->id,
                'source_name' => $source->name,
                ...$result,
            ]);
        } catch (Throwable $exception) {
            $message = $this->safeError($exception);

            $log->update([
                'status' => 'failed',
                'error_message' => $message,
                'finished_at' => now(),
            ]);

            $source->update([
                'last_status' => 'failed',
                'last_error' => $message,
            ]);

            Log::error('HR sync failed', [
                'source_id' => $source->id,
                'source_name' => $source->name,
                'error' => $message,
            ]);
        }

        return $log->fresh();
    }

    public function testSource(HrDataSource $source): array
    {
        $rows = $this->fetchRows($source, 5);

        return [
            'ok' => true,
            'total_preview' => count($rows),
            'sample_columns' => array_keys($rows[0] ?? []),
        ];
    }

    public function importUploadedFile(UploadedFile $file): HrSyncLog
    {
        $startedAt = now();
        $log = HrSyncLog::query()->create([
            'status' => 'running',
            'started_at' => $startedAt,
        ]);

        try {
            $rows = $this->uploadedRows($file);
            $extension = strtoupper($file->getClientOriginalExtension() ?: 'CSV');
            $result = $this->persistRows($rows, null, "Import Manual {$extension}", $file->getClientOriginalName());
            $status = $result['total_failed'] > 0 ? 'partial' : 'success';

            $log->update([
                'status' => $status,
                'total_found' => $result['total_found'],
                'total_inserted' => $result['total_inserted'],
                'total_updated' => $result['total_updated'],
                'total_duplicate' => $result['total_duplicate'],
                'total_failed' => $result['total_failed'],
                'error_message' => $result['error_message'],
                'finished_at' => now(),
            ]);

            $this->clearDashboardCache();
        } catch (Throwable $exception) {
            $log->update([
                'status' => 'failed',
                'error_message' => $this->safeError($exception),
                'finished_at' => now(),
            ]);
        }

        return $log->fresh();
    }

    private function uploadedRows(UploadedFile $file): array
    {
        $realPath = $file->getRealPath();
        $extension = strtolower($file->getClientOriginalExtension());

        if ($realPath === false || !file_exists($realPath)) {
            throw new InvalidArgumentException('File tidak dapat dibaca.');
        }

        if ($extension === 'xlsx') {
            return $this->readXlsxRows($realPath, config('hr_analytics.sync_limit'));
        }

        return $this->localCsvFallbackFetcher->fromPath($realPath, config('hr_analytics.sync_limit'));
    }

    private function readXlsxRows(string $path, int $limit): array
    {
        if (! class_exists(\ZipArchive::class)) {
            throw new InvalidArgumentException('Import Excel membutuhkan ekstensi PHP zip.');
        }

        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) {
            throw new InvalidArgumentException('File Excel tidak dapat dibaca.');
        }

        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        if ($sheetXml === false) {
            $zip->close();
            throw new InvalidArgumentException('Sheet pertama tidak ditemukan di file Excel.');
        }

        $xml = simplexml_load_string($sheetXml);
        if (! $xml) {
            $zip->close();
            throw new InvalidArgumentException('Format Excel tidak valid.');
        }

        $sharedStrings = $this->readSharedStrings($zip);
        $headers = [];
        $rows = [];

        foreach ($xml->sheetData->row as $sheetRow) {
            $values = [];

            foreach ($sheetRow->c as $cell) {
                $values[$this->columnIndex((string) $cell['r'])] = $this->cellValue($cell, $sharedStrings);
            }

            if ($values === []) {
                continue;
            }

            if ($headers === []) {
                $headers = array_map(fn (mixed $value): string => trim((string) $value), $values);
                continue;
            }

            $row = [];
            foreach ($headers as $index => $header) {
                if ($header !== '') {
                    $row[$header] = $values[$index] ?? null;
                }
            }

            if (array_filter($row, fn (mixed $value): bool => $value !== null && $value !== '')) {
                $rows[] = $row;
            }

            if (count($rows) >= $limit) {
                break;
            }
        }

        $zip->close();

        return $rows;
    }

    private function readSharedStrings(\ZipArchive $zip): array
    {
        $sharedXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($sharedXml === false) {
            return [];
        }

        $xml = simplexml_load_string($sharedXml);
        if (! $xml) {
            return [];
        }

        $strings = [];
        foreach ($xml->si as $item) {
            if (isset($item->t)) {
                $strings[] = (string) $item->t;
                continue;
            }

            $text = '';
            foreach ($item->r as $run) {
                $text .= (string) $run->t;
            }
            $strings[] = $text;
        }

        return $strings;
    }

    private function cellValue(\SimpleXMLElement $cell, array $sharedStrings): string
    {
        $type = (string) $cell['t'];

        if ($type === 's') {
            return $sharedStrings[(int) $cell->v] ?? '';
        }

        if ($type === 'inlineStr') {
            return trim((string) ($cell->is->t ?? ''));
        }

        return trim((string) $cell->v);
    }

    private function columnIndex(string $cellReference): int
    {
        $letters = preg_replace('/[^A-Z]/', '', strtoupper($cellReference)) ?: 'A';
        $index = 0;

        foreach (str_split($letters) as $letter) {
            $index = ($index * 26) + (ord($letter) - 64);
        }

        return max(0, $index - 1);
    }

    public function ensureLocalFallbackSource(): HrDataSource
    {
        return HrDataSource::query()->updateOrCreate(
            ['name' => 'CSV Lokal Fallback HR Analytics'],
            [
                'type' => 'local_csv_fallback',
                'source_url' => config('hr_analytics.fallback_csv_path'),
                'auth_type' => 'none',
                'is_active' => true,
                'sync_interval_minutes' => config('hr_analytics.scheduler_interval_minutes'),
                'last_status' => 'ready',
                'last_error' => null,
            ]
        );
    }

    private function rowIterator(HrDataSource $source, ?int $limit = null): iterable
    {
        $limit ??= config('hr_analytics.sync_limit');

        if ($source->type === 'local_csv_fallback') {
            return $this->localCsvFallbackFetcher->iterate($source, $limit);
        }

        return $this->fetchRows($source, $limit);
    }

    private function fetchRows(HrDataSource $source, ?int $limit = null): array
    {
        $limit ??= config('hr_analytics.sync_limit');

        return match ($source->type) {
            'csv_url' => $this->csvUrlFetcher->fetch($source, $limit),
            'json_api' => $this->jsonApiFetcher->fetch($source, $limit),
            'google_sheet_csv' => $this->csvUrlFetcher->fetch($source, $limit),
            'mysql_external' => $this->externalMysqlFetcher->fetch($source, $limit),
            'local_csv_fallback' => $this->localCsvFallbackFetcher->fetch($source, $limit),
            default => throw new InvalidArgumentException('Tipe sumber data tidak didukung.'),
        };
    }

    private function persistRows(iterable $rows, ?HrDataSource $source = null, ?string $sourceName = null, ?string $sourceUrl = null): array
    {
        $totalFound = 0;
        $totalDuplicate = 0;
        $totalFailed = 0;
        $totalInserted = 0;
        $totalUpdated = 0;
        $errors = [];
        $seen = [];
        $batch = [];

        foreach ($rows as $row) {
            $totalFound++;

            try {
                $normalized = $this->normalizer->normalize($row, $source, $sourceName, $sourceUrl);

                if (isset($seen[$normalized['unique_hash']])) {
                    $totalDuplicate++;
                    continue;
                }

                $seen[$normalized['unique_hash']] = true;
                $batch[] = $normalized;

                if (count($batch) >= 200) {
                    $counts = $this->upsertBatch($batch);
                    $totalInserted += $counts['inserted'];
                    $totalUpdated += $counts['updated'];
                    $batch = [];
                }
            } catch (Throwable $exception) {
                $totalFailed++;
                if (count($errors) < 3) {
                    $errors[] = $this->safeError($exception);
                }
            }
        }

        if ($batch !== []) {
            $counts = $this->upsertBatch($batch);
            $totalInserted += $counts['inserted'];
            $totalUpdated += $counts['updated'];
        }

        return [
            'total_found' => $totalFound,
            'total_inserted' => $totalInserted,
            'total_updated' => $totalUpdated,
            'total_duplicate' => $totalDuplicate,
            'total_failed' => $totalFailed,
            'error_message' => $errors ? implode(' | ', array_unique($errors)) : null,
        ];
    }

    private function upsertBatch(array $chunk): array
    {
        $hashes = array_column($chunk, 'unique_hash');
        $existingHashes = Employee::query()
            ->whereIn('unique_hash', $hashes)
            ->pluck('unique_hash')
            ->flip();

        $inserted = 0;
        $updated = 0;

        foreach ($chunk as $row) {
            if ($existingHashes->has($row['unique_hash'])) {
                $updated++;
            } else {
                $inserted++;
            }
        }

        $timestamp = now();
        $payload = array_map(function (array $row) use ($timestamp): array {
            return [
                ...$row,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }, $chunk);

        Employee::query()->upsert($payload, ['unique_hash'], [
            'employee_id',
            'full_name',
            'age',
            'gender',
            'department',
            'job_role',
            'education_level',
            'monthly_income',
            'years_at_company',
            'total_working_years',
            'monthly_work_hours',
            'projects_count',
            'job_satisfaction',
            'work_life_balance',
            'overtime',
            'attrition_risk_level',
            'attrition_risk_label',
            'source_name',
            'source_url',
            'imported_at',
            'synced_at',
            'updated_at',
        ]);

        return [
            'inserted' => $inserted,
            'updated' => $updated,
        ];
    }

    private function sourceIsDue(HrDataSource $source): bool
    {
        if (! $source->last_synced_at) {
            return true;
        }

        return $source->last_synced_at
            ->addMinutes($source->sync_interval_minutes)
            ->lte(now());
    }

    private function clearDashboardCache(): void
    {
        foreach (['dashboard_summary', 'dashboard_kpi', 'dashboard_charts', 'analytics_summary', 'hr_dashboard_overview', 'hr_dashboard_kpi', 'hr_dashboard_charts', 'hr_filters'] as $key) {
            Cache::forget($key);
        }
    }

    private function safeError(Throwable $exception): string
    {
        return str($exception->getMessage())->limit(500, '')->toString();
    }
}
