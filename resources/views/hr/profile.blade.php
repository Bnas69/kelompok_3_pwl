@extends('layouts.app')

@section('title', 'Profil Kelompok')

@section('content')
<div class="page-header">
    <div>
        <p class="page-eyebrow">Kelompok 3</p>
        <h1>Profil Kelompok</h1>
    </div>
</div>

<div class="row g-4">
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

<div class="card-dashboard mt-4">
    <div class="card-body">
        <span class="page-eyebrow">Informasi</span>
        <h2 class="card-title">Tentang Kelompok</h2>
        <p class="text-muted small mb-0">Human Resource Analytics Dashboard &mdash; Project mata kuliah Pemrograman Web Lanjut, Universitas Dian Nusantara.</p>
    </div>
</div>
@endsection
