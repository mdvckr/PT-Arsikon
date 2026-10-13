{{-- Liquid Glass (iOS 26 Style) Banner Card Component --}}
<div 
    x-data
    :class="{
        'glass': true,
        'glass-tint-info': banner.type === 'info',
        'glass-tint-success': banner.type === 'success',
        'glass-tint-warning': banner.type === 'warning',
        'glass-tint-danger': banner.type === 'danger',
        '!rounded-full': !banner.expanded,
        '!rounded-3xl shadow-2xl': banner.expanded,
    }"
    class="pointer-events-auto w-full transition-all duration-300 ease-out select-none cursor-pointer overflow-hidden p-2.5 sm:p-3 relative group"
    :style="`transform: translateY(${banner.currentDeltaY || 0}px) translateX(${banner.currentDeltaX || 0}px);`"
    @click="openNotification(banner)"
    @mouseenter="pauseTimer(banner)"
    @mouseleave="resumeTimer(banner)"
    @touchstart="handleTouchStart($event, banner)"
    @touchmove="handleTouchMove($event, banner)"
    @touchend="handleTouchEnd($event, banner)"
    role="status"
    aria-live="polite"
>
    <!-- Top Glass Rim Highlight -->
    <div class="absolute inset-x-4 top-0 h-[1px] bg-gradient-to-r from-transparent via-white/90 to-transparent pointer-events-none"></div>

    <div class="flex items-center gap-3">
        <!-- Leading Icon Pill -->
        <div 
            :class="{
                'bg-sky-500/15 text-sky-600 ring-1 ring-sky-500/30': banner.type === 'info',
                'bg-emerald-500/15 text-emerald-600 ring-1 ring-emerald-500/30': banner.type === 'success',
                'bg-amber-500/15 text-amber-600 ring-1 ring-amber-500/30': banner.type === 'warning',
                'bg-rose-500/15 text-rose-600 ring-1 ring-rose-500/30': banner.type === 'danger',
            }"
            class="w-10 h-10 sm:w-11 sm:h-11 rounded-full flex items-center justify-center text-[15px] sm:text-base flex-shrink-0 shadow-sm backdrop-blur-md transition-transform duration-200 group-hover:scale-105"
        >
            <template x-if="banner.type === 'info'">
                <i class="fas fa-circle-info"></i>
            </template>
            <template x-if="banner.type === 'success'">
                <i class="fas fa-circle-check"></i>
            </template>
            <template x-if="banner.type === 'warning'">
                <i class="fas fa-triangle-exclamation"></i>
            </template>
            <template x-if="banner.type === 'danger'">
                <i class="fas fa-circle-xmark"></i>
            </template>
        </div>

        <!-- Center Content -->
        <div class="flex-1 min-w-0 pr-1">
            <!-- Meta Tag & Time -->
            <div class="flex items-center justify-between gap-2 mb-0.5">
                <span 
                    :class="{
                        'bg-sky-500/15 text-sky-700': banner.type === 'info',
                        'bg-emerald-500/15 text-emerald-700': banner.type === 'success',
                        'bg-amber-500/15 text-amber-800': banner.type === 'warning',
                        'bg-rose-500/15 text-rose-700': banner.type === 'danger',
                    }"
                    class="text-[9.5px] uppercase tracking-wider font-extrabold px-2 py-0.5 rounded-full"
                    x-text="banner.type === 'success' ? 'Berhasil' : (banner.type === 'warning' ? 'Perhatian' : (banner.type === 'danger' ? 'Kritis' : 'Informasi'))"
                ></span>
                <span class="text-[10.5px] text-slate-500 font-medium" x-text="banner.time || 'Baru saja'"></span>
            </div>

            <!-- Title -->
            <div class="text-[13px] font-bold text-slate-900 leading-snug truncate" x-text="banner.title"></div>

            <!-- Message (Compact vs Expanded) -->
            <div 
                :class="banner.expanded ? 'line-clamp-none mt-1 text-slate-700' : 'line-clamp-1 sm:line-clamp-2 text-slate-600'"
                class="text-[12px] leading-relaxed transition-all duration-200"
                x-text="banner.message"
            ></div>

            <!-- Expanded Extra Actions -->
            <template x-if="banner.expanded && banner.url">
                <div class="mt-2.5 pt-2 border-t border-slate-200/60 flex items-center justify-between gap-2">
                    <span class="text-[11px] font-semibold text-blue-600 hover:text-blue-700 flex items-center gap-1">
                        <span>Buka Halaman</span>
                        <i class="fas fa-arrow-right text-[9px]"></i>
                    </span>
                    <button 
                        type="button" 
                        class="text-[11px] text-slate-500 hover:text-slate-800 font-medium px-2 py-0.5 rounded-md hover:bg-slate-200/50"
                        @click.stop="toggleExpand(banner.id)"
                    >
                        Tutup Kartu
                    </button>
                </div>
            </template>
        </div>

        <!-- Trailing Actions (Expand Arrow / Dismiss) -->
        <div class="flex items-center gap-1 flex-shrink-0">
            <!-- Expand Toggle (Long message or has URL) -->
            <template x-if="banner.url || (banner.message && banner.message.length > 50)">
                <button 
                    type="button" 
                    class="w-7 h-7 rounded-full flex items-center justify-center text-slate-400 hover:text-slate-700 hover:bg-white/60 transition-colors text-[11px]"
                    title="Perluas / Perkecil"
                    @click.stop="toggleExpand(banner.id)"
                >
                    <i class="fas" :class="banner.expanded ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                </button>
            </template>

            <!-- Close Button -->
            <button 
                type="button" 
                class="w-7 h-7 rounded-full flex items-center justify-center text-slate-400 hover:text-rose-600 hover:bg-rose-50/80 transition-colors text-[12px]"
                title="Tutup Notifikasi"
                @click.stop="dismissBanner(banner.id)"
            >
                <i class="fas fa-xmark"></i>
            </button>
        </div>
    </div>
</div>
