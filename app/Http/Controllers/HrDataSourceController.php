<?php

namespace App\Http\Controllers;

use App\Concerns\ClearsDashboardCache;
use App\Models\HrDataSource;
use App\Services\HrAnalytics\HrDataSyncService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class HrDataSourceController extends Controller
{
    use ClearsDashboardCache;

    public function __construct(private readonly HrDataSyncService $syncService)
    {
    }

    public function index(): View
    {
        return view('hr.data-sources.index', [
            'sources' => HrDataSource::query()->latest()->paginate(15),
            'activeSources' => HrDataSource::query()->where('is_active', true)->count(),
            'inactiveSources' => HrDataSource::query()->where('is_active', false)->count(),
            'types' => HrDataSource::TYPES,
            'authTypes' => HrDataSource::AUTH_TYPES,
        ]);
    }

    public function create(): View
    {
        return view('hr.data-sources.form', [
            'source' => new HrDataSource(['is_active' => true, 'sync_interval_minutes' => 60, 'auth_type' => 'none']),
            'types' => HrDataSource::TYPES,
            'authTypes' => HrDataSource::AUTH_TYPES,
            'mode' => 'create',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        HrDataSource::query()->create($data);
        $this->clearDashboardCache();

        return redirect()->route('data-sources.index')->with('success', 'Sumber data HR berhasil ditambahkan.');
    }

    public function edit(HrDataSource $dataSource): View
    {
        return view('hr.data-sources.form', [
            'source' => $dataSource,
            'types' => HrDataSource::TYPES,
            'authTypes' => HrDataSource::AUTH_TYPES,
            'mode' => 'edit',
        ]);
    }

    public function update(Request $request, HrDataSource $dataSource): RedirectResponse
    {
        $data = $this->validated($request);
        if (($data['api_key'] ?? '') === '') {
            unset($data['api_key']);
        }

        $dataSource->update($data);
        $this->clearDashboardCache();

        return redirect()->route('data-sources.index')->with('success', 'Sumber data HR berhasil diperbarui.');
    }

    public function destroy(HrDataSource $dataSource): RedirectResponse
    {
        $dataSource->update(['is_active' => false]);
        $this->clearDashboardCache();

        return back()->with('success', 'Sumber data dinonaktifkan. Riwayat log tetap disimpan.');
    }

    public function status(Request $request, HrDataSource $dataSource): RedirectResponse
    {
        $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        $isActive = $request->boolean('is_active');
        $dataSource->update([
            'is_active' => $isActive,
            'last_status' => $isActive ? ($dataSource->last_status ?: 'ready') : 'disabled',
            'last_error' => $isActive ? $dataSource->last_error : null,
        ]);
        $this->clearDashboardCache();

        return back()->with('success', $isActive ? 'Sumber data diaktifkan.' : 'Sumber data dinonaktifkan.');
    }

    public function test(HrDataSource $dataSource): RedirectResponse
    {
        try {
            $this->syncService->testSource($dataSource);

            return back()->with('success', 'Koneksi berhasil. Format data terbaca.');
        } catch (\Throwable $exception) {
            $dataSource->update([
                'last_status' => 'test_failed',
                'last_error' => str($exception->getMessage())->limit(500, '')->toString(),
            ]);

            return back()->withErrors([
                'source' => 'Koneksi gagal. Cek data sumber.',
            ]);
        }
    }

    public function sync(HrDataSource $dataSource): RedirectResponse
    {
        if (! $dataSource->is_active) {
            return back()->with('error', 'Aktifkan sumber data sebelum menjalankan sync.');
        }

        $log = $this->syncService->syncSource($dataSource, config('hr_analytics.manual_sync_limit'));

        return back()->with(
            $log->status === 'failed' ? 'error' : 'success',
            $log->status === 'failed'
                ? 'Sync gagal.'
                : "Sync selesai. Masuk {$log->total_inserted}, update {$log->total_updated}, gagal {$log->total_failed}."
        );
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'type' => ['required', Rule::in(array_keys(HrDataSource::TYPES))],
            'source_url' => ['nullable', 'string', 'max:2048'],
            'auth_type' => ['required', Rule::in(array_keys(HrDataSource::AUTH_TYPES))],
            'api_key' => ['nullable', 'string', 'max:2048'],
            'is_active' => ['nullable', 'boolean'],
            'sync_interval_minutes' => ['required', 'integer', 'min:15', 'max:1440'],
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $this->validateSourceUrl($request, $data);

        return $data;
    }

    private function validateSourceUrl(Request $request, array $data): void
    {
        if ($data['type'] === 'local_csv_fallback') {
            return;
        }

        if (($data['source_url'] ?? '') === '') {
            $request->validate(['source_url' => ['required']]);
        }

        if (in_array($data['type'], ['csv_url', 'json_api', 'google_sheet_csv'], true)
            && ! filter_var($data['source_url'], FILTER_VALIDATE_URL)) {
            $request->validate(['source_url' => ['url']]);
        }

        if ($data['type'] === 'mysql_external' && (parse_url($data['source_url'], PHP_URL_SCHEME) !== 'mysql')) {
            throw ValidationException::withMessages([
                'source_url' => 'URL external MySQL harus memakai format mysql://host:3306/database?table=employees.',
            ]);
        }
    }
}
