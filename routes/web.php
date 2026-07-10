<?php

use App\Http\Controllers\HrAnalyticsController;
use App\Http\Controllers\HrDataSourceController;
use App\Http\Controllers\HrExportController;
use App\Http\Controllers\HrImportController;
use App\Http\Controllers\HrPageController;
use App\Http\Controllers\HrSettingsController;
use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [LoginController::class, 'show'])->name('login');
Route::post('/login', [LoginController::class, 'authenticate'])
    ->middleware('hr.throttle.login')
    ->name('login.submit');

Route::middleware('hr.auth')->group(function (): void {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Semua role: dashboard, about, profile
    Route::get('/', [HrAnalyticsController::class, 'index'])->name('dashboard');
    Route::view('/tentang-project', 'hr.about')->name('about');
    Route::view('/profil-kelompok', 'hr.profile')->name('profile');

    // Admin + HRD: data karyawan, risiko, faktor
    Route::middleware('hr.auth:admin,hrd')->group(function (): void {
        Route::get('/data-karyawan', [HrPageController::class, 'employees'])->name('employees.index');
        Route::get('/risiko-turnover', [HrPageController::class, 'risks'])->name('risks.index');
        Route::get('/analisis-faktor', [HrPageController::class, 'factors'])->name('factors.index');
    });

    // Admin + HRD + Owner: exports & laporan
    Route::middleware('hr.auth:admin,hrd,owner')->group(function (): void {
        Route::get('/export-laporan', [HrExportController::class, 'index'])->name('exports.index');
        Route::get('/exports/employees.csv', [HrExportController::class, 'employeesCsv'])->name('exports.employees');
        Route::get('/exports/high-risk.csv', [HrExportController::class, 'highRiskCsv'])->name('exports.high-risk');
        Route::get('/exports/summary-pdf', [HrExportController::class, 'summaryPdf'])->name('exports.summary-pdf');
    });

    // Hanya Admin: settings, data-sources, import, sync-logs, CRUD karyawan
    Route::middleware('hr.auth:admin')->group(function (): void {
        Route::post('/data-karyawan', [HrPageController::class, 'storeEmployee'])
            ->middleware('throttle:10,1')
            ->name('employees.store');
        Route::put('/data-karyawan/{employee}', [HrPageController::class, 'updateEmployee'])
            ->middleware('throttle:10,1')
            ->name('employees.update');
        Route::delete('/data-karyawan/{employee}', [HrPageController::class, 'destroyEmployee'])
            ->middleware('throttle:10,1')
            ->name('employees.destroy');

        Route::get('/sync-logs', [HrPageController::class, 'logs'])->name('sync-logs.index');
        Route::get('/setting', [HrSettingsController::class, 'index'])->name('settings');
        Route::patch('/setting/profile', [HrSettingsController::class, 'updateProfile'])
            ->middleware('throttle:sensitive-action')
            ->name('settings.profile.update');
        Route::patch('/setting/password', [HrSettingsController::class, 'updatePassword'])
            ->middleware('throttle:sensitive-action')
            ->name('settings.password.update');
        Route::patch('/data-sources/{dataSource}/status', [HrDataSourceController::class, 'status'])
            ->middleware('throttle:sensitive-action')
            ->name('data-sources.status');
        Route::post('/data-sources/{dataSource}/sync', [HrDataSourceController::class, 'sync'])
            ->middleware('throttle:manual-sync')
            ->name('data-sources.sync');
        Route::resource('data-sources', HrDataSourceController::class)
            ->parameters(['data-sources' => 'dataSource'])
            ->except(['show']);
        Route::post('/data-sources/{dataSource}/test', [HrDataSourceController::class, 'test'])
            ->middleware('throttle:sensitive-action')
            ->name('data-sources.test');
        Route::get('/import', [HrImportController::class, 'index'])->name('import.index');
        Route::post('/import/upload', [HrImportController::class, 'upload'])
            ->middleware('throttle:10,1')
            ->name('import.upload');
        Route::post('/import/fallback', [HrImportController::class, 'importFallback'])
            ->middleware('throttle:manual-sync')
            ->name('import.fallback');
        Route::get('/download-template', [HrImportController::class, 'template'])->name('template.download');
    });
});
