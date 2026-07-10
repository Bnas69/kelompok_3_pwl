<?php

namespace App\Http\Controllers;

use App\Services\HrAnalytics\HrDashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class HrAnalyticsController extends Controller
{
    public function __construct(private readonly HrDashboardService $dashboard)
    {
    }

    public function index(): View
    {
        return view('dashboard');
    }

    public function analytics(): JsonResponse
    {
        return $this->safeJson(fn (): array => $this->cached('dashboard_summary', fn (): array => $this->dashboard->overview()));
    }

    public function kpi(): JsonResponse
    {
        return $this->safeJson(fn (): array => [
            'kpi' => $this->cached('dashboard_kpi', fn (): array => $this->dashboard->kpi()),
        ]);
    }

    public function charts(): JsonResponse
    {
        return $this->safeJson(fn (): array => [
            'charts' => $this->cached('dashboard_charts', fn (): array => $this->dashboard->charts()),
        ]);
    }

    public function employees(Request $request): JsonResponse
    {
        return $this->safeJson(fn (): array => [
            'employees' => $this->dashboard->paginatedEmployees($this->filters($request)),
        ]);
    }

    public function syncLogs(): JsonResponse
    {
        return $this->safeJson(fn (): array => [
            'logs' => $this->dashboard->syncLogs(30),
        ]);
    }

    private function filters(Request $request): array
    {
        return $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'job_role' => ['nullable', 'string', 'max:120'],
            'department' => ['nullable', 'string', 'max:120'],
            'gender' => ['nullable', 'string', 'max:40'],
            'risk_level' => ['nullable', 'in:all,0,1,2'],
            'age_group' => ['nullable', 'string', 'max:40'],
            'income_range' => ['nullable', 'string', 'max:40'],
            'per_page' => ['nullable', 'integer', 'min:10', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);
    }

    private function cached(string $key, callable $callback): array
    {
        return Cache::remember($key, now()->addMinutes(10), $callback);
    }

    private function safeJson(callable $callback): JsonResponse
    {
        try {
            return response()->json($callback());
        } catch (Throwable $exception) {
            Log::error('HR analytics API failed', [
                'message' => $exception->getMessage(),
                'exception' => $exception::class,
                'path' => request()->path(),
            ]);

            return response()->json([
                'message' => 'API HR Analytics gagal mengembalikan data JSON.',
                'error' => $this->publicErrorMessage($exception),
                'fallback' => $this->fallbackOverview($exception),
            ], 500);
        }
    }

    private function publicErrorMessage(Throwable $exception): string
    {
        $message = $exception->getMessage();

        if (! config('app.debug')) {
            return 'Terjadi kesalahan server. Silakan hubungi admin.';
        }

        return str($message)->limit(500, '')->toString();
    }

    private function fallbackOverview(?Throwable $exception = null): array
    {
        $errorMessage = $exception ? $this->publicErrorMessage($exception) : null;

        return [
            'metadata' => [
                'title' => 'Human Resource Analytics',
                'dataset_rows' => 0,
                'target' => 'Attrition_Risk_Level: 0 Low, 1 Medium, 2 High',
                'last_synced_at' => null,
                'data_source' => 'database_unavailable',
            ],
            'kpi' => [
                'total_employees' => 0,
                'synced_today' => 0,
                'high_risk' => 0,
                'medium_risk' => 0,
                'low_risk' => 0,
                'high_risk_percentage' => 0,
                'avg_monthly_income' => 0,
                'avg_monthly_work_hours' => 0,
                'avg_job_satisfaction' => 0,
                'avg_work_life_balance' => 0,
                'top_department' => '-',
                'top_department_high_risk' => 0,
            ],
            'charts' => [
                'risk_distribution' => [
                    ['level' => 0, 'label' => 'Low Risk', 'count' => 0, 'percentage' => 0],
                    ['level' => 1, 'label' => 'Medium Risk', 'count' => 0, 'percentage' => 0],
                    ['level' => 2, 'label' => 'High Risk', 'count' => 0, 'percentage' => 0],
                ],
                'high_risk_by_role' => [],
                'workload_by_risk' => [
                    ['level' => 0, 'label' => 'Low Risk', 'hours' => 0, 'projects' => 0],
                    ['level' => 1, 'label' => 'Medium Risk', 'hours' => 0, 'projects' => 0],
                    ['level' => 2, 'label' => 'High Risk', 'hours' => 0, 'projects' => 0],
                ],
                'satisfaction_by_risk' => [
                    ['level' => 0, 'label' => 'Low Risk', 'job_satisfaction' => 0, 'work_life_balance' => 0],
                    ['level' => 1, 'label' => 'Medium Risk', 'job_satisfaction' => 0, 'work_life_balance' => 0],
                    ['level' => 2, 'label' => 'High Risk', 'job_satisfaction' => 0, 'work_life_balance' => 0],
                ],
                'risk_by_age_group' => [],
                'department_risk' => [],
                'monthly_sync_trend' => [],
            ],
            'tables' => [
                'priority_employees' => [],
            ],
            'filters' => [
                'job_roles' => [],
                'departments' => [],
                'genders' => [],
                'risk_levels' => [
                    ['value' => 0, 'label' => 'Low Risk'],
                    ['value' => 1, 'label' => 'Medium Risk'],
                    ['value' => 2, 'label' => 'High Risk'],
                ],
                'age_groups' => ['< 25 Tahun', '25-34 Tahun', '35-44 Tahun', '45-54 Tahun', '55+ Tahun'],
                'income_ranges' => [
                    ['value' => 'lt_5000', 'label' => '< 5.000'],
                    ['value' => '5000_9999', 'label' => '5.000 - 9.999'],
                    ['value' => '10000_14999', 'label' => '10.000 - 14.999'],
                    ['value' => 'gte_15000', 'label' => '>= 15.000'],
                ],
            ],
            'sync' => [
                'last_sync' => null,
                'last_status' => 'database_unavailable',
                'active_sources' => 0,
                'total_sources' => 0,
                'logs' => [],
            ],
            'insights' => [
                'Dashboard belum dapat membaca database. Periksa konfigurasi .env, koneksi MySQL, dan migration.',
                'Jalankan php artisan hr:check-db untuk melihat status koneksi database.',
            ],
            'error' => [
                'message' => $errorMessage,
            ],
        ];
    }
}
