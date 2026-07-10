<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_sync_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('source_id')->nullable()->constrained('hr_data_sources')->nullOnDelete();
            $table->string('status', 40)->index();
            $table->unsignedInteger('total_found')->default(0);
            $table->unsignedInteger('total_inserted')->default(0);
            $table->unsignedInteger('total_updated')->default(0);
            $table->unsignedInteger('total_duplicate')->default(0);
            $table->unsignedInteger('total_failed')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_sync_logs');
    }
};
