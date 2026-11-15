@props(['count' => 0])

<span 
    x-show="unreadCount > 0"
    x-text="unreadCount > 99 ? '99+' : unreadCount"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="scale-50 opacity-0"
    x-transition:enter-end="scale-100 opacity-100"
    class="notif-badge absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 rounded-full bg-gradient-to-tr from-rose-500 via-rose-600 to-red-600 text-white text-[9.5px] font-extrabold flex items-center justify-center shadow-[0_2px_6px_rgba(225,29,72,0.5)] ring-2 ring-[#0f172a] pointer-events-none select-none z-10"
    style="{{ $count > 0 ? '' : 'display: none;' }}"
>
    {{ $count > 99 ? '99+' : $count }}
</span>
