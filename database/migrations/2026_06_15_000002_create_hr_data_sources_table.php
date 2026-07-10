<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_data_sources', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('type', 40)->index();
            $table->text('source_url')->nullable();
            $table->string('auth_type', 40)->default('none');
            $table->text('api_key')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedSmallInteger('sync_interval_minutes')->default(60);
            $table->timestamp('last_synced_at')->nullable();
            $table->string('last_status', 40)->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_data_sources');
    }
};
