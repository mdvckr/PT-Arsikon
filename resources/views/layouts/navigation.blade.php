<nav x-data="{ open: false }" class="bg-indigo-900 border-b border-indigo-800 text-white">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center space-x-6">
                <!-- Logo / Brand -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center space-x-2 text-white font-bold text-lg">
                        <span class="bg-amber-500 text-indigo-950 px-2 py-1 rounded text-xs font-black">CWMS</span>
                        <span class="tracking-wide">Arsikon Warehouse</span>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-4 sm:-my-px sm:flex text-sm">
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center px-3 py-2 text-indigo-100 hover:text-white font-medium">
                        Beranda
                    </a>
                </div>
            </div>

            <!-- Active Workspace & User Menu -->
            <div class="hidden sm:flex sm:items-center sm:space-x-4">
                <!-- Active Workspace Dropdown Selector -->
                @php
                    $user = Auth::user();
                    $activeWarehouse = $user?->activeWarehouse();
                    $userWarehouses = $user?->hasRole('Owner') ? \App\Models\Warehouse::all() : $user?->warehouses;
                @endphp

                @if($activeWarehouse && $userWarehouses)
                    <div class="relative" x-data="{ wsOpen: false }">
                        <button @click="wsOpen = !wsOpen" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-indigo-800 text-amber-300 border border-indigo-700 hover:bg-indigo-700 transition">
                            <span class="mr-1">{{ $activeWarehouse->is_central ? '🏢' : '🏗️' }}</span>
                            <span>{{ $activeWarehouse->name }}</span>
                            <svg class="w-3 h-3 ms-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>

                        <div x-show="wsOpen" @click.away="wsOpen = false" class="absolute right-0 mt-2 w-72 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50 text-gray-800">
                            <div class="py-1">
                                <div class="px-4 py-2 text-xs text-gray-500 font-bold border-b">PILIH WORKSPACE / GUDANG</div>
                                @foreach($userWarehouses as $wh)
                                    <form method="POST" action="{{ route('workspace.switch') }}">
                                        @csrf
                                        <input type="hidden" name="warehouse_id" value="{{ $wh->id }}">
                                        <button type="submit" class="w-full text-left px-4 py-2 text-xs flex items-center justify-between hover:bg-indigo-50 {{ $activeWarehouse->id === $wh->id ? 'font-bold text-indigo-700 bg-indigo-50/50' : 'text-gray-700' }}">
                                            <span class="flex items-center gap-1.5">
                                                <span>{{ $wh->is_central ? '🏢' : '🏗️' }}</span>
                                                <span>{{ $wh->name }}</span>
                                            </span>
                                            @if($wh->is_central)
                                                <span class="text-[10px] bg-amber-100 text-amber-800 px-1.5 py-0.5 rounded font-bold">PUSAT</span>
                                            @else
                                                <span class="text-[10px] bg-blue-100 text-blue-800 px-1.5 py-0.5 rounded font-bold">PROYEK</span>
                                            @endif
                                        </button>
                                    </form>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                <!-- User Dropdown -->
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-indigo-700 text-sm font-medium rounded-md text-indigo-100 bg-indigo-800 hover:text-white focus:outline-none transition">
                            <div class="flex items-center space-x-2">
                                <span>{{ Auth::user()->name }}</span>
                                <span class="bg-indigo-950 text-amber-400 text-[10px] px-2 py-0.5 rounded uppercase font-bold tracking-wider">
                                    {{ Auth::user()->roles->first()?->name ?? 'User' }}
                                </span>
                            </div>
                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            Profil Pengguna
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                                Keluar (Log Out)
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger (Mobile) -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-indigo-200 hover:text-white hover:bg-indigo-800 focus:outline-none transition">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu (Mobile) -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden bg-indigo-950 border-t border-indigo-800">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" class="text-white">
                Beranda
            </x-responsive-nav-link>
        </div>

        <div class="pt-4 pb-1 border-t border-indigo-800">
            <div class="px-4 text-xs">
                <div class="font-medium text-amber-300">{{ Auth::user()->name }} ({{ Auth::user()->roles->first()?->name }})</div>
                <div class="text-indigo-300">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')" class="text-white">
                    Profil Pengguna
                </x-responsive-nav-link>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();" class="text-white">
                        Keluar (Log Out)
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
