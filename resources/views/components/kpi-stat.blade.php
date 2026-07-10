<div class="card-dashboard">
    <div class="card-body">
        <div class="text-muted small fw-semibold text-uppercase">{{ $label }}</div>
        <div class="fw-bold" style="font-size:{{ $size ?? '24px' }};color:{{ $color ?? 'var(--text-dark)' }};">
            {{ $value }}
        </div>
        @if (isset($sub))
            <small class="text-muted">{{ $sub }}</small>
        @endif
    </div>
</div>
