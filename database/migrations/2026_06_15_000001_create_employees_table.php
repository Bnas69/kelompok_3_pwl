<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table): void {
            $table->id();
            $table->string('employee_id')->nullable()->unique();
            $table->string('full_name')->nullable();
            $table->unsignedTinyInteger('age')->nullable()->index();
            $table->string('gender', 40)->nullable();
            $table->string('department')->nullable()->index();
            $table->string('job_role')->nullable()->index();
            $table->unsignedTinyInteger('education_level')->nullable();
            $table->decimal('monthly_income', 14, 2)->nullable();
            $table->unsignedSmallInteger('years_at_company')->nullable();
            $table->unsignedSmallInteger('total_working_years')->nullable();
            $table->decimal('monthly_work_hours', 8, 2)->nullable();
            $table->unsignedSmallInteger('projects_count')->nullable();
            $table->decimal('job_satisfaction', 4, 2)->nullable();
            $table->decimal('work_life_balance', 4, 2)->nullable();
            $table->boolean('overtime')->nullable();
            $table->unsignedTinyInteger('attrition_risk_level')->default(0)->index();
            $table->string('attrition_risk_label', 40)->default('Low Risk');
            $table->string('source_name')->nullable();
            $table->text('source_url')->nullable();
            $table->timestamp('imported_at')->nullable();
            $table->timestamp('synced_at')->nullable()->index();
            $table->string('unique_hash', 64)->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
