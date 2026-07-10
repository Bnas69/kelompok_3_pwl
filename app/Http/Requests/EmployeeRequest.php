<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->session()->get('hr_role') === 'admin';
    }

    public function rules(): array
    {
        $unique = Rule::unique('employees', 'employee_id');
        if ($employee = $this->route('employee')) {
            $unique->ignore($employee->id);
        }

        return [
            'employee_id' => ['required', 'string', 'max:80', $unique],
            'full_name' => ['required', 'string', 'max:255'],
            'age' => ['nullable', 'integer', 'min:15', 'max:80'],
            'gender' => ['nullable', 'string', 'max:40'],
            'department' => ['required', 'string', 'max:255'],
            'job_role' => ['required', 'string', 'max:255'],
            'monthly_income' => ['nullable', 'numeric', 'min:0'],
            'monthly_work_hours' => ['nullable', 'numeric', 'min:0', 'max:744'],
            'projects_count' => ['nullable', 'integer', 'min:0', 'max:999'],
            'job_satisfaction' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'work_life_balance' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'attrition_risk_level' => ['required', Rule::in([0, 1, 2])],
            'overtime' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'employee_id.required' => 'Employee ID wajib diisi.',
            'employee_id.unique' => 'Employee ID sudah terdaftar.',
            'full_name.required' => 'Nama karyawan wajib diisi.',
            'department.required' => 'Department wajib diisi.',
            'job_role.required' => 'Job Role wajib diisi.',
            'attrition_risk_level.required' => 'Risk level wajib dipilih.',
            'attrition_risk_level.in' => 'Risk level harus 0 (Low), 1 (Medium), atau 2 (High).',
        ];
    }
}
