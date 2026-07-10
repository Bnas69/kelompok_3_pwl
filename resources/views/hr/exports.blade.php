@extends('layouts.app')

@section('title', 'Export Laporan')

@section('content')
<div class="page-header">
    <div>
        <p class="page-eyebrow">Reporting</p>
        <h1>Export Laporan</h1>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-4">
        <div class="card-dashboard text-center h-100">
            <div class="card-body">
                <div class="mb-3" style="font-size:40px;color:var(--primary);"><i class="bi bi-file-earmark-spreadsheet"></i></div>
                <h2 class="card-title">Excel Data Karyawan</h2>
                <p class="text-muted small mb-3">Export seluruh data karyawan sesuai filter</p>
                <a class="btn btn-primary" href="{{ route('exports.employees') }}">
                    <i class="bi bi-download me-1"></i> Download Excel
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card-dashboard text-center h-100">
            <div class="card-body">
                <div class="mb-3" style="font-size:40px;color:var(--success);"><i class="bi bi-file-earmark-pdf"></i></div>
                <h2 class="card-title">PDF Ringkasan Dashboard</h2>
                <p class="text-muted small mb-3">Ringkasan KPI dan insight utama</p>
                <a class="btn btn-primary" href="{{ route('exports.summary-pdf') }}" target="_blank" rel="noopener noreferrer">
                    <i class="bi bi-eye me-1"></i> Buka PDF
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card-dashboard text-center h-100">
            <div class="card-body">
                <div class="mb-3" style="font-size:40px;color:var(--danger);"><i class="bi bi-exclamation-triangle"></i></div>
                <h2 class="card-title">High Risk Employees</h2>
                <p class="text-muted small mb-3">Karyawan dengan attrition risk tinggi</p>
                <a class="btn btn-primary" href="{{ route('exports.high-risk') }}">
                    <i class="bi bi-download me-1"></i> Download High Risk
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
