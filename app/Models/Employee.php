<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    public const RISK_LABELS = [
        0 => 'Low Risk',
        1 => 'Medium Risk',
        2 => 'High Risk',
    ];

    protected $fillable = [
        'employee_id',
        'full_name',
        'age',
        'gender',
        'department',
        'job_role',
        'branch',
        'education_level',
        'marital_status',
        'hire_date',
        'termination_date',
        'monthly_income',
        'years_at_company',
        'total_working_years',
        'monthly_work_hours',
        'projects_count',
        'performance_rating',
        'job_satisfaction',
        'work_life_balance',
        'overtime',
        'work_mode',
        'training_hours',
        'absent_days',
        'promotion_last_2_years',
        'attrition_risk_level',
        'attrition_risk_label',
        'source_name',
        'source_url',
        'imported_at',
        'synced_at',
        'unique_hash',
    ];

    protected $casts = [
        'age' => 'integer',
        'education_level' => 'integer',
        'monthly_income' => 'decimal:2',
        'years_at_company' => 'integer',
        'total_working_years' => 'integer',
        'monthly_work_hours' => 'decimal:2',
        'projects_count' => 'integer',
        'performance_rating' => 'integer',
        'job_satisfaction' => 'decimal:2',
        'work_life_balance' => 'decimal:2',
        'overtime' => 'boolean',
        'training_hours' => 'integer',
        'absent_days' => 'integer',
        'promotion_last_2_years' => 'boolean',
        'attrition_risk_level' => 'integer',
        'hire_date' => 'date',
        'termination_date' => 'date',
        'imported_at' => 'datetime',
        'synced_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (Employee $employee): void {
            $level = (int) $employee->attrition_risk_level;
            $employee->attrition_risk_level = array_key_exists($level, self::RISK_LABELS) ? $level : 0;
            $employee->attrition_risk_label = self::RISK_LABELS[$employee->attrition_risk_level];
        });
    }

    public function scopeHighRisk(Builder $query): Builder
    {
        return $query->where('attrition_risk_level', 2);
    }

    public function getRiskToneAttribute(): string
    {
        return match ((int) $this->attrition_risk_level) {
            2 => 'Kritis',
            1 => 'Perlu Dipantau',
            default => 'Aman',
        };
    }
}
