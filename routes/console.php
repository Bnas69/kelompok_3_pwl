<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('about-hr', function () {
    $this->info('Human Resource Analytics - Kelompok 3');
    $this->line('Gunakan php artisan hr:sync untuk sinkronisasi data HR ke MySQL.');
});

Schedule::command('hr:sync')
    ->everyFifteenMinutes()
    ->withoutOverlapping()
    ->runInBackground();

Schedule::command('analytics:generate')
    ->dailyAt('00:05')
    ->withoutOverlapping();
