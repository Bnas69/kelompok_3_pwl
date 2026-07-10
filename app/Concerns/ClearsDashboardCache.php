<?php

namespace App\Concerns;

use Illuminate\Support\Facades\Cache;

trait ClearsDashboardCache
{
    private const CACHE_KEYS = [
        'dashboard_summary',
        'dashboard_kpi',
        'dashboard_charts',
        'analytics_summary',
        'hr_dashboard_overview',
        'hr_dashboard_kpi',
        'hr_dashboard_charts',
        'hr_filters',
    ];

    public function clearDashboardCache(): void
    {
        foreach (self::CACHE_KEYS as $key) {
            Cache::forget($key);
        }
    }
}
