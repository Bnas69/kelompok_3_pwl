<?php

namespace App\Services\Analytics;

use App\Models\AnalyticsDailyData;
use App\Models\AnalyticsSyncLog;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Carbon\CarbonPeriod;

class AnalyticsGeneratorService
{
    private const START_YEAR = 2005;

    public function generateHistorical(?CarbonInterface $endDate = null): array
    {
        $start = CarbonImmutable::create(self::START_YEAR, 1, 1, 0, 0, 0, config('app.timezone'));
        $end = CarbonImmutable::instance($endDate ?? now())->startOfDay();

        $daily = $this->generateDailyRange($start, $end);
        $sync = $this->generateSyncMonths($start->startOfMonth(), $end->startOfMonth());

        return [
            'daily_created' => $daily['created'],
            'sync_created' => $sync['created'],
            'sync_updated' => $sync['updated'],
        ];
    }

    public function syncDaily(?CarbonInterface $date = null): array
    {
        $today = CarbonImmutable::instance($date ?? today())->startOfDay();
        $latestDate = AnalyticsDailyData::query()->max('date');
        $start = $latestDate
            ? CarbonImmutable::parse($latestDate)->addDay()
            : $today;

        $daily = $start->lte($today)
            ? $this->generateDailyRange($start, $today)
            : ['created' => 0];

        $sync = $this->generateSyncMonths($today->startOfMonth(), $today->startOfMonth(), true);

        return [
            'daily_created' => $daily['created'],
            'sync_created' => $sync['created'],
            'sync_updated' => $sync['updated'],
        ];
    }

    private function generateDailyRange(CarbonImmutable $start, CarbonImmutable $end): array
    {
        $existing = AnalyticsDailyData::query()
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->get(['date', 'total_data'])
            ->keyBy(fn (AnalyticsDailyData $row): string => $row->date->format('Y-m-d'));

        $previousTotal = $this->previousDailyTotal($start);
        $created = 0;

        foreach (CarbonPeriod::create($start, $end) as $date) {
            $day = CarbonImmutable::instance($date);
            $key = $day->format('Y-m-d');

            if ($existing->has($key)) {
                $previousTotal = (int) $existing[$key]->total_data;
                continue;
            }

            $total = $this->dailyTotal($day);

            AnalyticsDailyData::query()->create([
                'date' => $key,
                'year' => (int) $day->format('Y'),
                'month' => (int) $day->format('m'),
                'day' => (int) $day->format('d'),
                'total_data' => $total,
                'growth_percentage' => growth_percentage($total, $previousTotal),
            ]);

            $previousTotal = $total;
            $created++;
        }

        return ['created' => $created];
    }

    private function generateSyncMonths(CarbonImmutable $start, CarbonImmutable $end, bool $refreshCurrentMonth = false): array
    {
        $existing = AnalyticsSyncLog::query()
            ->whereBetween('sync_date', [$start->toDateString(), $end->toDateString()])
            ->get(['sync_date', 'total_sync'])
            ->keyBy(fn (AnalyticsSyncLog $row): string => $row->sync_date->format('Y-m-d'));

        $previousTotal = $this->previousSyncTotal($start);
        $created = 0;
        $updated = 0;
        $currentMonth = today()->startOfMonth()->toDateString();

        foreach (CarbonPeriod::create($start, '1 month', $end) as $monthDate) {
            $month = CarbonImmutable::instance($monthDate)->startOfMonth();
            $key = $month->toDateString();
            $payload = $this->monthlySyncPayload($month, $previousTotal);

            if ($existing->has($key)) {
                if ($refreshCurrentMonth && $key === $currentMonth) {
                    AnalyticsSyncLog::query()->whereDate('sync_date', $key)->update($payload);
                    $updated++;
                    $previousTotal = $payload['total_sync'];
                    continue;
                }

                $previousTotal = (int) $existing[$key]->total_sync;
                continue;
            }

            AnalyticsSyncLog::query()->create([
                'sync_date' => $key,
                ...$payload,
            ]);

            $previousTotal = $payload['total_sync'];
            $created++;
        }

        return ['created' => $created, 'updated' => $updated];
    }

    private function dailyTotal(CarbonImmutable $date): int
    {
        $yearOffset = max(0, ((int) $date->format('Y')) - self::START_YEAR);
        $weekdayFactor = $date->isWeekend() ? 0.68 : 1.06;
        $seasonFactor = 1 + (sin((((int) $date->format('m')) - 1) / 12 * 2 * pi()) * 0.11);
        $midMonthFactor = ((int) $date->format('d')) >= 10 && ((int) $date->format('d')) <= 24 ? 1.05 : 0.97;
        $base = 88 + ($yearOffset * 5.8);
        $noise = $this->stableNumber($date->format('Y-m-d').':daily', -18, 22);

        return max(25, (int) round(($base * $weekdayFactor * $seasonFactor * $midMonthFactor) + $noise));
    }

    private function monthlySyncPayload(CarbonImmutable $month, int $previousTotal): array
    {
        $today = today();
        $days = $month->isSameMonth($today) ? (int) $today->format('d') : $month->daysInMonth;
        $activityFactor = 0.82 + ($this->stableNumber($month->format('Y-m').':activity', 0, 30) / 100);
        $total = max(6, (int) round(($days * $activityFactor) + ($month->quarter * 2)));
        $failed = min($total - 1, max(0, (int) round($total * (0.03 + ($this->stableNumber($month->format('Y-m').':failed', 0, 5) / 100)))));
        $success = $total - $failed;

        return [
            'year' => (int) $month->format('Y'),
            'month' => (int) $month->format('m'),
            'total_sync' => $total,
            'success_sync' => $success,
            'failed_sync' => $failed,
            'growth_percentage' => growth_percentage($total, $previousTotal),
        ];
    }

    private function previousDailyTotal(CarbonImmutable $date): int
    {
        return (int) (AnalyticsDailyData::query()
            ->whereDate('date', '<', $date->toDateString())
            ->latest('date')
            ->value('total_data') ?? 0);
    }

    private function previousSyncTotal(CarbonImmutable $month): int
    {
        return (int) (AnalyticsSyncLog::query()
            ->whereDate('sync_date', '<', $month->toDateString())
            ->latest('sync_date')
            ->value('total_sync') ?? 0);
    }

    private function stableNumber(string $seed, int $min, int $max): int
    {
        $range = $max - $min + 1;

        return $min + (abs(crc32($seed)) % $range);
    }
}
