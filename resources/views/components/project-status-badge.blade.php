@props(['status' => null])

@php
    $normalized = match ($status) {
        'ongoing', 'active', 'berjalan' => 'active',
        'suspended', 'on_hold' => 'on_hold',
        'completed', 'selesai' => 'completed',
        'planning', 'perencanaan' => 'planning',
        default => null,
    };

    $config = match ($normalized) {
        'active' => ['label' => 'Berjalan', 'class' => 'badge-success'],
        'completed' => ['label' => 'Selesai', 'class' => 'badge-info'],
        'on_hold' => ['label' => 'Ditangguhkan', 'class' => 'badge-warning'],
        'planning' => ['label' => 'Perencanaan', 'class' => 'badge-gray'],
        default => ['label' => $status ? ucfirst(str_replace('_', ' ', $status)) : 'Tanpa Status', 'class' => 'badge-gray'],
    };
@endphp

<span {{ $attributes->merge(['class' => 'badge proj-status ' . $config['class']]) }}>
    @switch($normalized)
        @case('active')
            <x-lucide-activity />
            @break
        @case('completed')
            <x-lucide-circle-check />
            @break
        @case('on_hold')
            <x-lucide-pause />
            @break
        @case('planning')
            <x-lucide-clock />
            @break
        @default
            <x-lucide-circle-dot />
    @endswitch
    {{ $config['label'] }}
</span>
