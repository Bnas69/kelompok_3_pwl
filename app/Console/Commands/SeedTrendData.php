<?php

namespace App\Console\Commands;

use App\Models\Employee;
use Illuminate\Console\Command;

class SeedTrendData extends Command
{
    protected $signature = 'hr:seed-trend';

    protected $description = 'Sebar data synced_at ke 6 bulan terakhir agar grafik trend attrition tampil.';

    public function handle(): int
    {
        $total = Employee::count();
        if ($total === 0) {
            $this->error('Tidak ada data karyawan.');
            return self::FAILURE;
        }

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $months = [0, 1, 2, 3, 4, 5]; // 0 = current, 1 = last month, etc.

        Employee::query()->eachById(function (Employee $e, int $index) use ($months, $bar) {
            $monthOffset = $months[$index % count($months)];
            $e->synced_at = now()->subMonths($monthOffset)->startOfMonth()->addDays(min($index % 28, 27));
            $e->save();
            $bar->advance();
        });

        $bar->finish();
        $this->newLine();
        $this->info("Data trend attrition siap — {$total} karyawan tersebar di 6 bulan terakhir.");

        return self::SUCCESS;
    }
}
