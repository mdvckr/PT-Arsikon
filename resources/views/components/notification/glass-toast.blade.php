@props([
    'type' => 'success',
    'title' => null,
    'message' => '',
])

@php
    $tintClass = match($type) {
        'info' => 'glass-tint-info',
        'warning' => 'glass-tint-warning',
        'danger', 'error' => 'glass-tint-danger',
        default => 'glass-tint-success',
    };
    $iconColor = match($type) {
        'info' => 'text-sky-600 bg-sky-500/15',
        'warning' => 'text-amber-600 bg-amber-500/15',
        'danger', 'error' => 'text-rose-600 bg-rose-500/15',
        default => 'text-emerald-600 bg-emerald-500/15',
    };
@endphp

<div 
    x-data="{ show: true }"
    x-show="show"
    x-init="setTimeout(() => show = false, 5000)"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="-translate-y-6 opacity-0 scale-95 blur-sm"
    x-transition:enter-end="translate-y-0 opacity-100 scale-100 blur-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 scale-100"
    x-transition:leave-end="-translate-y-4 opacity-0 scale-95"
    class="glass {{ $tintClass }} !rounded-[18px] p-3 shadow-xl flex items-center gap-3 w-full max-w-sm pointer-events-auto cursor-pointer"
    @click="show = false"
    role="status"
>
    <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 text-sm {{ $iconColor }}">
        @if($type === 'info')
            <x-lucide-info class="w-4 h-4" />
        @elseif($type === 'warning')
            <x-lucide-triangle-alert class="w-4 h-4" />
        @elseif(in_array($type, ['danger', 'error']))
            <x-lucide-circle-x class="w-4 h-4" />
        @else
            <x-lucide-circle-check class="w-4 h-4" />
        @endif
    </div>
    <div class="flex-1 min-w-0">
        @if($title)
            <div class="text-[12px] font-black text-black leading-tight">{{ $title }}</div>
        @endif
        <div class="text-[11.5px] font-semibold text-slate-900 leading-snug truncate">{{ $message }}</div>
    </div>
    <button type="button" @click.stop="show = false" class="text-slate-600 hover:text-black text-xs px-1" aria-label="Tutup">
        <x-lucide-x class="w-3.5 h-3.5" />
    </button>
</div>
