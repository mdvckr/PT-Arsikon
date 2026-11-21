@props(['active' => true])

<span {{ $attributes->merge(['class' => 'badge wh-status-badge ' . ($active ? 'badge-success' : 'badge-gray')]) }}>
    @if($active)
        <x-lucide-circle-check />
        Aktif
    @else
        <x-lucide-power-off />
        Nonaktif
    @endif
</span>
