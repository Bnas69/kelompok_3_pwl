<?php

use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\HrAnalyticsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['hr.auth', 'throttle:api'])->group(function (): void {
    Route::get('/hr-analytics', [HrAnalyticsController::class, 'analytics'])->name('api.hr-analytics');
    Route::get('/hr-analytics/kpi', [HrAnalyticsController::class, 'kpi'])->name('api.hr-analytics.kpi');
    Route::get('/hr-analytics/charts', [HrAnalyticsController::class, 'charts'])->name('api.hr-analytics.charts');
    Route::get('/hr-analytics/employees', [HrAnalyticsController::class, 'employees'])->name('api.hr-analytics.employees');
    Route::get('/hr-analytics/sync-logs', [HrAnalyticsController::class, 'syncLogs'])->name('api.hr-analytics.sync-logs');

    Route::get('/analytics/summary', [AnalyticsController::class, 'summary'])->name('api.analytics.summary');
    Route::get('/analytics/trend-data', [AnalyticsController::class, 'trendData'])->name('api.analytics.trend-data');
    Route::get('/analytics/sync-monthly', [AnalyticsController::class, 'syncMonthly'])->name('api.analytics.sync-monthly');
    Route::get('/analytics/department-risk', [AnalyticsController::class, 'departmentRisk'])->name('api.analytics.department-risk');
    Route::get('/analytics/risk-composition', [AnalyticsController::class, 'riskComposition'])->name('api.analytics.risk-composition');
    Route::get('/analytics/top-job-role-risk', [AnalyticsController::class, 'topJobRoleRisk'])->name('api.analytics.top-job-role-risk');
    Route::get('/analytics/job-satisfaction', [AnalyticsController::class, 'jobSatisfaction'])->name('api.analytics.job-satisfaction');
});
