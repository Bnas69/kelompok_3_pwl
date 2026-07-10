@extends('layouts.app')

@section('title', 'Import Data')

@section('content')
<div class="page-header">
    <div>
        <p class="page-eyebrow">Import Data</p>
        <h1>Import Data Karyawan</h1>
    </div>
    <a class="btn btn-outline-primary btn-sm" href="{{ route('template.download') }}">
        <i class="bi bi-download me-1"></i> Download Template
    </a>
</div>

<div class="row g-4">
    <div class="col-md-6">
        <div class="card-dashboard">
            <div class="card-body">
                <span class="page-eyebrow">Status</span>
                <h2 class="card-title">Status Database</h2>
                <p class="text-muted small mb-3">Total data di database saat ini: <strong class="text-dark">{{ number_format($totalEmployees, 0, ',', '.') }}</strong></p>
                <form method="post" action="{{ route('import.fallback') }}">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-repeat me-1"></i> Sync CSV Fallback
                    </button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card-dashboard">
            <div class="card-body">
                <span class="page-eyebrow">Upload</span>
                <h2 class="card-title">Upload CSV / Excel</h2>
                <form method="post" action="{{ route('import.upload') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label for="hr_file" class="form-label">File HR</label>
                        <input id="hr_file" name="hr_file" type="file" class="form-control" accept=".csv,.txt,.xlsx,text/csv,text/plain,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-upload me-1"></i> Upload dan Import
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
