@extends('layouts.app')

@section('title', 'Data Karyawan')

@section('content')
@php($isAdmin = session('hr_role') === 'admin')

<div class="page-header">
    <div>
        <p class="page-eyebrow">Database Employees</p>
        <h1>Data Karyawan</h1>
    </div>
    <a class="btn btn-outline-primary btn-sm" href="{{ route('exports.employees', request()->query()) }}">
        <i class="bi bi-download me-1"></i> Export Hasil Filter
    </a>
</div>

{{-- Filters --}}
<div class="card-dashboard mb-4">
    <div class="card-body">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label" for="search">Cari</label>
                <input id="search" name="search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="Nama / Employee ID">
            </div>
            <div class="col-md-2">
                <label class="form-label" for="department">Department</label>
                <select id="department" name="department" class="form-select form-select-sm">
                    <option value="all">Semua</option>
                    @foreach ($filters['departments'] as $department)
                        <option value="{{ $department }}" @selected(request('department', 'all') === $department)>{{ $department }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label" for="job_role">Job Role</label>
                <select id="job_role" name="job_role" class="form-select form-select-sm">
                    <option value="all">Semua</option>
                    @foreach ($filters['job_roles'] as $role)
                        <option value="{{ $role }}" @selected(request('job_role', 'all') === $role)>{{ $role }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label" for="risk_level">Risk Level</label>
                <select id="risk_level" name="risk_level" class="form-select form-select-sm">
                    <option value="all">Semua</option>
                    <option value="0" @selected(request('risk_level') === '0')>Low Risk</option>
                    <option value="1" @selected(request('risk_level') === '1')>Medium Risk</option>
                    <option value="2" @selected(request('risk_level') === '2')>High Risk</option>
                </select>
            </div>
            <div class="col-md-auto d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-funnel me-1"></i> Filter</button>
                <a class="btn btn-outline-secondary btn-sm" href="{{ route('employees.index') }}"><i class="bi bi-x-circle me-1"></i> Reset</a>
            </div>
        </form>
    </div>
</div>

{{-- Table --}}
<div class="card-dashboard mb-4">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Employee ID</th>
                    <th>Nama</th>
                    <th>Department</th>
                    <th>Job Role</th>
                    <th>Age</th>
                    <th>Income</th>
                    <th>Satisfaction</th>
                    <th>Risk</th>
                    <th>Rekomendasi</th>
                    @if ($isAdmin)
                        <th>Aksi</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse ($employees as $employee)
                    <tr>
                        <td class="fw-semibold">{{ $employee['employee_id'] }}</td>
                        <td>{{ $employee['full_name'] }}</td>
                        <td>{{ $employee['department'] }}</td>
                        <td>{{ $employee['job_role'] }}</td>
                        <td>{{ $employee['age'] }}</td>
                        <td>{{ number_format($employee['monthly_income'], 0, ',', '.') }}</td>
                        <td>{{ number_format($employee['job_satisfaction'], 1, ',', '.') }}</td>
                        <td>
                            <span @class(['risk-low' => $employee['risk_level'] === 0, 'risk-medium' => $employee['risk_level'] === 1, 'risk-high' => $employee['risk_level'] === 2])>
                                {{ $employee['risk_label'] }}
                            </span>
                        </td>
                        <td><small class="text-muted">{{ $employee['recommendation'] }}</small></td>
                        @if ($isAdmin)
                            <td>
                                <form method="post" action="{{ route('employees.destroy', $employee['id']) }}" data-confirm="Hapus data karyawan ini?">
                                    @csrf
                                    @method('delete')
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr><td colspan="{{ $isAdmin ? 10 : 9 }}" class="text-center text-muted py-4">Belum ada data karyawan sesuai filter.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $employees->withQueryString()->links('partials.pagination') }}
</div>

@if ($isAdmin)
    {{-- Form Tambah/Update --}}
    <div class="card-dashboard">
        <div class="card-body">
            <h2 class="card-title mb-3">Tambah / Update Data Karyawan</h2>
            <form method="post" action="{{ route('employees.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="employee_id">Employee ID</label>
                        <input id="employee_id" name="employee_id" class="form-control" value="{{ old('employee_id') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="full_name">Nama</label>
                        <input id="full_name" name="full_name" class="form-control" value="{{ old('full_name') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="department">Department</label>
                        <input id="department" name="department" class="form-control" value="{{ old('department') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="job_role">Position</label>
                        <input id="job_role" name="job_role" class="form-control" value="{{ old('job_role') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="attrition_risk_level">Risk</label>
                        <select id="attrition_risk_level" name="attrition_risk_level" class="form-select" required>
                            <option value="0" @selected(old('attrition_risk_level') === '0')>Low Risk</option>
                            <option value="1" @selected(old('attrition_risk_level') === '1')>Medium Risk</option>
                            <option value="2" @selected(old('attrition_risk_level') === '2')>High Risk</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="age">Age</label>
                        <input id="age" type="number" min="15" max="80" name="age" class="form-control" value="{{ old('age') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="gender">Gender</label>
                        <input id="gender" name="gender" class="form-control" value="{{ old('gender') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="monthly_income">Monthly Income</label>
                        <input id="monthly_income" type="number" min="0" step="0.01" name="monthly_income" class="form-control" value="{{ old('monthly_income') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="monthly_work_hours">Monthly Hours</label>
                        <input id="monthly_work_hours" type="number" min="0" max="744" step="0.01" name="monthly_work_hours" class="form-control" value="{{ old('monthly_work_hours') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="job_satisfaction">Job Satisfaction</label>
                        <input id="job_satisfaction" type="number" min="0" max="5" step="0.01" name="job_satisfaction" class="form-control" value="{{ old('job_satisfaction') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="work_life_balance">Work Life Balance</label>
                        <input id="work_life_balance" type="number" min="0" max="5" step="0.01" name="work_life_balance" class="form-control" value="{{ old('work_life_balance') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="projects_count">Projects</label>
                        <input id="projects_count" type="number" min="0" max="999" name="projects_count" class="form-control" value="{{ old('projects_count') }}">
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="overtime" value="1" id="overtime" @checked(old('overtime'))>
                            <label class="form-check-label" for="overtime">Overtime</label>
                        </div>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i> Simpan ke MySQL
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endif
@endsection
