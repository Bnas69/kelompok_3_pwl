<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrSyncLog extends Model
{
    protected $fillable = [
        'source_id',
        'status',
        'total_found',
        'total_inserted',
        'total_updated',
        'total_duplicate',
        'total_failed',
        'error_message',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'total_found' => 'integer',
        'total_inserted' => 'integer',
        'total_updated' => 'integer',
        'total_duplicate' => 'integer',
        'total_failed' => 'integer',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function source(): BelongsTo
    {
        return $this->belongsTo(HrDataSource::class, 'source_id');
    }
}
