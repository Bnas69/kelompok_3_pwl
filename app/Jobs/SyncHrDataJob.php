<?php

namespace App\Jobs;

use App\Models\HrDataSource;
use App\Services\HrAnalytics\HrDataSyncService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SyncHrDataJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 120;

    public function __construct(
        public readonly ?int $sourceId = null,
        public readonly bool $force = false,
    ) {
    }

    public function handle(HrDataSyncService $service): void
    {
        if ($this->sourceId) {
            $source = HrDataSource::query()->findOrFail($this->sourceId);
            $service->syncSource($source);

            return;
        }

        $service->syncAll($this->force);
    }
}
