<?php

namespace App\Services\Analytics;

use App\Models\AnalyticsDailyData;
use App\Models\AnalyticsSyncLog;
use App\Models\Employee;
use App\Models\HrSyncLog;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class AnalyticsDataService
{
    use \App\Concerns\RiskSumQueries;
    private const START_YEAR = 2005;

    public function summary(): array
    {
        $today = today();
        $currentMonthStart = $today->copy()->startOfMonth();
        $previousMonthStart = $today->copy()->subMonthNoOverflow()->startOfMonth();
        $previousMonthEnd = $previousMonthStart->copy()
            ->addDays(min((int) $today->format('d'), $previousMonthStart->daysInMonth) - 1)
            ->endOfDay();
        $currentYearStart = $today->copy()->startOfYear();
        $previousYearStart = $today->copy()->subYearNoOverflow()->startOfYear();
        $currentYearDay = ((int) $today->format('z')) + 1;
        $previousYearDays = $previousYearStart->isLeapYear() ? 366 : 365;
        $previousYearEnd = $previousYearStart->copy()
            ->addDays(min($currentYearDay, $previousYearDays) - 1)
            ->endOfDay();

        $monthTotal = $this->dailySum($currentMonthStart, $today);
        $previousMonthTotal = $this->dailySum($previousMonthStart, $previousMonthEnd);
        $yearTotal = $this->dailySum($currentYearStart, $today);
        $previousYearTotal = $this->dailySum($previousYearStart, $previousYearEnd);
        $currentMonthDays = max(1, AnalyticsDailyData::query()
            ->whereBetween('date', [$currentMonthStart->toDateString(), $today->toDateString()])
            ->count());
        $sync = AnalyticsSyncLog::query()
            ->where('year', (int) $today->format('Y'))
            ->where('month', (int) $today->format('m'))
            ->first();
        $latestSync = HrSyncLog::query()->latest('started_at')->first();
        $totalEmployees = Employee::query()->count();
        $highRisk = Employee::query()->where('attrition_risk_level', 2)->count();
        $avgSatisfaction = Employee::query()->whereNotNull('job_satisfaction')->avg('job_satisfaction');

        return [
            'cards' => [
                'total_employees' => $totalEmployees,
                'today_data' => (int) (AnalyticsDailyData::query()->whereDate('date', $today->toDateString())->value('total_data') ?? 0),
                'high_risk' => $highRisk,
                'high_risk_percentage' => $totalEmployees > 0 ? round(($highRisk / $totalEmployees) * 100, 2) : 0,
                'avg_job_satisfaction' => round((float) ($avgSatisfaction ?? 0), 2),
                'monthly_growth' => growth_percentage($monthTotal, $previousMonthTotal),
                'yearly_growth' => growth_percentage($yearTotal, $previousYearTotal),
                'daily_average' => round($monthTotal / $currentMonthDays, 2),
                'monthly_sync' => (int) ($sync->total_sync ?? 0),
                'sync_success' => (int) ($sync->success_sync ?? 0),
                'sync_failed' => (int) ($sync->failed_sync ?? 0),
                'last_sync' => $latestSync?->finished_at?->format('Y-m-d H:i:s') ?? '-',
            ],
            'last_updated_at' => $this->lastUpdatedAt(),
            'recent_employees' => $this->recentEmployees(),
        ];
    }

    public function trendData(array $filters): array
    {
        [$start, $end, $aggregation] = $this->trendPeriod($filters);
        $rows = AnalyticsDailyData::query()
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('date')
            ->get();

        $items = match ($aggregation) {
            'monthly' => $this->aggregateDailyRows($rows, 'month'),
            'yearly' => $this->aggregateDailyRows($rows, 'year'),
            default => $this->dailyItems($rows),
        };

        return [
            'filter' => $filters['filter'],
            'aggregation' => $aggregation,
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'items' => $items,
            'empty' => $items === [],
        ];
    }

    public function syncMonthly(array $filters): array
    {
        [$start, $end, $aggregation] = $this->syncPeriod($filters);
        $rows = AnalyticsSyncLog::query()
            ->whereBetween('sync_date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('sync_date')
            ->get();

        $items = $aggregation === 'yearly'
            ? $this->aggregateSyncRows($rows, 'year')
            : $this->monthlySyncItems($rows);

        return [
            'filter' => $filters['filter'],
            'aggregation' => $aggregation,
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'items' => $items,
            'empty' => $items === [],
        ];
    }

    public function departmentRisk(array $filters = []): array
    {
        $items = $this->filteredEmployees($filters)
            ->selectRaw("COALESCE(NULLIF(department, ''), 'Umum') as department")
            ->selectRaw($this->riskSumSql('high_risk', 2), $this->riskSumBindings(2))
            ->selectRaw($this->riskSumSql('medium_risk', 1), $this->riskSumBindings(1))
            ->groupBy('department')
            ->orderByDesc('high_risk')
            ->limit(8)
            ->get()
            ->map(fn ($row): array => [
                'department' => $row->department,
                'high_risk' => (int) $row->high_risk,
                'medium_risk' => (int) $row->medium_risk,
            ])
            ->all();

        return [
            'items' => $items,
            'empty' => $items === [],
        ];
    }

    public function riskComposition(array $filters = []): array
    {
        $counts = $this->filteredEmployees($filters)
            ->select('attrition_risk_level')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('attrition_risk_level')
            ->pluck('total', 'attrition_risk_level');

        $items = collect(Employee::RISK_LABELS)
            ->map(fn (string $label, int $level): array => [
                'level' => $level,
                'label' => $label,
                'total' => (int) ($counts[$level] ?? 0),
            ])
            ->values()
            ->all();

        return [
            'items' => $items,
            'empty' => collect($items)->sum('total') === 0,
        ];
    }

    public function topJobRoleRisk(array $filters = []): array
    {
        $items = $this->filteredEmployees($filters)
            ->where('attrition_risk_level', 2)
            ->whereNotNull('job_role')
            ->select('job_role')
            ->selectRaw('COUNT(*) as high_risk')
            ->groupBy('job_role')
            ->orderByDesc('high_risk')
            ->limit(5)
            ->get()
            ->map(fn ($row): array => [
                'job_role' => $row->job_role,
                'high_risk' => (int) $row->high_risk,
            ])
            ->all();

        return [
            'items' => $items,
            'empty' => $items === [],
        ];
    }

    public function jobSatisfaction(array $filters = []): array
    {
        $items = $this->filteredEmployees($filters)
            ->whereNotNull('job_satisfaction')
            ->selectRaw("COALESCE(NULLIF(department, ''), 'Umum') as department")
            ->selectRaw('AVG(job_satisfaction) as avg_job_satisfaction')
            ->groupBy('department')
            ->orderBy('avg_job_satisfaction')
            ->limit(8)
            ->get()
            ->map(fn ($row): array => [
                'department' => $row->department,
                'avg_job_satisfaction' => round((float) $row->avg_job_satisfaction, 2),
            ])
            ->all();

        return [
            'items' => $items,
            'empty' => $items === [],
        ];
    }

    private function dailyItems(Collection $rows): array
    {
        return $rows->map(fn (AnalyticsDailyData $row): array => [
            'period' => $row->date->toDateString(),
            'label' => $this->dateLabel($row->date),
            'total_data' => (int) $row->total_data,
            'growth_percentage' => (float) $row->growth_percentage,
            'status' => $this->growthStatus((float) $row->growth_percentage),
        ])->values()->all();
    }

    private function aggregateDailyRows(Collection $rows, string $mode): array
    {
        $groups = $rows->groupBy(fn (AnalyticsDailyData $row): string => $mode === 'year'
            ? (string) $row->year
            : $row->date->format('Y-m'));
        $previousTotal = 0;
        $items = [];

        foreach ($groups as $period => $groupRows) {
            $total = (int) $groupRows->sum('total_data');
            $growth = growth_percentage($total, $previousTotal);
            $items[] = [
                'period' => $period,
                'label' => $mode === 'year' ? $period : $this->monthLabel(CarbonImmutable::parse($period.'-01')),
                'total_data' => $total,
                'growth_percentage' => $growth,
                'status' => $this->growthStatus($growth),
            ];
            $previousTotal = $total;
        }

        return $items;
    }

    private function monthlySyncItems(Collection $rows): array
    {
        return $rows->map(fn (AnalyticsSyncLog $row): array => [
            'period' => $row->sync_date->format('Y-m'),
            'label' => $this->monthLabel($row->sync_date),
            'total_sync' => (int) $row->total_sync,
            'success_sync' => (int) $row->success_sync,
            'failed_sync' => (int) $row->failed_sync,
            'growth_percentage' => (float) $row->growth_percentage,
            'status' => $this->growthStatus((float) $row->growth_percentage),
        ])->values()->all();
    }

    private function aggregateSyncRows(Collection $rows, string $mode): array
    {
        $groups = $rows->groupBy(fn (AnalyticsSyncLog $row): string => $mode === 'year'
            ? (string) $row->year
            : $row->sync_date->format('Y-m'));
        $previousTotal = 0;
        $items = [];

        foreach ($groups as $period => $groupRows) {
            $total = (int) $groupRows->sum('total_sync');
            $success = (int) $groupRows->sum('success_sync');
            $failed = (int) $groupRows->sum('failed_sync');
            $growth = growth_percentage($total, $previousTotal);
            $items[] = [
                'period' => $period,
                'label' => $period,
                'total_sync' => $total,
                'success_sync' => $success,
                'failed_sync' => $failed,
                'growth_percentage' => $growth,
                'status' => $this->growthStatus($growth),
            ];
            $previousTotal = $total;
        }

        return $items;
    }

    private function recentEmployees(): array
    {
        return Employee::query()
            ->latest('synced_at')
            ->limit(8)
            ->get()
            ->map(fn (Employee $employee): array => [
                'name' => $employee->full_name ?: '-',
                'department' => $this->departmentName($employee),
                'job_role' => $employee->job_role ?: '-',
                'risk_label' => $employee->attrition_risk_label ?: '-',
                'risk_level' => (int) $employee->attrition_risk_level,
                'synced_at' => $employee->synced_at?->format('Y-m-d H:i:s') ?? '-',
            ])
            ->all();
    }

    private function trendPeriod(array $filters): array
    {
        $today = CarbonImmutable::today();
        $selected = $this->selectedPeriod($filters, $today);

        return match ($filters['filter']) {
            'daily' => $selected
                ? [$selected[0], $selected[1], 'daily']
                : [$today->subDays(29), $today, 'daily'],
            'monthly' => isset($filters['year'])
                ? [
                    CarbonImmutable::create((int) $filters['year'], 1, 1),
                    $this->maxEndDate(CarbonImmutable::create((int) $filters['year'], 12, 31), $today),
                    'monthly',
                ]
                : [$today->subMonthsNoOverflow(23)->startOfMonth(), $today, 'monthly'],
            'yearly' => [CarbonImmutable::create(self::START_YEAR, 1, 1), $today, 'yearly'],
            'date_range' => $this->dateRangePeriod($filters),
            default => [$today->subMonthsNoOverflow(23)->startOfMonth(), $today, 'monthly'],
        };
    }

    private function syncPeriod(array $filters): array
    {
        $today = CarbonImmutable::today();

        return match ($filters['filter']) {
            'yearly' => [CarbonImmutable::create(self::START_YEAR, 1, 1), $today, 'yearly'],
            'date_range' => $this->dateRangePeriod($filters, true),
            'monthly' => isset($filters['year'])
                ? [
                    CarbonImmutable::create((int) $filters['year'], 1, 1),
                    $this->maxEndDate(CarbonImmutable::create((int) $filters['year'], 12, 31), $today),
                    'monthly',
                ]
                : [$today->subMonthsNoOverflow(11)->startOfMonth(), $today, 'monthly'],
            default => [$today->subMonthsNoOverflow(11)->startOfMonth(), $today, 'monthly'],
        };
    }

    private function filteredEmployees(array $filters): Builder
    {
        $query = Employee::query();

        if (($filters['filter'] ?? null) === 'date_range') {
            $start = CarbonImmutable::parse($filters['start_date'])->startOfDay();
            $end = CarbonImmutable::parse($filters['end_date'])->endOfDay();

            return $query->whereBetween('synced_at', [$start, $end]);
        }

        if (isset($filters['year'])) {
            $query->whereYear('synced_at', (int) $filters['year']);
        }

        if (isset($filters['month'])) {
            $query->whereMonth('synced_at', (int) $filters['month']);
        }

        return $query;
    }

    private function selectedPeriod(array $filters, CarbonImmutable $today): ?array
    {
        if (! isset($filters['year'])) {
            return null;
        }

        $year = (int) $filters['year'];

        if (isset($filters['month'])) {
            $start = CarbonImmutable::create($year, (int) $filters['month'], 1);
            $end = $start->endOfMonth();

            return [$start, $this->maxEndDate($end, $today)];
        }

        $start = CarbonImmutable::create($year, 1, 1);
        $end = CarbonImmutable::create($year, 12, 31);

        return [$start, $this->maxEndDate($end, $today)];
    }

    private function maxEndDate(CarbonImmutable $end, CarbonImmutable $today): CarbonImmutable
    {
        return $end->greaterThan($today) ? $today : $end;
    }

    private function departmentName(Employee $employee): string
    {
        $department = trim((string) $employee->department);

        if ($department !== '' && ! in_array(strtolower($department), ['tidak diketahui', 'unknown', '-'], true)) {
            return $department;
        }

        $role = strtolower((string) $employee->job_role);

        return match (true) {
            str_contains($role, 'hr') => 'Human Resource',
            str_contains($role, 'sales') => 'Sales',
            str_contains($role, 'software'),
            str_contains($role, 'data'),
            str_contains($role, 'analyst') => 'Teknologi Data',
            str_contains($role, 'manager') => 'Manajemen',
            default => 'Umum',
        };
    }

    private function dateRangePeriod(array $filters, bool $forceMonthly = false): array
    {
        $start = CarbonImmutable::parse($filters['start_date'])->startOfDay();
        $end = CarbonImmutable::parse($filters['end_date'])->startOfDay();

        if ($forceMonthly) {
            return [$start->startOfMonth(), $end->endOfMonth(), 'monthly'];
        }

        $aggregation = $forceMonthly || $start->diffInDays($end) > 180 ? 'monthly' : 'daily';

        return [$start, $end, $aggregation];
    }

    private function dailySum(CarbonInterface $start, CarbonInterface $end): int
    {
        return (int) AnalyticsDailyData::query()
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->sum('total_data');
    }

    private function growthStatus(float $growth): string
    {
        if ($growth > 0.5) {
            return 'naik';
        }

        if ($growth < -0.5) {
            return 'turun';
        }

        return 'stabil';
    }

    private function lastUpdatedAt(): ?string
    {
        $daily = AnalyticsDailyData::query()->latest('updated_at')->value('updated_at');
        $sync = AnalyticsSyncLog::query()->latest('updated_at')->value('updated_at');
        $employee = Employee::query()->latest('updated_at')->value('updated_at');
        $hrSync = HrSyncLog::query()->latest('updated_at')->value('updated_at');

        return collect([$daily, $sync, $employee, $hrSync])
            ->filter()
            ->map(fn ($value) => CarbonImmutable::parse($value))
            ->sortDesc()
            ->first()
            ?->format('Y-m-d H:i:s');
    }

    private function dateLabel(CarbonInterface $date): string
    {
        return $date->format('d').' '.$this->monthName((int) $date->format('m')).' '.$date->format('Y');
    }

    private function monthLabel(CarbonInterface $date): string
    {
        return $this->monthName((int) $date->format('m')).' '.$date->format('Y');
    }

    private function monthName(int $month): string
    {
        return [
            1 => 'Jan',
            2 => 'Feb',
            3 => 'Mar',
            4 => 'Apr',
            5 => 'Mei',
            6 => 'Jun',
            7 => 'Jul',
            8 => 'Agu',
            9 => 'Sep',
            10 => 'Okt',
            11 => 'Nov',
            12 => 'Des',
        ][$month] ?? '-';
    }

}
