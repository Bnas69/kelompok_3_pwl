<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HrDataSource extends Model
{
    public const TYPES = [
        'csv_url' => 'CSV URL',
        'json_api' => 'JSON API',
        'google_sheet_csv' => 'Google Sheets CSV',
        'mysql_external' => 'External MySQL',
        'local_csv_fallback' => 'Local CSV Fallback',
    ];

    public const AUTH_TYPES = [
        'none' => 'Tanpa Auth',
        'bearer' => 'Bearer Token',
        'query_key' => 'Query API Key',
        'basic' => 'Basic Auth',
    ];

    protected $fillable = [
        'name',
        'type',
        'source_url',
        'auth_type',
        'api_key',
        'is_active',
        'sync_interval_minutes',
        'last_synced_at',
        'last_status',
        'last_error',
    ];

    protected $hidden = [
        'api_key',
    ];

    protected $casts = [
        'api_key' => 'encrypted',
        'is_active' => 'boolean',
        'sync_interval_minutes' => 'integer',
        'last_synced_at' => 'datetime',
    ];

    public function logs(): HasMany
    {
        return $this->hasMany(HrSyncLog::class, 'source_id');
    }
}
