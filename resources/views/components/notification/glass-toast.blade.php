@props([
    'type' => 'success',
    'title' => null,
    'message' => '',
])

@php
    $type = strtolower($type);
    if ($type === 'danger') $type = 'error';

    $cardBg = match($type) {
        'info', 'blue' => 'bg-[#f0f9ff] border-[#e0f2fe]',
        'warning' => 'bg-[#fffbeb] border-[#fef3c7]',
        'error' => 'bg-[#fef2f2] border-[#fee2e2]',
        default => 'bg-[#f0fdf4] border-[#dcfce7]',
    };

    $badgeBg = match($type) {
        'info', 'blue' => 'bg-[#0ea5e9]',
        'warning' => 'bg-[#f59e0b]',
        'error' => 'bg-[#ef4444]',
        default => 'bg-[#10b981]',
    };
@endphp

<div 
    x-data="{ show: true }"
    x-show="show"
    x-init="setTimeout(() => show = false, 5000)"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="-translate-y-4 opacity-0 scale-95"
    x-transition:enter-end="translate-y-0 opacity-100 scale-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 scale-100"
    x-transition:leave-end="-translate-y-4 opacity-0 scale-95"
    class="{{ $cardBg }} border rounded-[10px] sm:rounded-xl p-2.5 sm:p-3 shadow-[0_10px_22px_-4px_rgba(15,23,42,0.08),0_3px_8px_-2px_rgba(15,23,42,0.04)] flex items-start justify-between gap-2.5 w-full max-w-[360px] pointer-events-auto mx-auto"
    role="status"
>
    <div class="flex items-start gap-2.5 flex-1 min-w-0">
        <!-- Circular White Disc with Solid Badge -->
        <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-white shadow-[0_1px_2px_rgba(0,0,0,0.05)] border border-slate-100 flex items-center justify-center flex-shrink-0 mt-0.5">
            <div class="w-4 h-4 sm:w-[18px] sm:h-[18px] rounded-full {{ $badgeBg }} flex items-center justify-center text-white">
                @if($type === 'info' || $type === 'blue')
                    <span class="font-serif italic font-bold text-[10px]">i</span>
                @elseif($type === 'warning')
                    <span class="font-bold text-[10px] leading-none">!</span>
                @elseif($type === 'error')
                    <svg class="w-2.5 h-2.5 sm:w-3 sm:h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/>
                        <line x1="12" y1="9" x2="12" y2="13"/>
                        <line x1="12" y1="17" x2="12.01" y2="17"/>
                    </svg>
                @else
                    <svg class="w-2.5 h-2.5 sm:w-3 sm:h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                @endif
            </div>
        </div>
        <div class="flex-1 min-w-0 pt-0.5">
            @if($title)
                <h4 class="text-[12px] sm:text-[13px] font-bold text-[#0f172a] leading-tight">{{ $title }}</h4>
            @endif
            <p class="text-[11px] sm:text-[11.5px] text-[#475569] leading-snug {{ $title ? 'mt-0.5' : '' }}">{{ $message }}</p>
        </div>
    </div>
    <!-- Minimalist Close Button -->
    <button type="button" @click.stop="show = false" class="text-slate-400 hover:text-slate-600 w-6 h-6 rounded-md hover:bg-black/5 flex items-center justify-center transition cursor-pointer flex-shrink-0 -mr-1 -mt-1" aria-label="Tutup">
        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="18" y1="6" x2="6" y2="18"/>
            <line x1="6" y1="6" x2="18" y2="18"/>
        </svg>
    </button>
</div>
