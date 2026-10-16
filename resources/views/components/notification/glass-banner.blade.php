{{-- iOS 26 Liquid Glass Toast Banner Component (Pure Black Text, High Contrast, Crystal Clean) --}}
<div 
    x-data
    :class="{
        'ios26-liquid-toast': true,
        'ios26-toast-warning': banner.type === 'warning',
        'ios26-toast-danger': banner.type === 'danger',
        'ios26-toast-info': banner.type === 'info',
        'shadow-2xl': banner.expanded,
    }"
    class="relative overflow-hidden rounded-[24px] p-4 sm:p-4.5 flex items-start gap-3.5 max-w-xl w-full
           pointer-events-auto select-none transition-all duration-300 ease-out group"
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
    <!-- Efek Kilau Specular Halus Kaca Atas -->
    <div class="pointer-events-none absolute inset-x-0 top-0 h-[1.5px] bg-gradient-to-r from-transparent via-white to-transparent opacity-90"></div>

    <!-- Kotak Ikon Status (Glass Squircle 3D) -->
    <div 
        :class="{
            'ios26-squircle-warning': banner.type === 'warning',
            'ios26-squircle-danger': banner.type === 'danger',
            'ios26-squircle-info': banner.type === 'info',
        }"
        class="ios26-glass-squircle rounded-2xl w-11 h-11 flex items-center justify-center shrink-0 relative z-10 transition-transform duration-200 group-hover:scale-105"
    >
        <div 
            :class="{
                'bg-emerald-600 shadow-[0_0_10px_rgba(5,150,105,0.45)]': banner.type === 'success' || !banner.type,
                'bg-amber-500 shadow-[0_0_10px_rgba(245,158,11,0.45)]': banner.type === 'warning',
                'bg-rose-600 shadow-[0_0_10px_rgba(225,29,72,0.45)]': banner.type === 'danger',
                'bg-blue-600 shadow-[0_0_10px_rgba(37,99,235,0.45)]': banner.type === 'info',
            }"
            class="w-6 h-6 rounded-full flex items-center justify-center transition-colors duration-200"
        >
            <!-- Success Icon (Check) -->
            <template x-if="banner.type === 'success' || !banner.type">
                <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
            </template>
            <!-- Warning Icon (Exclamation) -->
            <template x-if="banner.type === 'warning'">
                <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01" />
                </svg>
            </template>
            <!-- Danger Icon (X) -->
            <template x-if="banner.type === 'danger'">
                <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </template>
            <!-- Info Icon (i) -->
            <template x-if="banner.type === 'info'">
                <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01" />
                </svg>
            </template>
        </div>
    </div>

    <!-- Konten Teks: Teks Hitam Pekat, Kontras Tinggi & Sangat Terbaca -->
    <div class="relative z-10 flex-1 min-w-0 pr-7">
        <!-- Header Bar: Status Badge + Waktu -->
        <div class="flex items-center gap-2 mb-1 flex-wrap">
            <span 
                :class="{
                    'bg-emerald-100 text-emerald-950 border-emerald-300': banner.type === 'success' || !banner.type,
                    'bg-amber-100 text-amber-950 border-amber-300': banner.type === 'warning',
                    'bg-rose-100 text-rose-950 border-rose-300': banner.type === 'danger',
                    'bg-blue-100 text-blue-950 border-blue-300': banner.type === 'info',
                }"
                class="rounded-full border px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider shadow-sm"
                x-text="banner.type === 'success' || !banner.type ? 'BERHASIL' : (banner.type === 'warning' ? 'PERHATIAN' : (banner.type === 'danger' ? 'PENTING' : 'INFORMASI'))"
            ></span>
            <span class="text-[11px] font-bold text-slate-600" x-text="banner.time || '1 detik yang lalu'"></span>
        </div>

        <!-- Judul: TEKS HITAM PEKAT (BLACK) -->
        <h4 class="text-sm sm:text-[15px] font-black text-black tracking-tight leading-snug break-words" x-text="banner.title"></h4>

        <!-- Pesan: TEKS HITAM PEKAT (BLACK) -->
        <p 
            :class="banner.expanded ? 'line-clamp-none mt-1' : 'line-clamp-2 mt-0.5'"
            class="text-xs sm:text-[13px] font-bold text-slate-950 leading-relaxed break-words" 
            x-text="banner.message"
        ></p>

        <!-- Aksi Cepat / Link Buka Halaman -->
        <template x-if="banner.url">
            <div class="mt-2.5 pt-2 border-t border-slate-300/80 flex items-center justify-between gap-3">
                <span class="text-xs font-black text-blue-800 hover:text-blue-950 inline-flex items-center gap-1.5 transition">
                    <span>Buka Dokumen</span>
                    <svg class="w-3.5 h-3.5 text-blue-800" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </span>
                <button 
                    type="button" 
                    class="text-[11px] text-slate-800 hover:text-black font-bold px-2 py-0.5 rounded-lg hover:bg-black/5 transition"
                    @click.stop="toggleExpand(banner.id)"
                    x-text="banner.expanded ? 'Perkecil' : 'Baca Selengkapnya'"
                ></button>
            </div>
        </template>
    </div>

    <!-- Tombol Tutup & Perluas di Sudut Kanan Atas (Rapi & Sejajar) -->
    <div class="absolute top-3.5 right-3.5 z-20 flex items-center gap-1.5">
        <!-- Expand Toggle (Jika pesan panjang atau ada tautan) -->
        <template x-if="banner.url || (banner.message && banner.message.length > 70)">
            <button 
                type="button" 
                class="w-7 h-7 rounded-full bg-black/5 hover:bg-black/15 text-slate-900 hover:text-black flex items-center justify-center active:scale-95 transition"
                title="Perluas / Perkecil"
                @click.stop="toggleExpand(banner.id)"
            >
                <svg class="w-3.5 h-3.5 text-slate-900" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" :class="banner.expanded ? 'rotate-180' : ''">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
        </template>

        <!-- Tombol Tutup (X) -->
        <button 
            type="button"
            class="w-7 h-7 rounded-full bg-black/5 hover:bg-black/15 text-slate-900 hover:text-black flex items-center justify-center active:scale-95 transition"
            title="Tutup Notifikasi"
            @click.stop="dismissBanner(banner.id)"
        >
            <svg class="w-3.5 h-3.5 text-slate-900" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <!-- Progress Bar (Tipis, Elegan, Masuk ke Dalam Margin) -->
    <div class="absolute bottom-1.5 left-5 right-5 h-[3px] rounded-full overflow-hidden bg-slate-300/60 pointer-events-none">
        <div 
            class="h-full rounded-full glass-progress-bar"
            :class="{
                'bg-emerald-600 shadow-[0_0_8px_#059669]': banner.type === 'success' || !banner.type,
                'bg-amber-500 shadow-[0_0_8px_#f59e0b]': banner.type === 'warning',
                'bg-rose-600 shadow-[0_0_8px_#e11d48]': banner.type === 'danger',
                'bg-blue-600 shadow-[0_0_8px_#2563eb]': banner.type === 'info',
                'glass-progress-paused': banner.paused
            }"
            :style="`--toast-duration: ${banner.duration || 5500}ms;`"
        ></div>
    </div>
</div>
