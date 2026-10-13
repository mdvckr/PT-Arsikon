<div 
    id="glass-notification-stack"
    class="fixed top-3 sm:top-5 left-1/2 -translate-x-1/2 z-[9999] w-[calc(100vw-24px)] max-w-[430px] pointer-events-none flex flex-col items-center gap-2.5 transition-all duration-300"
    x-init="
        @if(session('success'))
            $nextTick(() => pushBanner({
                title: 'Berhasil',
                message: {{ json_encode(session('success')) }},
                type: 'success',
                sound_type: 'success'
            }));
        @endif
        @if(session('error'))
            $nextTick(() => pushBanner({
                title: 'Perhatian / Gagal',
                message: {{ json_encode(session('error')) }},
                type: 'danger',
                sound_type: 'warning'
            }));
        @endif
        @if(session('warning'))
            $nextTick(() => pushBanner({
                title: 'Peringatan Sistem',
                message: {{ json_encode(session('warning')) }},
                type: 'warning',
                sound_type: 'warning'
            }));
        @endif
    "
>
    <!-- Loop through active banners (max 3 stacked) -->
    <template x-for="(banner, index) in banners" :key="banner.id">
        <div 
            class="w-full pointer-events-auto transition-all duration-300 ease-out"
            :style="`transform: scale(${index === 0 ? 1 : (index === 1 ? 0.96 : 0.92)});`"
            x-show="!banner.isClosing"
            x-transition:enter="transition cubic-bezier(0.16, 1, 0.3, 1) duration-350"
            x-transition:enter-start="-translate-y-8 opacity-0 scale-90 blur-md"
            x-transition:enter-end="translate-y-0 opacity-100 scale-100 blur-0"
            x-transition:leave="transition cubic-bezier(0.16, 1, 0.3, 1) duration-250"
            x-transition:leave-start="translate-y-0 opacity-100 scale-100"
            x-transition:leave-end="-translate-y-6 opacity-0 scale-90 blur-sm"
        >
            <x-notification.glass-banner />
        </div>
    </template>
</div>
