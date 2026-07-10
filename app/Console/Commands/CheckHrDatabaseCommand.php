<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Models\HrDataSource;
use App\Models\HrSyncLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Throwable;

class CheckHrDatabaseCommand extends Command
{
    protected $signature = 'hr:check-db';

    protected $description = 'Cek koneksi database MySQL server dan status data HR Analytics.';

    public function handle(): int
    {
        try {
            DB::connection()->getPdo();
            $this->info('Koneksi database: OK');
        } catch (Throwable $exception) {
            $this->error('Koneksi database: GAGAL');
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $latestLog = HrSyncLog::query()->latest('started_at')->first();

        $this->table(['Item', 'Nilai'], [
            ['Driver', Config::get('database.default')],
            ['Host', Config::get('database.connections.'.Config::get('database.default').'.host', '-')],
            ['Database', DB::connection()->getDatabaseName()],
            ['Total employees', Employee::query()->count()],
            ['Active data sources', HrDataSource::query()->where('is_active', true)->count()],
            ['Total data sources', HrDataSource::query()->count()],
            ['Last sync time', $latestLog?->finished_at?->format('Y-m-d H:i:s') ?: '-'],
            ['Last sync status', $latestLog?->status ?: '-'],
            ['Latest scheduler evidence', $latestLog ? 'hr:sync pernah berjalan via command/scheduler' : 'belum ada log sync'],
        ]);

        Artisan::call('migrate:status', ['--no-ansi' => true]);
        $this->line(Artisan::output());

        return self::SUCCESS;
    }
}
