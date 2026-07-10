<?php

namespace App\Services\HrAnalytics;

use App\Models\Employee;
use App\Models\HrDataSource;
use App\Models\HrSyncLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class HrDashboardService
{
    use \App\Concerns\RiskSumQueries;
    public function overview(): array
    {
        return Cache::remember('hr_dashboard_overview', config('hr_analytics.dashboard_cache_seconds'), function (): array {
            return [
                'metadata' => $this->metadata(),
                'kpi' => $this->kpi(),
                'charts' => $this->charts(),
                'tables' => [
                    'priority_employees' => $this->priorityEmployees(10),
                ],
                'filters' => $this->filters(),
                'sync' => $this->syncSummary(),
                'insights' => $this->insights(),
            ];
        });
    }

    public function kpi(): array
    {
        return Cache::remember('hr_dashboard_kpi', config('hr_analytics.dashboard_cache_seconds'), function (): array {
            // Use single query with raw SQL for better performance
            $stats = Employee::query()->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN attrition_risk_level = 2 THEN 1 ELSE 0 END) as high_risk,
                SUM(CASE WHEN attrition_risk_level = 1 THEN 1 ELSE 0 END) as medium_risk,
                SUM(CASE WHEN attrition_risk_level = 0 THEN 1 ELSE 0 END) as low_risk,
                SUM(CASE WHEN DATE(synced_at) = CURDATE() THEN 1 ELSE 0 END) as synced_today,
                AVG(monthly_income) as avg_monthly_income,
                AVG(monthly_work_hours) as avg_monthly_work_hours,
                AVG(job_satisfaction) as avg_job_satisfaction,
                AVG(work_life_balance) as avg_work_life_balance
            ')->first();

            $total = (int) $stats->total;
            $highRisk = (int) $stats->high_risk;
            $highRiskPercentage = $total > 0 ? round(($highRisk / $total) * 100, 2) : 0;
            
            $topDepartment = Employee::query()
                ->select('department')
                ->selectRaw($this->riskSumSql('high_risk', 2), $this->riskSumBindings(2))
                ->selectRaw('COUNT(*) as total')
                ->groupBy('department')
                ->orderByDesc('high_risk')
                ->limit(1)
                ->first();

            return [
                'total_employees' => $total,
                'synced_today' => (int) $stats->synced_today,
                'high_risk' => $highRisk,
                'medium_risk' => (int) $stats->medium_risk,
                'low_risk' => (int) $stats->low_risk,
                'high_risk_percentage' => $highRiskPercentage,
                'attrition_rate' => $highRiskPercentage,
                'avg_monthly_income' => round((float) ($stats->avg_monthly_income ?? 0), 2),
                'avg_monthly_work_hours' => round((float) ($stats->avg_monthly_work_hours ?? 0), 2),
                'avg_job_satisfaction' => round((float) ($stats->avg_job_satisfaction ?? 0), 2),
                'avg_work_life_balance' => round((float) ($stats->avg_work_life_balance ?? 0), 2),
                'top_department' => $topDepartment?->department ?: '-',
                'top_department_high_risk' => (int) ($topDepartment->high_risk ?? 0),
            ];
        });
    }

    public function charts(): array
    {
        return Cache::remember('hr_dashboard_charts', config('hr_analytics.dashboard_cache_seconds'), function (): array {
            return [
                'risk_distribution' => $this->riskDistribution(),
                'high_risk_by_role' => $this->highRiskByRole(),
                'workload_by_risk' => $this->workloadByRisk(),
                'satisfaction_by_risk' => $this->satisfactionByRisk(),
                'risk_by_age_group' => $this->riskByAgeGroup(),
                'department_risk' => $this->departmentRisk(),
                'monthly_sync_trend' => $this->monthlySyncTrend(),
            ];
        });
    }

    public function paginatedEmployees(array $filters): LengthAwarePaginator
    {
        return $this->employeeQuery($filters)
            ->orderByDesc('attrition_risk_level')
            ->orderBy('job_satisfaction')
            ->orderByDesc('monthly_work_hours')
            ->paginate(min((int) ($filters['per_page'] ?? 25), 100))
            ->through(fn (Employee $employee) => $this->employeeResource($employee));
    }

    public function employeeQuery(array $filters = []): Builder
    {
        return Employee::query()
            ->select([
                'id', 'employee_id', 'full_name', 'age', 'gender', 'department', 'job_role',
                'monthly_income', 'monthly_work_hours', 'projects_count', 
                'job_satisfaction', 'work_life_balance', 'attrition_risk_level', 'attrition_risk_label'
            ])
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $inner) use ($search): void {
                    $inner->where('employee_id', 'like', "%{$search}%")
                        ->orWhere('full_name', 'like', "%{$search}%")
                        ->orWhere('job_role', 'like', "%{$search}%");
                });
            })
            ->when(($filters['job_role'] ?? 'all') !== 'all', fn (Builder $query) => $query->where('job_role', $filters['job_role']))
            ->when(($filters['department'] ?? 'all') !== 'all', fn (Builder $query) => $query->where('department', $filters['department']))
            ->when(($filters['gender'] ?? 'all') !== 'all', fn (Builder $query) => $query->where('gender', $filters['gender']))
            ->when(($filters['risk_level'] ?? 'all') !== 'all', fn (Builder $query) => $query->where('attrition_risk_level', (int) $filters['risk_level']))
            ->when(($filters['age_group'] ?? 'all') !== 'all', fn (Builder $query) => $this->applyAgeGroup($query, $filters['age_group']))
            ->when(($filters['income_range'] ?? 'all') !== 'all', fn (Builder $query) => $this->applyIncomeRange($query, $filters['income_range']));
    }

    public function priorityEmployees(int $limit = 25): array
    {
        return Employee::query()
            ->highRisk()
            ->orderBy('job_satisfaction')
            ->orderBy('work_life_balance')
            ->orderByDesc('monthly_work_hours')
            ->limit($limit)
            ->get()
            ->map(fn (Employee $employee) => $this->employeeResource($employee))
            ->all();
    }

    public function syncLogs(int $limit = 15): array
    {
        return HrSyncLog::query()
            ->with('source:id,name,type')
            ->latest('started_at')
            ->limit($limit)
            ->get()
            ->map(fn (HrSyncLog $log) => [
                'id' => $log->id,
                'source_name' => $log->source?->name ?: 'Manual Import',
                'status' => $log->status,
                'total_found' => $log->total_found,
                'total_inserted' => $log->total_inserted,
                'total_updated' => $log->total_updated,
                'total_duplicate' => $log->total_duplicate,
                'total_failed' => $log->total_failed,
                'error_message' => $log->error_message,
                'started_at' => $log->started_at?->format('Y-m-d H:i:s'),
                'finished_at' => $log->finished_at?->format('Y-m-d H:i:s'),
            ])
            ->all();
    }

    public function filters(): array
    {
        return Cache::remember('hr_filters', 3600, function (): array {
            return [
                'job_roles' => Employee::query()->whereNotNull('job_role')->distinct()->orderBy('job_role')->pluck('job_role')->values(),
                'departments' => Employee::query()->whereNotNull('department')->distinct()->orderBy('department')->pluck('department')->values(),
                'genders' => Employee::query()->whereNotNull('gender')->distinct()->orderBy('gender')->pluck('gender')->values(),
                'risk_levels' => collect(Employee::RISK_LABELS)->map(fn (string $label, int $value) => ['value' => $value, 'label' => $label])->values()->all(),
                'age_groups' => ['< 25 Tahun', '25-34 Tahun', '35-44 Tahun', '45-54 Tahun', '55+ Tahun'],
                'income_ranges' => [
                    ['value' => 'lt_5000', 'label' => '< 5.000'],
                    ['value' => '5000_9999', 'label' => '5.000 - 9.999'],
                    ['value' => '10000_14999', 'label' => '10.000 - 14.999'],
                    ['value' => 'gte_15000', 'label' => '>= 15.000'],
                ],
            ];
        });
    }

    public function recommendation(Employee $employee): string
    {
        if ((int) $employee->attrition_risk_level !== 2) {
            return 'Monitoring rutin sesuai kebijakan HR';
        }

        if (($employee->monthly_work_hours ?? 0) >= 190 && ($employee->work_life_balance ?? 5) <= 2) {
            return 'Evaluasi beban kerja dan work-life balance';
        }

        if (($employee->job_satisfaction ?? 5) <= 2) {
            return 'Lakukan 1-on-1 meeting dan evaluasi kepuasan kerja';
        }

        if (($employee->monthly_income ?? 0) <= 5000) {
            return 'Evaluasi kompensasi dan benefit';
        }

        if (($employee->projects_count ?? 0) >= 5) {
            return 'Kurangi overload project';
        }

        return 'Monitoring berkala oleh HR';
    }

    private function metadata(): array
    {
        return [
            'title' => 'Human Resource Analytics',
            'dataset_rows' => Employee::query()->count(),
            'target' => 'Attrition_Risk_Level: 0 Low, 1 Medium, 2 High',
            'last_synced_at' => HrSyncLog::query()->whereIn('status', ['success', 'partial'])->latest('finished_at')->value('finished_at'),
            'data_source' => Employee::query()->exists() ? 'database' : 'database_empty_csv_fallback_available',
        ];
    }

    private function syncSummary(): array
    {
        $latest = HrSyncLog::query()->latest('started_at')->first();

        return [
            'last_sync' => $latest?->finished_at?->format('Y-m-d H:i:s'),
            'last_status' => $latest?->status ?: 'belum_ada_sync',
            'active_sources' => HrDataSource::query()->where('is_active', true)->count(),
            'total_sources' => HrDataSource::query()->count(),
        ];
    }

    private function riskDistribution(): array
    {
        $counts = Employee::query()
            ->select('attrition_risk_level', DB::raw('COUNT(*) as total'))
            ->groupBy('attrition_risk_level')
            ->pluck('total', 'attrition_risk_level');

        $total = (int) $counts->sum();

        return collect(Employee::RISK_LABELS)->map(fn (string $label, int $level) => [
            'level' => $level,
            'label' => $label,
            'count' => (int) ($counts[$level] ?? 0),
            'percentage' => $total > 0 ? round(((int) ($counts[$level] ?? 0) / $total) * 100, 2) : 0,
        ])->values()->all();
    }

    private function highRiskByRole(): array
    {
        return Employee::query()
            ->select('job_role as label', DB::raw('COUNT(*) as high'))
            ->highRisk()
            ->groupBy('job_role')
            ->orderByDesc('high')
            ->limit(10)
            ->get()
            ->map(fn ($row) => ['label' => $row->label ?: 'Tidak diketahui', 'high' => (int) $row->high])
            ->all();
    }

    private function workloadByRisk(): array
    {
        $rows = Employee::query()
            ->select('attrition_risk_level')
            ->selectRaw('AVG(monthly_work_hours) as hours, AVG(projects_count) as projects')
            ->groupBy('attrition_risk_level')
            ->get()
            ->keyBy('attrition_risk_level');

        return collect(Employee::RISK_LABELS)->map(fn (string $label, int $level) => [
            'level' => $level,
            'label' => $label,
            'hours' => round((float) ($rows[$level]->hours ?? 0), 2),
            'projects' => round((float) ($rows[$level]->projects ?? 0), 2),
        ])->values()->all();
    }

    private function satisfactionByRisk(): array
    {
        $rows = Employee::query()
            ->select('attrition_risk_level')
            ->selectRaw('AVG(job_satisfaction) as job_satisfaction, AVG(work_life_balance) as work_life_balance')
            ->groupBy('attrition_risk_level')
            ->get()
            ->keyBy('attrition_risk_level');

        return collect(Employee::RISK_LABELS)->map(fn (string $label, int $level) => [
            'level' => $level,
            'label' => $label,
            'job_satisfaction' => round((float) ($rows[$level]->job_satisfaction ?? 0), 2),
            'work_life_balance' => round((float) ($rows[$level]->work_life_balance ?? 0), 2),
        ])->values()->all();
    }

    private function riskByAgeGroup(): array
    {
        $case = $this->ageGroupCase();
        $rows = Employee::query()
            ->selectRaw("{$case} as label")
            ->selectRaw($this->riskSumSql('low', 0), $this->riskSumBindings(0))
            ->selectRaw($this->riskSumSql('medium', 1), $this->riskSumBindings(1))
            ->selectRaw($this->riskSumSql('high', 2), $this->riskSumBindings(2))
            ->selectRaw('COUNT(*) as total')
            ->groupBy('label')
            ->get()
            ->keyBy('label');

        return collect(['< 25 Tahun', '25-34 Tahun', '35-44 Tahun', '45-54 Tahun', '55+ Tahun'])
            ->map(fn (string $label) => [
                'label' => $label,
                'low' => (int) ($rows[$label]->low ?? 0),
                'medium' => (int) ($rows[$label]->medium ?? 0),
                'high' => (int) ($rows[$label]->high ?? 0),
                'total' => (int) ($rows[$label]->total ?? 0),
            ])
            ->all();
    }

    private function departmentRisk(): array
    {
        return Employee::query()
            ->select('department as label')
            ->selectRaw($this->riskSumSql('low', 0), $this->riskSumBindings(0))
            ->selectRaw($this->riskSumSql('medium', 1), $this->riskSumBindings(1))
            ->selectRaw($this->riskSumSql('high', 2), $this->riskSumBindings(2))
            ->selectRaw('COUNT(*) as total')
            ->groupBy('department')
            ->orderByDesc('high')
            ->limit(10)
            ->get()
            ->map(fn ($row) => [
                'label' => $row->label ?: 'Tidak diketahui',
                'low' => (int) $row->low,
                'medium' => (int) $row->medium,
                'high' => (int) $row->high,
                'total' => (int) $row->total,
            ])
            ->all();
    }

    private function monthlySyncTrend(): array
    {
        return Employee::query()
            ->selectRaw("DATE_FORMAT(COALESCE(synced_at, created_at), '%Y-%m') as period")
            ->selectRaw('COUNT(*) as total')
            ->selectRaw($this->riskSumSql('high', 2), $this->riskSumBindings(2))
            ->groupBy('period')
            ->orderBy('period')
            ->limit(24)
            ->get()
            ->map(fn ($row) => ['period' => $row->period, 'total' => (int) $row->total, 'high' => (int) $row->high])
            ->all();
    }

    private function insights(): array
    {
        $kpi = $this->kpi();
        $topRole = $this->highRiskByRole()[0] ?? ['label' => '-', 'high' => 0];

        return [
            "Total data aktif di database adalah {$kpi['total_employees']} karyawan. Dashboard membaca MySQL sebagai sumber utama, bukan CSV langsung.",
            "Persentase High Risk saat ini {$kpi['high_risk_percentage']}%. Kelompok ini menjadi prioritas intervensi HR.",
            "Job role dengan High Risk terbesar adalah {$topRole['label']} sebanyak {$topRole['high']} karyawan.",
            "Department paling perlu dipantau adalah {$kpi['top_department']} dengan {$kpi['top_department_high_risk']} karyawan High Risk.",
        ];
    }

    private function employeeResource(Employee $employee): array
    {
        return [
            'id' => $employee->id,
            'employee_id' => $employee->employee_id,
            'full_name' => $employee->full_name,
            'age' => $employee->age,
            'gender' => $employee->gender,
            'department' => $employee->department,
            'job_role' => $employee->job_role,
            'monthly_income' => (float) $employee->monthly_income,
            'monthly_work_hours' => (float) $employee->monthly_work_hours,
            'projects_count' => $employee->projects_count,
            'job_satisfaction' => (float) $employee->job_satisfaction,
            'work_life_balance' => (float) $employee->work_life_balance,
            'risk_level' => $employee->attrition_risk_level,
            'risk_label' => $employee->attrition_risk_label,
            'risk_tone' => $employee->risk_tone,
            'recommendation' => $this->recommendation($employee),
        ];
    }

    private function applyAgeGroup(Builder $query, string $group): Builder
    {
        return match ($group) {
            '< 25 Tahun' => $query->where('age', '<', 25),
            '25-34 Tahun' => $query->whereBetween('age', [25, 34]),
            '35-44 Tahun' => $query->whereBetween('age', [35, 44]),
            '45-54 Tahun' => $query->whereBetween('age', [45, 54]),
            '55+ Tahun' => $query->where('age', '>=', 55),
            default => $query,
        };
    }

    private function applyIncomeRange(Builder $query, string $range): Builder
    {
        return match ($range) {
            'lt_5000' => $query->where('monthly_income', '<', 5000),
            '5000_9999' => $query->whereBetween('monthly_income', [5000, 9999]),
            '10000_14999' => $query->whereBetween('monthly_income', [10000, 14999]),
            'gte_15000' => $query->where('monthly_income', '>=', 15000),
            default => $query,
        };
    }

    private function ageGroupCase(): string
    {
        return "CASE
            WHEN age < 25 THEN '< 25 Tahun'
            WHEN age BETWEEN 25 AND 34 THEN '25-34 Tahun'
            WHEN age BETWEEN 35 AND 44 THEN '35-44 Tahun'
            WHEN age BETWEEN 45 AND 54 THEN '45-54 Tahun'
            ELSE '55+ Tahun'
        END";
    }

}
