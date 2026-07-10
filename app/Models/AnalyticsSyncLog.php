<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalyticsSyncLog extends Model
{
    protected $table = 'analytics_sync_logs';

    protected $fillable = [
        'sync_date',
        'year',
        'month',
        'total_sync',
        'success_sync',
        'failed_sync',
        'growth_percentage',
    ];

    protected $casts = [
        'sync_date' => 'date',
        'year' => 'integer',
        'month' => 'integer',
        'total_sync' => 'integer',
        'success_sync' => 'integer',
        'failed_sync' => 'integer',
        'growth_percentage' => 'decimal:2',
    ];
}
