@php
    $mapping = [
        'success' => ['class' => 'status-success', 'label' => 'Berhasil'],
        'failed' => ['class' => 'status-danger', 'label' => 'Gagal'],
        'partial' => ['class' => 'status-warning', 'label' => 'Partial'],
        'high' => ['class' => 'status-danger', 'label' => 'High'],
        'medium' => ['class' => 'status-warning', 'label' => 'Medium'],
        'low' => ['class' => 'status-success', 'label' => 'Low'],
    ];
    $item = $mapping[$status ?? ''] ?? ['class' => 'badge bg-secondary', 'label' => $label ?? $status ?? '-'];
@endphp
<span class="{{ $item['class'] }}">{{ $item['label'] }}</span>
