<?php

use Illuminate\Support\Facades\Facade;
use Illuminate\Support\ServiceProvider;

return [
    'name' => env('APP_NAME', 'Human Resource Analytics Dashboard'),
    'env' => env('APP_ENV', 'production'),
    'debug' => (bool) env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost'),
    'asset_url' => env('ASSET_URL'),
    'timezone' => 'Asia/Jakarta',
    'locale' => 'id',
    'fallback_locale' => 'en',
    'faker_locale' => 'id_ID',
    'cipher' => 'AES-256-CBC',
    'key' => env('APP_KEY'),
    'version' => env('APP_VERSION', '1.0.0'),
    'study_program' => env('APP_STUDY_PROGRAM', 'Teknik Informatika'),
    'academic_year' => env('APP_ACADEMIC_YEAR', '2025/2026'),
    'login_username' => env('LOGIN_USERNAME', 'admin'),
    'login_password_hash' => env('LOGIN_PASSWORD_HASH', ''),
    'login_role' => env('LOGIN_ROLE', 'admin'),
    'login_display_name' => env('LOGIN_DISPLAY_NAME', 'Admin Kelompok 3'),
    'login_email' => env('LOGIN_EMAIL'),
    'previous_keys' => [
        ...array_filter(explode(',', (string) env('APP_PREVIOUS_KEYS', ''))),
    ],
    'maintenance' => [
        'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),
        'store' => env('APP_MAINTENANCE_STORE', 'database'),
    ],
    'providers' => ServiceProvider::defaultProviders()->merge([
        App\Providers\AppServiceProvider::class,
    ])->toArray(),
    'aliases' => Facade::defaultAliases()->toArray(),
];
