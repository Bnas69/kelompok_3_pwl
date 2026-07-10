@extends('layouts.app')

@section('title', 'Risiko Turnover')

@section('content')
<div class="page-header">
    <div>
        <p class="page-eyebrow">Risk Analysis</p>
        <h1>Risiko Turnover</h1>
        <p class="page-subtitle mb-0">Evaluasi risiko karyawan dan rekomendasi tindakan untuk HR.</p>
    </div>
</div>

{{-- KPI Summary --}}
<div class="kpi-row mb-4">
    <div class="card-dashboard"><div class="card-body">
        <div class="text-muted small fw-semibold text-uppercase tracking-wider">Total Karyawan</div>
        <div class="fw-bold" style="font-size:28px;color:var(--text-dark);">{{ number_format($overview['kpi']['total_employees'], 0, ',', '.') }}</div>
        <small class="text-muted">Data dari MySQL</small>
    </div></div>
    <div class="card-dashboard"><div class="card-body">
        <div class="text-muted small fw-semibold text-uppercase">High Risk</div>
        <div class="fw-bold" style="font-size:28px;color:var(--danger);">{{ number_format($overview['kpi']['high_risk'], 0, ',', '.') }}</div>
        <small class="text-muted">{{ $overview['kpi']['high_risk_percentage'] }}% dari total — <span class="text-danger fw-semibold">Butuh intervensi segera</span></small>
    </div></div>
    <div class="card-dashboard"><div class="card-body">
        <div class="text-muted small fw-semibold text-uppercase">Medium Risk</div>
        <div class="fw-bold" style="font-size:28px;color:var(--warning);">{{ number_format($overview['kpi']['medium_risk'], 0, ',', '.') }}</div>
        <small class="text-muted">Perlu dipantau secara berkala</small>
    </div></div>
    <div class="card-dashboard"><div class="card-body">
        <div class="text-muted small fw-semibold text-uppercase">Low Risk</div>
        <div class="fw-bold" style="font-size:28px;color:var(--success);">{{ number_format($overview['kpi']['low_risk'], 0, ',', '.') }}</div>
        <small class="text-muted">Relatif aman, monitoring rutin</small>
    </div></div>
</div>

{{-- Evaluasi Risiko --}}
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card-dashboard h-100" style="border-left:4px solid var(--danger);">
            <div class="card-body">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2" style="font-size:12px;font-weight:600;">HIGH RISK</span>
                </div>
                <h3 class="fw-bold" style="font-size:16px;">Mengapa High Risk?</h3>
                <ul class="text-muted small mb-3" style="line-height:1.8;">
                    <li>Job satisfaction rendah (&le; 2.5)</li>
                    <li>Work-life balance buruk (&le; 2.0)</li>
                    <li>Beban kerja tinggi (jam kerja &ge; 190/bln atau proyek &ge; 5)</li>
                    <li>Income tidak kompetitif (&le; 5.000)</li>
                    <li>Riwayat overtime berlebihan</li>
                </ul>
                <h4 class="fw-semibold" style="font-size:13px;color:var(--danger);">Tindakan HR:</h4>
                <ul class="text-muted small mb-0" style="line-height:1.8;">
                    <li>Segera lakukan 1-on-1 meeting</li>
                    <li>Evaluasi beban kerja dan distribusi tugas</li>
                    <li>Pertimbangkan penyesuaian kompensasi</li>
                    <li>Berikan program wellness & konseling</li>
                    <li>Jadwalkan training pengembangan diri</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card-dashboard h-100" style="border-left:4px solid var(--warning);">
            <div class="card-body">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-2" style="font-size:12px;font-weight:600;">MEDIUM RISK</span>
                </div>
                <h3 class="fw-bold" style="font-size:16px;">Mengapa Medium Risk?</h3>
                <ul class="text-muted small mb-3" style="line-height:1.8;">
                    <li>Satisfaction atau WLB di bawah rata-rata (2.5 &ndash; 3.5)</li>
                    <li>Jam kerja cenderung tinggi (160 &ndash; 190/bln)</li>
                    <li>Proyek cukup banyak (3 &ndash; 4 aktif)</li>
                    <li>Mulai ada indikasi overtime</li>
                    <li>Belum ada gejala resign, tapi perlu diwaspadai</li>
                </ul>
                <h4 class="fw-semibold" style="font-size:13px;color:var(--warning);">Tindakan HR:</h4>
                <ul class="text-muted small mb-0" style="line-height:1.8;">
                    <li>Pantau satisfaction secara berkala (bulanan)</li>
                    <li>Lakukan check-in informal setiap 2 minggu</li>
                    <li>Pastikan beban kerja masih wajar</li>
                    <li>Berikan kesempatan pengembangan skill</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card-dashboard h-100" style="border-left:4px solid var(--success);">
            <div class="card-body">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge bg-success bg-opacity-10 text-success px-3 py-2" style="font-size:12px;font-weight:600;">LOW RISK</span>
                </div>
                <h3 class="fw-bold" style="font-size:16px;">Mengapa Low Risk?</h3>
                <ul class="text-muted small mb-3" style="line-height:1.8;">
                    <li>Satisfaction tinggi (&ge; 3.5)</li>
                    <li>Work-life balance baik (&ge; 3.5)</li>
                    <li>Beban kerja normal (&le; 160 jam/bln, &le; 2 proyek)</li>
                    <li>Kompensasi sesuai atau di atas rata-rata</li>
                    <li>Tidak ada indikasi turnover risk</li>
                </ul>
                <h4 class="fw-semibold" style="font-size:13px;color:var(--success);">Tindakan HR:</h4>
                <ul class="text-muted small mb-0" style="line-height:1.8;">
                    <li>Pertahankan kondisi kerja yang baik</li>
                    <li>Berikan apresiasi secara berkala</li>
                    <li>Fasilitasi pengembangan karir</li>
                    <li>Jadwalkan monitoring rutin (kuartalan)</li>
                </ul>
            </div>
        </div>
    </div>
</div>

{{-- Tabel High Risk --}}
<div class="card-dashboard">
    <div class="card-body">
        <span class="page-eyebrow mb-1 d-block">Prioritas HR</span>
        <h2 class="card-title mb-3">Karyawan High Risk — Butuh Intervensi Segera</h2>
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Employee ID</th>
                        <th>Nama</th>
                        <th>Dept</th>
                        <th>Role</th>
                        <th>Jam Kerja</th>
                        <th>Proyek</th>
                        <th>Satisfaction</th>
                        <th>WLB</th>
                        <th>Alasan Utama</th>
                        <th>Rekomendasi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($overview['tables']['priority_employees'] as $employee)
                        <tr>
                            <td>{{ $employee['employee_id'] }}</td>
                            <td>{{ $employee['full_name'] }}</td>
                            <td>{{ $employee['department'] }}</td>
                            <td>{{ $employee['job_role'] }}</td>
                            <td><span @class(['risk-high' => ($employee['monthly_work_hours'] ?? 0) >= 190, 'risk-medium' => ($employee['monthly_work_hours'] ?? 0) >= 160 && ($employee['monthly_work_hours'] ?? 0) < 190])>{{ $employee['monthly_work_hours'] }}</span></td>
                            <td><span @class(['risk-high' => ($employee['projects_count'] ?? 0) >= 5])>{{ $employee['projects_count'] }}</span></td>
                            <td><span @class(['risk-high' => ($employee['job_satisfaction'] ?? 5) <= 2.5, 'risk-medium' => ($employee['job_satisfaction'] ?? 5) > 2.5 && ($employee['job_satisfaction'] ?? 5) <= 3.5])>{{ $employee['job_satisfaction'] }}</span></td>
                            <td><span @class(['risk-high' => ($employee['work_life_balance'] ?? 5) <= 2])>{{ $employee['work_life_balance'] }}</span></td>
                            <td><small class="text-muted">
                                @php
                                    $reasons = [];
                                    if (($employee['job_satisfaction'] ?? 5) <= 2.5) $reasons[] = 'Kepuasan rendah';
                                    if (($employee['work_life_balance'] ?? 5) <= 2) $reasons[] = 'WLB buruk';
                                    if (($employee['monthly_work_hours'] ?? 0) >= 190) $reasons[] = 'Overwork';
                                    if (($employee['projects_count'] ?? 0) >= 5) $reasons[] = 'Terlalu banyak proyek';
                                    if (($employee['monthly_income'] ?? 0) <= 5000) $reasons[] = 'Kompensasi rendah';
                                    echo implode(', ', $reasons) ?: 'Multifaktor';
                                @endphp
                            </small></td>
                            <td><small class="text-muted">{{ $employee['recommendation'] }}</small></td>
                        </tr>
                    @empty
                        <tr><td colspan="10" class="text-center text-muted py-4">Belum ada data High Risk.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
