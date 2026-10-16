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
    $iconClass = match($type) {
        'info' => 'fa-circle-info text-sky-600 bg-sky-500/15',
        'warning' => 'fa-triangle-exclamation text-amber-600 bg-amber-500/15',
        'danger', 'error' => 'fa-circle-xmark text-rose-600 bg-rose-500/15',
        default => 'fa-circle-check text-emerald-600 bg-emerald-500/15',
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
    <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 text-sm {{ $iconClass }}">
        <i class="fas {{ explode(' ', $iconClass)[0] }}"></i>
    </div>
    <div class="flex-1 min-w-0">
        @if($title)
            <div class="text-[12px] font-black text-black leading-tight">{{ $title }}</div>
        @endif
        <div class="text-[11.5px] font-semibold text-slate-900 leading-snug truncate">{{ $message }}</div>
    </div>
    <button type="button" @click.stop="show = false" class="text-slate-600 hover:text-black text-xs px-1">
        <i class="fas fa-xmark"></i>
    </button>
</div>
