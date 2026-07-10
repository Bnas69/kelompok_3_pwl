@php
    $icons = [
        'success' => 'bi-check-circle-fill',
        'danger' => 'bi-exclamation-circle-fill',
        'warning' => 'bi-exclamation-triangle-fill',
        'info' => 'bi-info-circle-fill',
    ];
    $icon = $icons[$type ?? 'info'] ?? 'bi-info-circle-fill';
@endphp
<div class="alert alert-{{ $type ?? 'info' }} d-flex align-items-center gap-2 py-2 px-3 mb-3" role="alert" style="border-radius:10px;border:none;">
    <i class="bi {{ $icon }}"></i> {{ $message ?? $slot }}
</div>
