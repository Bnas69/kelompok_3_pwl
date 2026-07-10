<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table): void {
            $table->string('branch')->nullable()->after('job_role');
            $table->string('marital_status', 40)->nullable()->after('education_level');
            $table->date('hire_date')->nullable()->after('marital_status');
            $table->date('termination_date')->nullable()->after('hire_date');
            $table->unsignedTinyInteger('performance_rating')->nullable()->after('projects_count');
            $table->string('work_mode', 40)->nullable()->after('overtime');
            $table->unsignedSmallInteger('training_hours')->nullable()->after('work_mode');
            $table->unsignedSmallInteger('absent_days')->nullable()->after('training_hours');
            $table->boolean('promotion_last_2_years')->nullable()->after('absent_days');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table): void {
            $table->dropColumn(['branch', 'marital_status', 'hire_date', 'termination_date', 'performance_rating', 'work_mode', 'training_hours', 'absent_days', 'promotion_last_2_years']);
        });
    }
};
