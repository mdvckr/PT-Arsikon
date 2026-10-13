{{-- Liquid Glass (iOS 26 Style) Dropdown Notification Panel Component --}}
<div 
    id="glass-notification-panel-wrapper"
    x-show="panelOpen"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 scale-100 translate-y-0"
    x-transition:leave-end="opacity-0 scale-95 -translate-y-1.5"
    @click.away="panelOpen = false"
    class="absolute right-0 top-12 z-50 pointer-events-auto"
    style="display: none;"
>
    <div
        id="glass-notification-panel"
        class="w-[340px] sm:w-[380px] max-w-[calc(100vw-32px)] glass text-slate-800 dark:text-slate-100"
    >
        <!-- Header: Satu permukaan kaca utuh tanpa latar terpisah -->
        <div class="px-4 py-3.5 border-b glass-divider flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="text-[14px] font-extrabold tracking-tight glass-title">Pemberitahuan</span>
                <template x-if="unreadCount > 0">
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-rose-500/15 text-rose-600 dark:text-rose-400 border border-rose-500/20" x-text="`${unreadCount} baru`"></span>
                </template>
            </div>

            <template x-if="unreadCount > 0">
                <button 
                    type="button" 
                    @click="markAllAsRead()"
                    class="text-[11px] font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400 hover:underline flex items-center gap-1"
                >
                    <i class="fas fa-check-double text-[10px]"></i>
                    <span>Tandai Semua</span>
                </button>
            </template>
        </div>

        <!-- Content List -->
        <div class="max-h-[380px] overflow-y-auto divide-y glass-divider scrollbar-thin">
            <!-- Loading Skeleton -->
            <template x-if="panelLoading">
                <div class="p-6 text-center text-slate-400 text-xs">
                    <i class="fas fa-circle-notch fa-spin text-base text-blue-500 mb-2 block"></i>
                    <span>Memuat notifikasi...</span>
                </div>
            </template>

            <!-- Empty State -->
            <template x-if="!panelLoading && panelItems.length === 0">
                <div class="p-8 text-center">
                    <div class="w-12 h-12 rounded-full bg-black/5 dark:bg-white/10 glass-body flex items-center justify-center mx-auto mb-2 text-base">
                        <i class="fas fa-bell-slash"></i>
                    </div>
                    <div class="text-[13px] font-bold glass-title">Tidak Ada Notifikasi Baru</div>
                    <p class="text-[11.5px] glass-body mt-1 max-w-[220px] mx-auto">Semua aktivitas logistik dan permohonan telah Anda tinjau.</p>
                </div>
            </template>

            <!-- Unread Items -->
            <template x-if="!panelLoading && panelItems.length > 0">
                <div>
                    <template x-for="item in panelItems" :key="item.id">
                        <div 
                            @click="markSingleAsRead(item.id, item.url)"
                            class="p-3.5 glass-row transition-colors cursor-pointer flex items-start gap-3 group relative"
                        >
                            <!-- Icon with refined tinting -->
                            <div 
                                :class="{
                                    'bg-sky-500/15 text-sky-600': item.type === 'info',
                                    'bg-emerald-500/15 text-emerald-600': item.type === 'success',
                                    'bg-amber-400/25 text-amber-600': item.type === 'warning',
                                    'bg-rose-500/15 text-rose-600': item.type === 'danger',
                                }"
                                class="w-9 h-9 rounded-full flex items-center justify-center text-xs flex-shrink-0 mt-0.5"
                            >
                                <template x-if="item.type === 'info'"><i class="fas fa-circle-info"></i></template>
                                <template x-if="item.type === 'success'"><i class="fas fa-circle-check"></i></template>
                                <template x-if="item.type === 'warning'"><i class="fas fa-triangle-exclamation"></i></template>
                                <template x-if="item.type === 'danger'"><i class="fas fa-circle-xmark"></i></template>
                            </div>

                            <!-- Text -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-1 mb-0.5">
                                    <span class="text-[12px] font-bold glass-title truncate group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors" x-text="item.title"></span>
                                    <span class="text-[10px] glass-body flex-shrink-0 opacity-80" x-text="item.created_at_human"></span>
                                </div>
                                <p class="text-[11.5px] glass-body line-clamp-2 leading-relaxed" x-text="item.message"></p>
                            </div>

                            <!-- Read Dot: iOS 26 glow shadow -->
                            <span class="w-2 h-2 rounded-full bg-blue-600 flex-shrink-0 mt-2 shadow-[0_0_8px_rgba(59,130,246,0.6)]"></span>
                        </div>
                    </template>
                </div>
            </template>
        </div>

        <!-- Footer: Tanpa blok abu-abu, menyatu dengan permukaan kaca -->
        <div class="p-2.5 border-t glass-divider text-center">
            <a 
                href="{{ route('notifications.index') }}" 
                class="text-[11.5px] font-semibold glass-title inline-flex items-center gap-1.5 py-1.5 px-3 rounded-xl hover:bg-white/20 dark:hover:bg-white/10 transition-all"
            >
                <span>Buka Semua Riwayat Notifikasi</span>
                <i class="fas fa-arrow-right text-[10px] opacity-75"></i>
            </a>
        </div>
    </div>
</div>
