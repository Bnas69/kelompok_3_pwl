<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Services\HrAnalytics\HrDataSyncService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;

class HrImportController extends Controller
{
    public function __construct(private readonly HrDataSyncService $syncService)
    {
    }

    public function index(): View
    {
        return view('hr.import', [
            'totalEmployees' => Employee::query()->count(),
            'fallbackPath' => config('hr_analytics.fallback_csv_path'),
        ]);
    }

    public function importFallback(): RedirectResponse
    {
        $source = $this->syncService->ensureLocalFallbackSource();
        $log = $this->syncService->syncSource($source, config('hr_analytics.manual_sync_limit'));

        return back()->with(
            $log->status === 'failed' ? 'error' : 'success',
            $log->status === 'failed'
                ? 'Import gagal.'
                : "Import selesai. Masuk {$log->total_inserted}, update {$log->total_updated}, gagal {$log->total_failed}."
        );
    }

    public function upload(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'hr_file' => ['required', 'file', 'mimes:csv,txt,xlsx', 'extensions:csv,txt,xlsx', 'max:51200'],
        ], [
            'hr_file.required' => 'File HR wajib dipilih.',
            'hr_file.mimes' => 'File harus berformat CSV, TXT, atau XLSX.',
            'hr_file.extensions' => 'Ekstensi file harus .csv, .txt, atau .xlsx.',
            'hr_file.max' => 'Ukuran file maksimal 50 MB.',
        ]);

        $log = $this->syncService->importUploadedFile($data['hr_file']);

        return back()->with(
            $log->status === 'failed' ? 'error' : 'success',
            $log->status === 'failed'
                ? 'Import gagal.'
                : "Import selesai. Masuk {$log->total_inserted}, update {$log->total_updated}, gagal {$log->total_failed}."
        );
    }

    public function template()
    {
        $headers = [
            'Employee_ID',
            'Full_Name',
            'Age',
            'Gender',
            'Department',
            'Job_Role',
            'Monthly_Income',
            'Job_Satisfaction',
            'Work_Life_Balance',
            'Num_Projects',
            'Avg_Monthly_Hours',
            'Years_at_Company',
            'Total_Working_Years',
            'Education_Level',
            'Overtime',
            'Attrition_Risk_Level',
        ];

        return Response::streamDownload(function () use ($headers): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);
            fclose($handle);
        }, 'template_hr_employees.csv', ['Content-Type' => 'text/csv']);
    }
}
