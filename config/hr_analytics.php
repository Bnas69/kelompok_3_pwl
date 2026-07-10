<?php

return [
    'fallback_csv_path' => env('HR_FALLBACK_CSV_PATH', 'public/data/hr_employee_attrition_data.csv'),
    'sync_limit' => (int) env('HR_SYNC_LIMIT', 15000),
    'manual_sync_limit' => (int) env('HR_MANUAL_SYNC_LIMIT', 3000),
    'http_timeout' => (int) env('HR_SYNC_HTTP_TIMEOUT', 20),
    'http_retry_times' => (int) env('HR_SYNC_HTTP_RETRY_TIMES', 2),
    'http_retry_sleep' => (int) env('HR_SYNC_HTTP_RETRY_SLEEP', 500),
    'scheduler_interval_minutes' => (int) env('HR_SYNC_INTERVAL_MINUTES', 60),
    // OPTIMIZATION: Reduced from 300s to 180s (3 minutes) for fresher data & better performance balance
    'dashboard_cache_seconds' => (int) env('HR_DASHBOARD_CACHE_SECONDS', 180),
    // OPTIMIZATION: New cache TTL for filter data (1 hour)
    'filter_cache_seconds' => (int) env('HR_FILTER_CACHE_SECONDS', 3600),
    // OPTIMIZATION: Pagination limits
    'pagination_per_page' => (int) env('HR_PAGINATION_PER_PAGE', 25),
    'pagination_max_per_page' => (int) env('HR_PAGINATION_MAX_PER_PAGE', 100),
];
