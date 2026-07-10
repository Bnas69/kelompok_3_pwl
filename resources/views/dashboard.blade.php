@extends('layouts.app')

@section('title', 'Dashboard')

@push('styles')
<style>
    .training-card th { font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:#64748b;background:#f8fafc;border-bottom:1px solid #e2e8f0;padding:12px 16px; }
    .training-card td { padding:12px 16px;border-bottom:1px solid #f1f5f9;vertical-align:middle; }
</style>
@endpush

@section('content')
@php
    $role = session('hr_role', 'karyawan');
    $isKaryawan = $role === 'karyawan';
@endphp

@if ($isKaryawan)
    {{-- ═══ KARYAWAN DASHBOARD ═══════════════════════════ --}}
    <div class="page-header">
        <div>
            <p class="page-eyebrow">Dashboard</p>
            <h1>Welcome, {{ session('hr_display_name', 'Karyawan') }}</h1>
            <p class="page-subtitle mb-0">Portal informasi kepegawaian — pantau status dan informasi akun Anda.</p>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="card-dashboard h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div style="width:56px;height:56px;border-radius:16px;background:var(--primary-light);color:var(--primary);display:flex;align-items:center;justify-content:center;font-size:24px;font-weight:700;flex-shrink:0;">
                            {{ strtoupper(substr(session('hr_display_name', 'K'), 0, 1)) }}
                        </div>
                        <div>
                            <h2 class="card-title mb-1" style="font-size:18px;">{{ session('hr_display_name', 'Karyawan') }}</h2>
                            <span class="badge bg-primary bg-opacity-10 text-primary">Karyawan</span>
                        </div>
                    </div>
                    <table class="table table-borderless mb-0 small">
                        <tbody>
                            <tr><td class="text-muted ps-0" style="width:35%;">Username</td><td class="fw-semibold ps-0">{{ session('hr_username', '-') }}</td></tr>
                            <tr><td class="text-muted ps-0" style="width:35%;">Email</td><td class="fw-semibold ps-0">{{ session('hr_email', '-') }}</td></tr>
                            <tr><td class="text-muted ps-0" style="width:35%;">Role</td><td class="fw-semibold ps-0">Karyawan</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card-dashboard h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div style="width:48px;height:48px;border-radius:12px;background:#f0fdf4;color:#16a34a;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0;">
                            <i class="bi bi-info-circle"></i>
                        </div>
                        <div>
                            <h2 class="card-title mb-0">Informasi Sistem</h2>
                        </div>
                    </div>
                    <table class="table table-borderless mb-0 small">
                        <tbody>
                            <tr><td class="text-muted ps-0" style="width:35%;">Aplikasi</td><td class="fw-semibold ps-0">HR Analytics Dashboard</td></tr>
                            <tr><td class="text-muted ps-0" style="width:35%;">Versi</td><td class="fw-semibold ps-0">{{ config('app.version', '1.0') }}</td></tr>
                            <tr><td class="text-muted ps-0" style="width:35%;">Kelompok</td><td class="fw-semibold ps-0">Kelompok 3</td></tr>
                            <tr><td class="text-muted ps-0" style="width:35%;">Universitas</td><td class="fw-semibold ps-0">Universitas Dian Nusantara</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="card-dashboard">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div style="width:48px;height:48px;border-radius:12px;background:#fffbeb;color:#d97706;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0;">
                            <i class="bi bi-megaphone"></i>
                        </div>
                        <div>
                            <h2 class="card-title mb-0">Pengumuman</h2>
                            <p class="text-muted small mb-0">Informasi terbaru dari HR Departemen.</p>
                        </div>
                    </div>
                    <p class="text-muted small mb-0">Selamat datang di portal HR Analytics. Gunakan menu <strong>Tentang Project</strong> untuk melihat informasi tim pengembang, atau menu <strong>Profil Kelompok</strong> untuk detail anggota kelompok.</p>
                </div>
            </div>
        </div>
    </div>
@else
    {{-- ═══ ADMIN / HRD / OWNER DASHBOARD ═══════════════ --}}
    <div data-dashboard>

        {{-- Header --}}
        <div class="card-dashboard mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                    <div>
                        <p class="page-eyebrow mb-1">Dashboard</p>
                        <h1 class="mb-1" style="font-size:24px;font-weight:800;">Human Resource Analytics Dashboard</h1>
                        <p class="page-subtitle mb-0">Ringkasan utama data karyawan, attrition risk, dan performa departemen.</p>
                    </div>
                    <div class="text-end flex-shrink-0">
                        <div class="text-muted small fw-semibold mb-1">Terakhir Diperbarui</div>
                        <div id="analyticsUpdatedAt" class="fw-bold" style="font-size:18px;color:var(--text-dark);">-</div>
                        <span id="dashDbStatusBadge" class="badge bg-success bg-opacity-10 text-success mt-2" hidden>
                            <i class="bi bi-check-circle-fill me-1"></i>Data Aktif di MySQL
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Status warning --}}
        <div id="dashboardDataStatus" hidden></div>

        {{-- Skeleton Loading --}}
        <div id="dashboardSkeleton">
            <div class="kpi-row mb-3">
                @for ($i = 0; $i < 6; $i++)
                    <div class="card-dashboard"><div class="card-body"><div class="skeleton skeleton-card"></div></div></div>
                @endfor
            </div>
            <div class="charts-three mb-3">
                @for ($i = 0; $i < 3; $i++)
                    <div class="card-dashboard"><div class="card-body"><div class="skeleton skeleton-chart"></div></div></div>
                @endfor
            </div>
        </div>

        {{-- ═══ KPI CARDS ═══════════════════════════════════ --}}
        <div class="kpi-row" id="analyticsSummaryGrid"></div>

        {{-- ═══ ALERT BANNER ════════════════════════════════ --}}
        <div id="dashboardAlertBanner" class="alert-banner" hidden>
            <div class="alert-banner-icon"><i class="bi bi-exclamation-triangle-fill"></i></div>
            <div class="flex-grow-1">
                <div class="fw-bold" style="color:#92400e;font-size:13px;">Perhatian Diperlukan</div>
                <div id="alertBannerDesc" style="color:#78350f;font-size:12px;">Beberapa departemen memerlukan perhatian HR.</div>
            </div>
            <a href="{{ route('risks.index') }}" class="btn btn-sm" style="color:#d97706;border:1px solid #fbbf24;border-radius:8px;font-weight:600;">Lihat Rekomendasi</a>
        </div>

        {{-- ═══ REFRESH ROW ══════════════════════════════════ --}}
        <div class="card-dashboard mb-3">
            <div class="card-body d-flex align-items-center gap-3 flex-wrap">
                <button id="refreshDashboardButton" class="btn btn-primary btn-sm" type="button">
                    <i class="bi bi-arrow-clockwise me-1"></i> Refresh Data
                </button>
                <span class="text-muted small">Terakhir sinkronisasi: <strong id="lastSyncTime" class="fw-semibold">-</strong></span>
                <span id="analyticsMessage" class="small text-success ms-auto"></span>
            </div>
        </div>

        {{-- ═══ CHARTS ═══════════════════════════════════════ --}}
        <div class="charts-three">
            {{-- Dept Risk Bar --}}
            <div class="chart-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <span class="page-eyebrow">Department Overview</span>
                            <h2 class="card-title">Risiko per Departemen</h2>
                        </div>
                        <span class="badge bg-light text-muted">Top 10</span>
                    </div>
                    <div class="chart-box">
                        <canvas id="departmentOverviewChart"></canvas>
                    </div>
                    <div data-chart-empty="departmentOverviewChart" hidden class="text-muted small mt-2">Belum ada data departemen.</div>
                </div>
            </div>

            {{-- Trend --}}
            <div class="chart-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <span class="page-eyebrow">Trend Attrition</span>
                            <h2 class="card-title">Attrition Rate per Periode</h2>
                        </div>
                        <span class="badge bg-light text-muted">6 Bulan</span>
                    </div>
                    <div class="chart-box">
                        <canvas id="trendAttritionChart"></canvas>
                    </div>
                    <div data-chart-empty="trendAttritionChart" hidden class="text-muted small mt-2">Belum ada data tren.</div>
                </div>
            </div>

            {{-- Donut --}}
            <div class="chart-card">
                <div class="card-body">
                    <div class="mb-3">
                        <span class="page-eyebrow">Distribusi Risiko</span>
                        <h2 class="card-title">Komposisi Risk Level</h2>
                    </div>
                    <div class="chart-box-sm">
                        <canvas id="riskDonutChart"></canvas>
                    </div>
                    <div id="donutLegend" class="donut-legend"></div>
                    <div data-chart-empty="riskDonutChart" hidden class="text-muted small mt-2">Belum ada data risiko.</div>
                </div>
            </div>
        </div>

        {{-- ═══ TRAINING TABLE ═══════════════════════════════ --}}
        <div class="card-dashboard mt-3">
            <div class="card-body">
                <span class="page-eyebrow mb-3 d-block">Rekomendasi Training Otomatis</span>
                <div class="table-responsive">
                    <table class="table training-card mb-0" style="min-width:700px;">
                        <thead>
                            <tr>
                                <th>Departemen</th>
                                <th>Skor Performa</th>
                                <th>Status</th>
                                <th>Rekomendasi Training</th>
                                <th>Jadwal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="trainingTableBody">
                            <tr><td colspan="6" class="text-muted text-center py-3">Memuat rekomendasi...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="text-center small text-muted mt-4 pb-2">&copy; {{ date('Y') }} HR Analytics. All rights reserved.</div>
    </div>
@endif
@endsection
