@extends('layouts.app')

@section('title', 'Tentang Project')

@section('content')
<div class="page-header">
    <div>
        <p class="page-eyebrow">Dokumentasi Sistem</p>
        <h1>Tentang Project</h1>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-6">
        <div class="card-dashboard">
            <div class="card-body">
                <span class="page-eyebrow">Arsitektur</span>
                <h2 class="card-title">Arsitektur Data</h2>
                <p class="text-muted small">Sistem menggunakan <strong>MySQL</strong> sebagai database utama. Data dapat diimpor dari berbagai sumber: CSV URL, JSON API, Google Sheets, External MySQL, atau file CSV lokal. Dashboard membaca data langsung dari database MySQL, bukan dari file CSV.</p>
                <ul class="text-muted small mt-3 mb-0">
                    <li>Laravel 13 sebagai framework utama</li>
                    <li>MySQL sebagai primary data store</li>
                    <li>CSV/Excel sebagai secondary data source</li>
                    <li>Data sinkronisasi via command/queue</li>
                    <li>Cache untuk optimalisasi performa dashboard</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card-dashboard">
            <div class="card-body">
                <span class="page-eyebrow">Teknologi</span>
                <h2 class="card-title">Stack Teknologi</h2>
                <ul class="list-group list-group-flush text-muted small">
                    <li class="list-group-item px-0 d-flex justify-content-between"><span>Framework</span><span class="fw-semibold">Laravel 13</span></li>
                    <li class="list-group-item px-0 d-flex justify-content-between"><span>Database</span><span class="fw-semibold">MySQL</span></li>
                    <li class="list-group-item px-0 d-flex justify-content-between"><span>Frontend</span><span class="fw-semibold">Bootstrap 5 + Chart.js</span></li>
                    <li class="list-group-item px-0 d-flex justify-content-between"><span>Icons</span><span class="fw-semibold">Bootstrap Icons</span></li>
                    <li class="list-group-item px-0 d-flex justify-content-between"><span>Asset Bundler</span><span class="fw-semibold">Vite</span></li>
                    <li class="list-group-item px-0 d-flex justify-content-between"><span>Cache</span><span class="fw-semibold">Laravel Cache (File/Redis)</span></li>
                    <li class="list-group-item px-0 d-flex justify-content-between"><span>Queue</span><span class="fw-semibold">Laravel Queue</span></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="card-dashboard mt-4">
    <div class="card-body">
        <span class="page-eyebrow">Development</span>
        <h2 class="card-title">Tim Pengembang</h2>
        <p class="text-muted small">Human Resource Analytics Dashboard dikembangkan oleh Kelompok 3, Program Studi Teknik Informatika, Universitas Dian Nusantara, tahun akademik 2025/2026.</p>
    </div>
</div>

<div class="row g-4 mt-2">
    <div class="col-md-4">
        <div class="card-dashboard text-center h-100">
            <div class="card-body">
                <div class="mb-3" style="width:64px;height:64px;border-radius:50%;background:var(--primary-light);color:var(--primary);display:inline-flex;align-items:center;justify-content:center;font-size:24px;font-weight:700;">S</div>
                <h5 class="fw-bold">Septian Dwi Saputra</h5>
                <span class="text-muted small">411232056</span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card-dashboard text-center h-100">
            <div class="card-body">
                <div class="mb-3" style="width:64px;height:64px;border-radius:50%;background:var(--primary-light);color:var(--primary);display:inline-flex;align-items:center;justify-content:center;font-size:24px;font-weight:700;">T</div>
                <h5 class="fw-bold">Tiara Adisa Marcianda</h5>
                <span class="text-muted small">411232040</span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card-dashboard text-center h-100">
            <div class="card-body">
                <div class="mb-3" style="width:64px;height:64px;border-radius:50%;background:var(--primary-light);color:var(--primary);display:inline-flex;align-items:center;justify-content:center;font-size:24px;font-weight:700;">I</div>
                <h5 class="fw-bold">Izatul Janah</h5>
                <span class="text-muted small">411232019</span>
            </div>
        </div>
    </div>
</div>
@endsection
