<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laporan HR Analytics - {{ $generatedAt }}</title>
    <style>
        * { box-sizing:border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; color:#1f2937; margin:0; padding:32px; background:#fff; }
        h1 { font-size:22px; margin:0 0 4px; font-weight:700; }
        h2 { font-size:16px; margin:24px 0 10px; font-weight:700; color:#1e40af; border-bottom:2px solid #1e40af; padding-bottom:4px; }
        h3 { font-size:13px; margin:14px 0 6px; font-weight:600; }
        p { font-size:11px; line-height:1.6; margin:2px 0; color:#4b5563; }
        table { width:100%; border-collapse:collapse; margin:8px 0; font-size:10px; }
        th, td { border:1px solid #d1d5db; padding:6px 8px; text-align:left; }
        th { background:#f3f4f6; font-weight:600; color:#374151; }
        .header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:20px; padding-bottom:12px; border-bottom:3px solid #1e40af; }
        .header-right { text-align:right; font-size:10px; color:#6b7280; }
        .kpi-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:8px; margin:12px 0; }
        .kpi-card { border:1px solid #e5e7eb; padding:10px 12px; border-radius:6px; }
        .kpi-card .lbl { font-size:9px; text-transform:uppercase; letter-spacing:.5px; color:#6b7280; font-weight:600; }
        .kpi-card .val { font-size:20px; font-weight:700; margin:2px 0; }
        .kpi-card .sub { font-size:9px; color:#9ca3af; }
        .dept-bar { display:flex; align-items:center; gap:6px; margin:2px 0; font-size:10px; }
        .dept-bar-inner { height:14px; border-radius:3px; min-width:2px; }
        .risk-high-bg { background:#fee2e2; }
        .risk-med-bg { background:#fef3c7; }
        .risk-low-bg { background:#ecfdf5; }
        .risk-high { color:#dc2626; font-weight:600; }
        .risk-med { color:#d97706; font-weight:600; }
        .risk-low { color:#16a34a; font-weight:600; }
        .grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
        .footer { margin-top:24px; padding-top:12px; border-top:1px solid #e5e7eb; font-size:9px; color:#9ca3af; text-align:center; }
        .badge { display:inline-block; padding:2px 8px; border-radius:4px; font-size:9px; font-weight:600; }
        button { display:none; }
        @media print { body { padding:16px; } }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <h1>Human Resource Analytics</h1>
            <p>Laporan Ringkasan Data Karyawan, Attrition Risk, dan Performa Departemen</p>
        </div>
        <div class="header-right">
            <div><strong>Dibuat:</strong> {{ $generatedAt }}</div>
            <div><strong>Total Data:</strong> {{ number_format($overview['kpi']['total_employees'], 0, ',', '.') }} karyawan</div>
            <div><strong>Sumber:</strong> MySQL Database</div>
        </div>
    </div>

    {{-- KPI Grid --}}
    <h2>Ringkasan Eksekutif</h2>
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="lbl">Total Karyawan</div>
            <div class="val">{{ number_format($overview['kpi']['total_employees'], 0, ',', '.') }}</div>
            <div class="sub">Seluruh data aktif</div>
        </div>
        <div class="kpi-card">
            <div class="lbl">Attrition Rate</div>
            <div class="val @if(($overview['kpi']['high_risk_percentage'] ?? 0) > 20) risk-high @endif">{{ $overview['kpi']['high_risk_percentage'] ?? 0 }}%</div>
            <div class="sub">Persentase high risk</div>
        </div>
        <div class="kpi-card">
            <div class="lbl">High Risk</div>
            <div class="val risk-high">{{ number_format($overview['kpi']['high_risk'], 0, ',', '.') }}</div>
            <div class="sub">Butuh intervensi segera</div>
        </div>
        <div class="kpi-card">
            <div class="lbl">Medium Risk</div>
            <div class="val risk-med">{{ number_format($overview['kpi']['medium_risk'], 0, ',', '.') }}</div>
            <div class="sub">Perlu pemantauan</div>
        </div>
        <div class="kpi-card">
            <div class="lbl">Low Risk</div>
            <div class="val risk-low">{{ number_format($overview['kpi']['low_risk'], 0, ',', '.') }}</div>
            <div class="sub">Relatif aman</div>
        </div>
        <div class="kpi-card">
            <div class="lbl">Rata-rata Kepuasan</div>
            <div class="val">{{ number_format($overview['kpi']['avg_job_satisfaction'] ?? 0, 2) }}</div>
            <div class="sub">Skala 1–5</div>
        </div>
        <div class="kpi-card">
            <div class="lbl">Rata-rata Income</div>
            <div class="val">Rp {{ number_format($overview['kpi']['avg_monthly_income'] ?? 0, 0, ',', '.') }}</div>
            <div class="sub">Per bulan</div>
        </div>
        <div class="kpi-card">
            <div class="lbl">Work-Life Balance</div>
            <div class="val">{{ number_format($overview['kpi']['avg_work_life_balance'] ?? 0, 2) }}</div>
            <div class="sub">Skala 1–5</div>
        </div>
    </div>

    {{-- Risk Distribution --}}
    <h2>Distribusi Risiko</h2>
    <p>Komposisi karyawan berdasarkan tingkat attrition risk.</p>
    <table>
        <thead>
            <tr>
                <th>Level Risiko</th>
                <th>Jumlah Karyawan</th>
                <th>Persentase</th>
            </tr>
        </thead>
        <tbody>
            @foreach (($overview['charts']['risk_distribution'] ?? []) as $risk)
                <tr>
                    <td><span class="badge @if($risk['level'] === 2) risk-high-bg risk-high @elseif($risk['level'] === 1) risk-med-bg risk-med @else risk-low-bg risk-low @endif">{{ $risk['label'] }}</span></td>
                    <td>{{ number_format($risk['count'], 0, ',', '.') }}</td>
                    <td>{{ number_format($risk['percentage'], 1) }}%</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Department Risk --}}
    <h2>Risiko per Departemen</h2>
    <p>Distribusi risiko attrition di setiap departemen. Departemen dengan high risk tertinggi menjadi prioritas intervensi.</p>
    <table>
        <thead>
            <tr>
                <th>Departemen</th>
                <th>Total</th>
                <th>Low Risk</th>
                <th>Medium Risk</th>
                <th>High Risk</th>
                <th>Skor Performa</th>
            </tr>
        </thead>
        <tbody>
            @php $maxHigh = max(array_column($overview['charts']['department_risk'] ?? [['high' => 1]], 'high')); @endphp
            @foreach (($overview['charts']['department_risk'] ?? []) as $dept)
                @php
                    $score = $dept['total'] > 0 ? round((1 - ($dept['high'] / $dept['total'])) * 100, 1) : 0;
                    $barW = $maxHigh > 0 ? ($dept['high'] / $maxHigh) * 100 : 0;
                @endphp
                <tr>
                    <td><strong>{{ $dept['label'] }}</strong></td>
                    <td>{{ number_format($dept['total'], 0, ',', '.') }}</td>
                    <td class="risk-low">{{ number_format($dept['low'], 0, ',', '.') }}</td>
                    <td class="risk-med">{{ number_format($dept['medium'], 0, ',', '.') }}</td>
                    <td class="risk-high">
                        {{ number_format($dept['high'], 0, ',', '.') }}
                        <div class="dept-bar">
                            <div class="dept-bar-inner risk-high-bg" style="width:{{ $barW }}%;"></div>
                        </div>
                    </td>
                    <td>
                        <span class="badge @if($score < 75) risk-high-bg risk-high @elseif($score < 90) risk-med-bg risk-med @else risk-low-bg risk-low @endif">{{ $score }}%</span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Trend Attrition --}}
    <h2>Tren Attrition Rate (6 Bulan Terakhir)</h2>
    <p>Perkembangan persentase karyawan high risk per periode.</p>
    <table>
        <thead>
            <tr>
                <th>Periode</th>
                <th>Total Karyawan</th>
                <th>High Risk</th>
                <th>Attrition Rate</th>
                <th>Tren</th>
            </tr>
        </thead>
        <tbody>
            @php $prevRate = null; @endphp
            @foreach (($overview['charts']['monthly_sync_trend'] ?? []) as $trend)
                @php
                    $rate = $trend['total'] > 0 ? round(($trend['high'] / $trend['total']) * 100, 1) : 0;
                    $arrow = '';
                    if ($prevRate !== null) {
                        if ($rate > $prevRate) $arrow = '↑ Naik';
                        elseif ($rate < $prevRate) $arrow = '↓ Turun';
                        else $arrow = '→ Tetap';
                    }
                    $prevRate = $rate;
                @endphp
                <tr>
                    <td><strong>{{ $trend['period'] }}</strong></td>
                    <td>{{ number_format($trend['total'], 0, ',', '.') }}</td>
                    <td class="risk-high">{{ number_format($trend['high'], 0, ',', '.') }}</td>
                    <td><span class="badge @if($rate > 20) risk-high-bg risk-high @elseif($rate > 10) risk-med-bg risk-med @else risk-low-bg risk-low @endif">{{ $rate }}%</span></td>
                    <td style="font-size:10px;">{{ $arrow }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Priority Employees --}}
    <h2>Prioritas Karyawan High Risk</h2>
    <p>Daftar karyawan dengan attrition risk tertinggi yang membutuhkan intervensi HR segera.</p>
    <table>
        <thead>
            <tr>
                <th>Employee ID</th>
                <th>Nama</th>
                <th>Departemen</th>
                <th>Role</th>
                <th>Jam Kerja</th>
                <th>Proyek</th>
                <th>Kepuasan</th>
                <th>WLB</th>
                <th>Alasan</th>
                <th>Rekomendasi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($overview['tables']['priority_employees'] as $emp)
                @php
                    $reasons = [];
                    if (($emp['job_satisfaction'] ?? 5) <= 2.5) $reasons[] = 'Kepuasan rendah';
                    if (($emp['work_life_balance'] ?? 5) <= 2) $reasons[] = 'WLB buruk';
                    if (($emp['monthly_work_hours'] ?? 0) >= 190) $reasons[] = 'Overwork';
                    if (($emp['projects_count'] ?? 0) >= 5) $reasons[] = 'Terlalu banyak proyek';
                    if (($emp['monthly_income'] ?? 0) <= 5000) $reasons[] = 'Kompensasi rendah';
                @endphp
                <tr>
                    <td>{{ $emp['employee_id'] }}</td>
                    <td>{{ $emp['full_name'] }}</td>
                    <td>{{ $emp['department'] }}</td>
                    <td>{{ $emp['job_role'] }}</td>
                    <td>{{ $emp['monthly_work_hours'] }}</td>
                    <td>{{ $emp['projects_count'] }}</td>
                    <td class="risk-high">{{ $emp['job_satisfaction'] }}</td>
                    <td class="risk-high">{{ $emp['work_life_balance'] }}</td>
                    <td style="font-size:9px;">{{ implode(', ', $reasons) ?: 'Multifaktor' }}</td>
                    <td style="font-size:9px;">{{ $emp['recommendation'] }}</td>
                </tr>
            @empty
                <tr><td colspan="10" style="text-align:center;color:#9ca3af;">Tidak ada data high risk.</td></tr>
            @endforelse
        </tbody>
    </table>

    {{-- Insights --}}
    <h2>Insight & Rekomendasi Strategis</h2>
    <ul style="font-size:11px;line-height:1.8;">
        @foreach ($overview['insights'] as $insight)
            <li>{{ $insight }}</li>
        @endforeach
        <li>Dashboard diperbarui secara otomatis setiap 2 menit. Data dapat di-refresh manual melalui tombol Refresh Data.</li>
    </ul>

    <div class="footer">
        Laporan digenerate otomatis oleh HR Analytics &mdash; {{ $generatedAt }}
    </div>
</body>
</html>
