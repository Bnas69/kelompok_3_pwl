<?php

namespace App\Services\HrAnalytics;

use App\Models\Employee;
use App\Models\HrDataSource;
use Illuminate\Support\Str;
use InvalidArgumentException;

class HrRowNormalizer
{
    public function normalize(array $row, ?HrDataSource $source = null, ?string $sourceName = null, ?string $sourceUrl = null): array
    {
        $riskLevel = $this->riskLevel($this->first($row, [
            'Attrition_Risk_Level',
            'attrition_risk_level',
            'risk_level',
            'Risk_Level',
            'Risk Level',
            'Attrition Risk Level',
            'risk_label',
            'Risk_Label',
            'Attrition_Risk_Label',
        ]));

        if (! in_array($riskLevel, [0, 1, 2], true)) {
            throw new InvalidArgumentException('Attrition_Risk_Level wajib bernilai 0, 1, atau 2.');
        }

        $employeeId = $this->string($this->first($row, ['Employee_ID', 'employee_id', 'Employee ID', 'id_karyawan', 'employee_code', 'staff_code']));
        $fullName = $this->string($this->first($row, ['Full_Name', 'full_name', 'Full Name', 'Employee_Name', 'employee_name', 'Employee Name', 'Name', 'nama']));
        $department = $this->resolveDepartment(
            $this->string($this->first($row, ['Department', 'department', 'Dept', 'dept', 'Divisi', 'division'])),
            $this->string($this->first($row, ['Job_Role', 'job_role', 'Job Role', 'Role', 'role', 'Position', 'position', 'jabatan']))
        );
        $jobRole = $this->string($this->first($row, ['Job_Role', 'job_role', 'Job Role', 'Role', 'role', 'Position', 'position', 'jabatan']));
        $sourceUrlValue = $this->string($this->first($row, ['source_url', 'Source_URL', 'SourceUrl', 'source']));

        $naturalKey = $employeeId ?: implode('|', [
            $fullName,
            $department,
            $jobRole,
            $this->first($row, ['Age', 'age']),
            $this->first($row, ['Monthly_Income', 'monthly_income']),
            $riskLevel,
        ]);

        return [
            'employee_id' => $employeeId ?: null,
            'full_name' => $fullName ?: ($employeeId ?: 'Karyawan tanpa nama'),
            'age' => $this->boundedInteger($this->first($row, ['Age', 'age']), 15, 80),
            'gender' => $this->string($this->first($row, ['Gender', 'gender', 'Jenis_Kelamin']), 40) ?: null,
            'department' => $department,
            'job_role' => $jobRole ?: 'Tidak diketahui',
            'education_level' => $this->boundedInteger($this->first($row, ['Education_Level', 'education_level', 'Education Level']), 0, 10),
            'monthly_income' => $this->decimal($this->first($row, ['Monthly_Income', 'monthly_income', 'Monthly Income', 'salary', 'gaji'])),
            'years_at_company' => $this->boundedInteger($this->first($row, ['Years_at_Company', 'years_at_company', 'Years at Company']), 0, 80),
            'total_working_years' => $this->boundedInteger($this->first($row, ['Total_Working_Years', 'total_working_years', 'Total Working Years', 'Years_Worked']), 0, 80),
            'monthly_work_hours' => $this->decimal($this->first($row, ['Avg_Monthly_Hours', 'monthly_work_hours', 'Monthly_Work_Hours', 'Monthly Work Hours', 'Average Monthly Hours'])),
            'projects_count' => $this->boundedInteger($this->first($row, ['Num_Projects', 'projects_count', 'Projects_Count', 'Projects Count', 'Number of Projects']), 0, 999),
            'job_satisfaction' => $this->decimal($this->first($row, ['Job_Satisfaction', 'job_satisfaction', 'Job Satisfaction'])),
            'work_life_balance' => $this->decimal($this->first($row, ['Work_Life_Balance', 'work_life_balance', 'Work Life Balance', 'Work-Life Balance'])),
            'overtime' => $this->boolean($this->first($row, ['Overtime', 'overtime', 'Over_Time'])),
            'attrition_risk_level' => $riskLevel,
            'attrition_risk_label' => Employee::RISK_LABELS[$riskLevel],
            'source_name' => $source?->name ?: ($sourceName ?: 'Manual Import'),
            'source_url' => $sourceUrlValue ?: ($source?->source_url ?: $sourceUrl),
            'imported_at' => now(),
            'synced_at' => now(),
            'unique_hash' => hash('sha256', strtolower($employeeId ? 'employee:'.$employeeId : 'row:'.$naturalKey)),
        ];
    }

    private function resolveDepartment(string $department, string $jobRole): string
    {
        if ($department !== '') {
            return $department;
        }

        return match ($jobRole) {
            'HR Specialist' => 'HR',
            'Data Scientist', 'Software Engineer' => 'IT',
            'Manager' => 'Operations',
            'Analyst' => 'Finance',
            'Sales Executive' => 'Sales',
            default => $jobRole !== '' ? $jobRole : 'Tidak diketahui',
        };
    }

    private function first(array $row, array $keys): mixed
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $row) && $row[$key] !== '') {
                return $row[$key];
            }
        }

        $lower = array_change_key_case($row, CASE_LOWER);
        foreach ($keys as $key) {
            $lookup = strtolower($key);
            if (array_key_exists($lookup, $lower) && $lower[$lookup] !== '') {
                return $lower[$lookup];
            }
        }

        $normalized = [];
        foreach ($row as $key => $value) {
            $normalized[$this->normalizedKey((string) $key)] = $value;
        }

        foreach ($keys as $key) {
            $lookup = $this->normalizedKey($key);
            if (array_key_exists($lookup, $normalized) && $normalized[$lookup] !== '') {
                return $normalized[$lookup];
            }
        }

        return null;
    }

    private function normalizedKey(string $key): string
    {
        return preg_replace('/[^a-z0-9]+/', '', strtolower($key)) ?: $key;
    }

    private function string(mixed $value, int $limit = 255): string
    {
        $clean = trim(strip_tags((string) ($value ?? '')));

        return Str::limit($clean, $limit, '');
    }

    private function integer(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return is_numeric($value) ? (int) $value : null;
    }

    private function riskLevel(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        $normalized = strtolower(trim((string) $value));

        return match ($normalized) {
            'low', 'low risk', 'rendah' => 0,
            'medium', 'medium risk', 'sedang' => 1,
            'high', 'high risk', 'tinggi' => 2,
            default => null,
        };
    }

    private function boundedInteger(mixed $value, int $min, int $max): ?int
    {
        $number = $this->integer($value);

        if ($number === null) {
            return null;
        }

        return $number >= $min && $number <= $max ? $number : null;
    }

    private function decimal(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        $normalized = str_replace(',', '.', (string) $value);

        return is_numeric($normalized) ? round((float) $normalized, 2) : null;
    }

    private function boolean(mixed $value): ?bool
    {
        if ($value === null || $value === '') {
            return null;
        }

        $normalized = strtolower(trim((string) $value));

        return match ($normalized) {
            '1', 'true', 'yes', 'y', 'ya', 'lembur' => true,
            '0', 'false', 'no', 'n', 'tidak' => false,
            default => null,
        };
    }
}
