@props(['central' => false])

<span {{ $attributes->merge(['class' => 'badge wh-type-badge ' . ($central ? 'badge-primary' : 'badge-info')]) }}>
    @if($central)
        <x-lucide-building-2 />
        Gudang Pusat
    @else
        <x-lucide-hard-hat />
        Gudang Proyek
    @endif
</span>
