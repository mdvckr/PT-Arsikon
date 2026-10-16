{{-- Liquid Glass (iOS 26 Style) Dropdown Notification Panel Component (Black Text, Ultra High Contrast) --}}
<div 
    id="glass-notification-panel-wrapper"
    x-show="panelOpen"
    x-transition:enter="transition cubic-bezier(0.16, 1, 0.3, 1) duration-300"
    x-transition:enter-start="opacity-0 scale-95 -translate-y-3"
    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 scale-100 translate-y-0"
    x-transition:leave-end="opacity-0 scale-95 -translate-y-2"
    @click.away="panelOpen = false"
    class="absolute right-0 top-full mt-3 w-[420px] max-w-[calc(100vw-32px)] rounded-[28px] overflow-hidden z-50 pointer-events-auto
      bg-white/95 backdrop-blur-3xl ios26-liquid-panel
      border border-white/90
      shadow-[inset_0_2px_3px_rgba(255,255,255,1),inset_0_-1px_1px_rgba(0,0,0,0.05),0_25px_50px_-12px_rgba(0,0,0,0.22)]"
    style="display: none;"
>
    <!-- Kilau Permukaan Kaca Atas -->
    <div class="pointer-events-none absolute inset-x-0 top-0 h-[1.5px] bg-gradient-to-r from-transparent via-white to-transparent opacity-90"></div>

    <!-- Header Dropdown -->
    <div class="relative z-10 px-5 py-4 flex items-center justify-between border-b border-slate-200/80 bg-white/40">
        <div class="flex items-center gap-2.5">
            <h3 class="text-base font-black text-black tracking-tight">Pemberitahuan</h3>
            <template x-if="unreadCount > 0">
                <span 
                    class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider
                        bg-rose-100 text-rose-900 border border-rose-300 shadow-sm"
                    x-text="`${unreadCount} baru`"
                ></span>
            </template>
        </div>
        <template x-if="unreadCount > 0">
            <button 
                type="button"
                @click="markAllAsRead()"
                class="text-xs font-black text-indigo-700 hover:text-indigo-950 transition flex items-center gap-1 cursor-pointer"
            >
                <span>Tandai Semua</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
            </button>
        </template>
    </div>

    <!-- Daftar Item Notifikasi -->
    <div class="relative z-10 p-3.5 flex flex-col gap-2.5 max-h-[380px] overflow-y-auto scrollbar-thin">
        
        <!-- Loading State -->
        <template x-if="panelLoading">
            <div class="py-8 text-center text-slate-700 text-xs flex flex-col items-center justify-center gap-2">
                <svg class="w-6 h-6 animate-spin text-indigo-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="font-bold">Memuat notifikasi...</span>
            </div>
        </template>

        <!-- Empty State -->
        <template x-if="!panelLoading && panelItems.length === 0">
            <div class="py-8 px-4 text-center rounded-2xl bg-slate-50/80 border border-slate-200/80">
                <div class="w-12 h-12 rounded-2xl bg-slate-200/80 flex items-center justify-center mx-auto mb-2 text-slate-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                </div>
                <div class="text-sm font-black text-black">Tidak Ada Notifikasi Baru</div>
                <p class="text-xs font-semibold text-slate-600 mt-1 max-w-[240px] mx-auto">Semua aktivitas logistik dan permohonan telah Anda tinjau.</p>
            </div>
        </template>

        <!-- Unread Items -->
        <template x-if="!panelLoading && panelItems.length > 0">
            <div class="flex flex-col gap-2.5">
                <template x-for="item in panelItems" :key="item.id">
                    <!-- Item Notifikasi (Glass Card dengan Teks Hitam Pekat) -->
                    <div 
                        @click="markSingleAsRead(item.id, item.url)"
                        class="group relative rounded-2xl p-3.5 flex items-start gap-3 transition-all duration-200 cursor-pointer
                            bg-white hover:bg-slate-50/90
                            border border-slate-200/80 hover:border-slate-300
                            shadow-sm hover:shadow"
                    >
                        <!-- Ikon Status (Glass Squircle) -->
                        <div 
                            :class="{
                                'bg-amber-100 border-amber-300 text-amber-700': item.type === 'warning',
                                'bg-emerald-100 border-emerald-300 text-emerald-700': item.type === 'success',
                                'bg-rose-100 border-rose-300 text-rose-700': item.type === 'danger',
                                'bg-blue-100 border-blue-300 text-blue-700': item.type === 'info' || !item.type
                            }"
                            class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 border shadow-xs transition-transform duration-200 group-hover:scale-105"
                        >
                            <template x-if="item.type === 'warning'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </template>
                            <template x-if="item.type === 'success'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </template>
                            <template x-if="item.type === 'danger'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </template>
                            <template x-if="item.type === 'info' || !item.type">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </template>
                        </div>

                        <!-- Teks Informasi: 100% HITAM PEKAT -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-2 mb-0.5">
                                <h4 class="text-sm font-black text-black truncate group-hover:text-indigo-700 transition-colors" x-text="item.title"></h4>
                                <span class="text-[11px] font-bold text-slate-500 shrink-0" x-text="item.created_at_human"></span>
                            </div>
                            <p class="text-xs font-semibold text-slate-800 leading-relaxed line-clamp-2" x-text="item.message"></p>
                        </div>

                        <!-- Indikator Belum Dibaca (Blue Dot Glow) -->
                        <div class="w-2.5 h-2.5 rounded-full bg-blue-600 shrink-0 shadow-[0_0_8px_#2563eb] self-center"></div>
                    </div>
                </template>
            </div>
        </template>

    </div>

    <!-- Footer Dropdown -->
    <div class="relative z-10 px-5 py-3.5 border-t border-slate-200/80 text-center bg-slate-50/80">
        <a href="{{ route('notifications.index') }}" class="text-xs font-black text-black hover:text-indigo-700 transition flex items-center justify-center gap-1.5">
            <span>Buka Semua Riwayat Notifikasi</span>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
            </svg>
        </a>
    </div>
</div>
