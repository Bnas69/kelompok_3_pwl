@extends('layouts.app')

@section('title', 'Analisis Faktor')

@section('content')
<div class="page-header">
    <div>
        <p class="page-eyebrow">Factor Analysis</p>
        <h1>Analisis Faktor</h1>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-6">
        <div class="card-dashboard">
            <div class="card-body">
                <span class="page-eyebrow">Workload</span>
                <h2 class="card-title">Rata-rata Workload per Risk Level</h2>
                <div class="mt-3">
                    @foreach ($overview['charts']['workload_by_risk'] as $row)
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <span class="fw-semibold">{{ $row['label'] }}</span>
                            <span class="text-muted small">{{ $row['hours'] }} jam/bulan &middot; {{ $row['projects'] }} proyek</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card-dashboard">
            <div class="card-body">
                <span class="page-eyebrow">Engagement</span>
                <h2 class="card-title">Engagement &amp; Wellbeing</h2>
                <div class="mt-3">
                    @foreach ($overview['charts']['satisfaction_by_risk'] as $row)
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <span class="fw-semibold">{{ $row['label'] }}</span>
                            <span class="text-muted small">Satisfaction {{ $row['job_satisfaction'] }} &middot; WLB {{ $row['work_life_balance'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card-dashboard mt-4">
    <div class="card-body">
        <span class="page-eyebrow">Distribusi</span>
        <h2 class="card-title">Distribusi Risiko per Kelompok Usia</h2>
        <div class="table-responsive mt-3">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Kelompok Usia</th>
                        <th>Low</th>
                        <th>Medium</th>
                        <th>High</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($overview['charts']['risk_by_age_group'] as $row)
                        <tr>
                            <td class="fw-semibold">{{ $row['label'] }}</td>
                            <td><span class="risk-low">{{ $row['low'] }}</span></td>
                            <td><span class="risk-medium">{{ $row['medium'] }}</span></td>
                            <td><span class="risk-high">{{ $row['high'] }}</span></td>
                            <td>{{ $row['total'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
