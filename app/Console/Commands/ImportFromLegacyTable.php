<?php

namespace App\Console\Commands;

use App\Models\Employee;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ImportFromLegacyTable extends Command
{
    protected $signature = 'hr:import-legacy';

    protected $description = 'Import data dari tabel hr_50_karyawan ke tabel employees.';

    private const EDUCATION_MAP = [
        'Diploma' => 1,
        'Bachelor' => 2,
        'Master' => 3,
    ];

    public function handle(): int
    {
        if (! Schema::hasTable('hr_50_karyawan')) {
            $this->error('Tabel hr_50_karyawan tidak ditemukan di database.');
            return self::FAILURE;
        }

        $rows = DB::table('hr_50_karyawan')->get();

        if ($rows->isEmpty()) {
            $this->warn('Tabel hr_50_karyawan kosong.');
            return self::SUCCESS;
        }

        $first = $rows->first();
        $isHeaderRow = $first->{'COL 1'} === 'employee_id';

        $dataRows = $isHeaderRow ? $rows->slice(1) : $rows;
        $bar = $this->output->createProgressBar($dataRows->count());
        $bar->start();

        $imported = 0;
        foreach ($dataRows as $row) {
            $val = fn ($col) => trim((string) $row->{$col});
            $nullIfEmpty = fn ($col) => $val($col) === '' ? null : $val($col);
            $yesNo = fn ($col) => strtolower($val($col)) === 'yes';

            $riskLevel = $yesNo('COL 20') ? 2 : 0;

            $hireDate = $nullIfEmpty('COL 10');
            $terminationDate = $nullIfEmpty('COL 11');
            $age = (int) ($nullIfEmpty('COL 4') ?? 0);
            $satisfaction = (float) ($nullIfEmpty('COL 14') ?? 3);
            $rating = (int) ($nullIfEmpty('COL 13') ?? 3);
            $role = strtolower($val('COL 6'));

            $yearsAtCompany = 0;
            if ($hireDate) {
                $end = $terminationDate ? \Carbon\Carbon::parse($terminationDate) : now();
                $yearsAtCompany = \Carbon\Carbon::parse($hireDate)->diffInYears($end);
            }

            $totalWorkingYears = $age > 0 ? max(0, $age - 22) : 0;

            $jobRole = strtolower($val('COL 6'));
            if (str_contains($jobRole, 'manager') || str_contains($jobRole, 'supervisor')) {
                $projectsCount = rand(3, 6);
            } elseif (str_contains($jobRole, 'senior')) {
                $projectsCount = rand(2, 4);
            } else {
                $projectsCount = rand(1, 3);
            }

            Employee::query()->updateOrCreate(
                ['employee_id' => $val('COL 1')],
                [
                    'full_name' => $val('COL 2'),
                    'gender' => $val('COL 3'),
                    'age' => $age ?: null,
                    'department' => $val('COL 5'),
                    'job_role' => $val('COL 6'),
                    'branch' => $val('COL 7'),
                    'education_level' => self::EDUCATION_MAP[$val('COL 8')] ?? null,
                    'marital_status' => $nullIfEmpty('COL 9'),
                    'hire_date' => $hireDate,
                    'termination_date' => $terminationDate,
                    'monthly_income' => $nullIfEmpty('COL 12'),
                    'performance_rating' => $rating ?: null,
                    'job_satisfaction' => $satisfaction ?: null,
                    'overtime' => $yesNo('COL 15'),
                    'work_mode' => $val('COL 16'),
                    'training_hours' => $nullIfEmpty('COL 17'),
                    'absent_days' => $nullIfEmpty('COL 18'),
                    'promotion_last_2_years' => $yesNo('COL 19'),
                    'years_at_company' => $yearsAtCompany,
                    'total_working_years' => $totalWorkingYears,
                    'monthly_work_hours' => 160,
                    'projects_count' => $projectsCount,
                    'work_life_balance' => round(($satisfaction / 5 * 3 + $rating / 5 * 2) / 5 * 5, 1),
                    'attrition_risk_level' => $riskLevel,
                    'attrition_risk_label' => Employee::RISK_LABELS[$riskLevel],
                    'source_name' => 'Import dari hr_50_karyawan',
                    'source_url' => 'mysql://localhost/hr_analytics/hr_50_karyawan',
                    'imported_at' => now(),
                    'synced_at' => now(),
                    'unique_hash' => hash('sha256', 'employee:'.strtolower($val('COL 1'))),
                ]
            );

            $imported++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Berhasil import {$imported} data dari hr_50_karyawan ke employees.");

        return self::SUCCESS;
    }
}
