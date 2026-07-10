<?php

namespace App\Http\Controllers;

use App\Concerns\ClearsDashboardCache;
use App\Http\Requests\EmployeeRequest;
use App\Models\Employee;
use App\Models\HrSyncLog;
use App\Services\HrAnalytics\HrDashboardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class HrPageController extends Controller
{
    use ClearsDashboardCache;

    public function __construct(private readonly HrDashboardService $dashboard)
    {
    }

    public function employees(): View
    {
        return view('hr.employees', [
            'employees' => $this->dashboard->paginatedEmployees(request()->all()),
            'filters' => $this->dashboard->filters(),
        ]);
    }

    public function risks(): View
    {
        return view('hr.risks', ['overview' => $this->dashboard->overview()]);
    }

    public function factors(): View
    {
        return view('hr.factors', ['overview' => $this->dashboard->overview()]);
    }

    public function logs(): View
    {
        return view('hr.sync-logs', [
            'logs' => HrSyncLog::query()->with('source:id,name,type')->latest('started_at')->paginate(20),
        ]);
    }

    public function storeEmployee(EmployeeRequest $request): RedirectResponse
    {
        $payload = $this->employeePayload($request->validated());

        Employee::query()->updateOrCreate(
            ['employee_id' => $payload['employee_id']],
            $payload
        );

        $this->clearDashboardCache();

        return back()->with('success', 'Data karyawan berhasil disimpan ke MySQL.');
    }

    public function updateEmployee(EmployeeRequest $request, Employee $employee): RedirectResponse
    {
        $employee->fill($this->employeePayload($request->validated()))->save();
        $this->clearDashboardCache();

        return back()->with('success', 'Data karyawan berhasil diperbarui.');
    }

    public function destroyEmployee(Employee $employee): RedirectResponse
    {
        $employee->delete();
        $this->clearDashboardCache();

        return back()->with('success', 'Data karyawan berhasil dihapus.');
    }

    private function employeePayload(array $data): array
    {
        $riskLevel = (int) $data['attrition_risk_level'];
        $employeeId = strtolower($data['employee_id']);

        return [
            ...$data,
            'overtime' => (bool) ($data['overtime'] ?? false),
            'attrition_risk_level' => $riskLevel,
            'attrition_risk_label' => Employee::RISK_LABELS[$riskLevel],
            'source_name' => 'Input Manual',
            'imported_at' => now(),
            'synced_at' => now(),
            'unique_hash' => hash('sha256', 'employee:'.$employeeId),
        ];
    }
}
