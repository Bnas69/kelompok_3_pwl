@extends('layouts.app')

@section('title', 'Riwayat Sync')

@php use Illuminate\Support\Str; @endphp

@section('content')
<div class="page-header">
    <div>
        <p class="page-eyebrow">Audit Sinkronisasi</p>
        <h1>Riwayat Sync</h1>
    </div>
</div>

<div class="card-dashboard">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Sumber</th>
                    <th>Status</th>
                    <th>Ditemukan</th>
                    <th>Masuk</th>
                    <th>Update</th>
                    <th>Duplikat</th>
                    <th>Gagal</th>
                    <th>Mulai</th>
                    <th>Selesai</th>
                    <th>Error</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($logs as $log)
                    <tr>
                        <td>{{ $log->source?->name ?? 'Manual Import' }}</td>
                        <td>
                            <span @class([
                                'status-success' => $log->status === 'success',
                                'status-danger' => $log->status === 'failed',
                                'status-warning' => $log->status === 'partial',
                                'badge bg-secondary' => !in_array($log->status, ['success','failed','partial']),
                            ])>{{ $log->status }}</span>
                        </td>
                        <td>{{ $log->total_found }}</td>
                        <td>{{ $log->total_inserted }}</td>
                        <td>{{ $log->total_updated }}</td>
                        <td>{{ $log->total_duplicate }}</td>
                        <td>{{ $log->total_failed }}</td>
                        <td><small class="text-muted">{{ $log->started_at?->format('Y-m-d H:i:s') }}</small></td>
                        <td><small class="text-muted">{{ $log->finished_at?->format('Y-m-d H:i:s') }}</small></td>
                        <td><small class="text-muted">{{ $log->error_message ? Str::limit($log->error_message, 80) : '-' }}</small></td>
                    </tr>
                @empty
                    <tr><td colspan="10" class="text-center text-muted py-4">Belum ada log sync.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $logs->links('partials.pagination') }}
</div>
@endsection
