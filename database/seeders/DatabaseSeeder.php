<?php

namespace Database\Seeders;

use App\Models\HrDataSource;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        HrDataSource::query()->updateOrCreate(
            ['name' => 'CSV Lokal Fallback HR Analytics'],
            [
                'type' => 'local_csv_fallback',
                'source_url' => config('hr_analytics.fallback_csv_path'),
                'auth_type' => 'none',
                'is_active' => true,
                'sync_interval_minutes' => config('hr_analytics.scheduler_interval_minutes'),
                'last_status' => 'ready',
                'last_error' => null,
            ]
        );

        Artisan::call('hr:sync', ['--force' => true]);
    }
}
