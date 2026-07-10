<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Services\HrAnalytics\HrDashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;

class HrExportController extends Controller
{
    public function __construct(private readonly HrDashboardService $dashboard)
    {
    }

    public function index(): View
    {
        return view('hr.exports', [
            'overview' => $this->dashboard->overview(),
        ]);
    }

    public function employeesCsv(Request $request)
    {
        $query = $this->dashboard->employeeQuery($request->all())->orderBy('employee_id');

        return Response::streamDownload(function () use ($query): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $this->headers());
            $query->chunk(500, function ($employees) use ($handle): void {
                foreach ($employees as $employee) {
                    fputcsv($handle, $this->row($employee));
                }
            });
            fclose($handle);
        }, 'employees_export.csv', ['Content-Type' => 'text/csv']);
    }

    public function highRiskCsv()
    {
        $query = Employee::query()->highRisk()->orderBy('job_satisfaction');

        return Response::streamDownload(function () use ($query): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [...$this->headers(), 'Rekomendasi HR']);
            $query->chunk(500, function ($employees) use ($handle): void {
                foreach ($employees as $employee) {
                    fputcsv($handle, [...$this->row($employee), $this->dashboard->recommendation($employee)]);
                }
            });
            fclose($handle);
        }, 'high_risk_employees.csv', ['Content-Type' => 'text/csv']);
    }

    public function summaryPdf(): View
    {
        return view('reports.summary', [
            'overview' => $this->dashboard->overview(),
            'generatedAt' => now()->format('Y-m-d H:i:s'),
        ]);
    }

    private function headers(): array
    {
        return [
            'Employee ID',
            'Nama',
            'Department',
            'Job Role',
            'Age',
            'Gender',
            'Monthly Income',
            'Monthly Work Hours',
            'Projects Count',
            'Job Satisfaction',
            'Work-Life Balance',
            'Risk Level',
            'Risk Label',
            'Synced At',
        ];
    }

    private function row(Employee $employee): array
    {
        return [
            $employee->employee_id,
            $employee->full_name,
            $employee->department,
            $employee->job_role,
            $employee->age,
            $employee->gender,
            $employee->monthly_income,
            $employee->monthly_work_hours,
            $employee->projects_count,
            $employee->job_satisfaction,
            $employee->work_life_balance,
            $employee->attrition_risk_level,
            $employee->attrition_risk_label,
            $employee->synced_at?->format('Y-m-d H:i:s'),
        ];
    }
}
