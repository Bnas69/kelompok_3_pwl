<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_daily_data', function (Blueprint $table): void {
            $table->id();
            $table->date('date')->unique();
            $table->unsignedSmallInteger('year')->index();
            $table->unsignedTinyInteger('month')->index();
            $table->unsignedTinyInteger('day');
            $table->unsignedInteger('total_data');
            $table->decimal('growth_percentage', 8, 2)->default(0);
            $table->timestamps();

            $table->index(['year', 'month']);
        });

        Schema::create('analytics_sync_logs', function (Blueprint $table): void {
            $table->id();
            $table->date('sync_date')->unique();
            $table->unsignedSmallInteger('year')->index();
            $table->unsignedTinyInteger('month')->index();
            $table->unsignedInteger('total_sync');
            $table->unsignedInteger('success_sync');
            $table->unsignedInteger('failed_sync');
            $table->decimal('growth_percentage', 8, 2)->default(0);
            $table->timestamps();

            $table->index(['year', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_sync_logs');
        Schema::dropIfExists('analytics_daily_data');
    }
};
