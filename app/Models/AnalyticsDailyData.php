<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalyticsDailyData extends Model
{
    protected $table = 'analytics_daily_data';

    protected $fillable = [
        'date',
        'year',
        'month',
        'day',
        'total_data',
        'growth_percentage',
    ];

    protected $casts = [
        'date' => 'date',
        'year' => 'integer',
        'month' => 'integer',
        'day' => 'integer',
        'total_data' => 'integer',
        'growth_percentage' => 'decimal:2',
    ];
}
