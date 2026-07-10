<?php

namespace App\Console\Commands;

use App\Jobs\SyncHrDataJob;
use App\Models\HrDataSource;
use App\Models\HrSyncLog;
use Illuminate\Console\Command;

class SyncHrDataCommand extends Command
{
    protected $signature = 'hr:sync {source_id? : ID sumber data tertentu} {--force : Jalankan tanpa mengecek interval} {--queue : Kirim ke queue worker}';

    protected $description = 'Sinkronisasi data HR dari data source online/fallback ke database MySQL.';

    public function handle(): int
    {
        @ini_set('memory_limit', '256M');

        $sourceId = $this->argument('source_id') ? (int) $this->argument('source_id') : null;
        $force = (bool) $this->option('force');

        if ($sourceId && ! HrDataSource::query()->whereKey($sourceId)->exists()) {
            $this->error("Sumber data ID {$sourceId} tidak ditemukan.");

            return self::FAILURE;
        }

        $beforeId = HrSyncLog::query()->max('id') ?? 0;

        if ($this->option('queue')) {
            SyncHrDataJob::dispatch($sourceId, $force);
            $this->info('Sync HR dikirim ke queue. Jalankan worker: php artisan queue:work --tries=3 --timeout=120');

            return self::SUCCESS;
        }

        SyncHrDataJob::dispatchSync($sourceId, $force);

        $logs = HrSyncLog::query()
            ->with('source:id,name')
            ->where('id', '>', $beforeId)
            ->latest('id')
            ->get();

        if ($logs->isEmpty()) {
            $this->warn('Tidak ada sumber yang perlu disinkronkan. Gunakan --force jika ingin memaksa sync sekarang.');

            return self::SUCCESS;
        }

        $this->table(
            ['Source', 'Status', 'Found', 'Inserted', 'Updated', 'Duplicate', 'Failed', 'Error'],
            $logs->map(fn (HrSyncLog $log) => [
                $log->source?->name ?: 'Manual Import',
                $log->status,
                $log->total_found,
                $log->total_inserted,
                $log->total_updated,
                $log->total_duplicate,
                $log->total_failed,
                $log->error_message ? str($log->error_message)->limit(70)->toString() : '-',
            ])->all()
        );

        return $logs->contains('status', 'failed') ? self::FAILURE : self::SUCCESS;
    }
}
