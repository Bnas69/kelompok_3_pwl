@extends('layouts.app')

@section('title', 'Sumber Data')

@section('content')
<div class="page-header">
    <div>
        <p class="page-eyebrow">Admin Data</p>
        <h1>Sumber Data HR</h1>
    </div>
    <a class="btn btn-primary btn-sm" href="{{ route('data-sources.create') }}">
        <i class="bi bi-plus-circle me-1"></i> Tambah Sumber
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card-dashboard"><div class="card-body">
            <div class="text-muted small fw-semibold text-uppercase">Total Sources</div>
            <div class="fw-bold" style="font-size:24px;color:var(--text-dark);">{{ number_format($sources->total(), 0, ',', '.') }}</div>
            <small class="text-muted">Semua konfigurasi sumber</small>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card-dashboard"><div class="card-body">
            <div class="text-muted small fw-semibold text-uppercase">Aktif</div>
            <div class="fw-bold" style="font-size:24px;color:var(--success);">{{ number_format($activeSources, 0, ',', '.') }}</div>
            <small class="text-muted">Dipakai saat sync</small>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card-dashboard"><div class="card-body">
            <div class="text-muted small fw-semibold text-uppercase">Nonaktif</div>
            <div class="fw-bold" style="font-size:24px;color:var(--text-muted);">{{ number_format($inactiveSources, 0, ',', '.') }}</div>
            <small class="text-muted">Tidak dibaca sync</small>
        </div></div>
    </div>
</div>

<div class="card-dashboard">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <span class="page-eyebrow">Daftar</span>
                <h2 class="card-title mb-0">Sumber Data</h2>
            </div>
            <span class="text-muted small">{{ $sources->total() }} data</span>
        </div>
        <div class="table-responsive">
            <table class="table mb-0" style="min-width:900px;">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Tipe</th>
                        <th>Status</th>
                        <th>Interval</th>
                        <th>Sync Terakhir</th>
                        <th>Hasil</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sources as $source)
                        @php
                            $lastStatus = strtolower((string) $source->last_status);
                        @endphp
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $source->name }}</div>
                                <small class="text-muted">{{ $source->source_url ?: '-' }}</small>
                            </td>
                            <td>{{ $types[$source->type] ?? $source->type }}</td>
                            <td>
                                <span @class(['badge', 'bg-success' => $source->is_active, 'bg-secondary' => !$source->is_active])>
                                    {{ $source->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td>{{ $source->sync_interval_minutes }} menit</td>
                            <td><small class="text-muted">{{ $source->last_synced_at?->format('Y-m-d H:i:s') ?? '-' }}</small></td>
                            <td>
                                <span @class([
                                    'status-success' => $lastStatus === 'success',
                                    'status-danger' => $lastStatus === 'failed',
                                    'badge bg-secondary' => !in_array($lastStatus, ['success','failed']),
                                ])>{{ $lastStatus === 'success' ? 'Berhasil' : ($lastStatus === 'failed' ? 'Gagal' : ($source->last_status ?? '-')) }}</span>
                            </td>
                            <td>
                                <div class="d-flex gap-1 flex-wrap" style="min-width:180px;">
                                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('data-sources.edit', $source) }}"><i class="bi bi-pencil"></i></a>
                                    <form method="post" action="{{ route('data-sources.test', $source) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-info"><i class="bi bi-plug"></i></button>
                                    </form>
                                    <form method="post" action="{{ route('data-sources.sync', $source) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-primary" @disabled(!$source->is_active)><i class="bi bi-arrow-repeat"></i></button>
                                    </form>
                                    <form method="post" action="{{ route('data-sources.status', $source) }}" class="d-inline">
                                        @csrf
                                        @method('patch')
                                        <input type="hidden" name="is_active" value="{{ $source->is_active ? 0 : 1 }}">
                                        <button type="submit" @class(['btn btn-sm', $source->is_active ? 'btn-outline-danger' : 'btn-outline-success'])>
                                            <i class="bi {{ $source->is_active ? 'bi-pause-circle' : 'bi-play-circle' }}"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">Belum ada sumber data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    {{ $sources->links('partials.pagination') }}
</div>
@endsection
