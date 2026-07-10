<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table): void {
            // Add composite indexes untuk query filtering yang sering digunakan
            $table->index(['attrition_risk_level', 'department'], 'idx_risk_department');
            $table->index(['job_role', 'attrition_risk_level'], 'idx_role_risk');
            $table->index(['department', 'attrition_risk_level'], 'idx_dept_risk');
            $table->index(['attrition_risk_level', 'job_satisfaction'], 'idx_risk_satisfaction');
            $table->index(['synced_at', 'attrition_risk_level'], 'idx_synced_risk');
            
            // Index untuk full-text search
            $table->fullText(['employee_id', 'full_name', 'job_role'], 'ft_employee_search');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table): void {
            $table->dropIndex('idx_risk_department');
            $table->dropIndex('idx_role_risk');
            $table->dropIndex('idx_dept_risk');
            $table->dropIndex('idx_risk_satisfaction');
            $table->dropIndex('idx_synced_risk');
            $table->dropFullText('ft_employee_search');
        });
    }
};
