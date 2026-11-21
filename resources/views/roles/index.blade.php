<x-app-layout>
    <x-slot name="title">Manajemen Jabatan & Peran (Role)</x-slot>

    @push('styles')
    <style>
        /* Modal Overlay & Card System */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.65);
            backdrop-filter: blur(4px);
            z-index: 2000;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }
        .modal-overlay.show {
            display: flex;
        }
        .modal-box {
            background: #ffffff;
            border-radius: 16px;
            width: 100%;
            max-width: 680px;
            max-height: 90vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.25);
            border: 1px solid #e2e8f0;
            animation: modalIn 0.2s ease-out;
        }
        @keyframes modalIn {
            from { opacity: 0; transform: scale(0.97) translateY(-8px); }
            to   { opacity: 1; transform: scale(1) translateY(0); }
        }

        .modal-header {
            padding: 18px 22px;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #ffffff;
            flex-shrink: 0;
        }
        .modal-title {
            font-size: 15px;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
        }
        .btn-close-modal {
            background: none;
            border: none;
            cursor: pointer;
            color: #94a3b8;
            font-size: 18px;
            padding: 4px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.15s ease;
        }
        .btn-close-modal:hover {
            color: #0f172a;
            background: #f1f5f9;
        }
        .modal-body {
            padding: 20px 22px;
            overflow-y: auto;
            flex: 1;
            background: #fcfcfb;
        }
        .modal-footer {
            padding: 14px 22px;
            border-top: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 10px;
            background: #ffffff;
            flex-shrink: 0;
        }

        /* Compact Permission Matrix Module Cards */
        .perm-group-section {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 12px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.02);
        }
        .perm-group-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 14px;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
        }
        .perm-chips-wrap {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            padding: 12px 14px;
            background: #ffffff;
        }

        /* Interactive Clickable Permission Chip */
        .perm-check-chip {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 6px 11px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            background: #ffffff;
            font-size: 12px;
            color: #334155;
            cursor: pointer;
            user-select: none;
            transition: all 0.15s ease;
        }
        .perm-check-chip:hover {
            border-color: #ea580c;
            background: #fff7ed;
            color: #9a3412;
        }
        .perm-check-chip input[type="checkbox"] {
            accent-color: #ea580c;
            width: 14px;
            height: 14px;
            cursor: pointer;
            margin: 0;
            border-radius: 4px;
        }
        .perm-check-chip.is-active {
            background: #fff7ed;
            border-color: #fdba74;
            color: #9a3412;
            font-weight: 600;
            box-shadow: 0 1px 2px rgba(234, 88, 12, 0.08);
        }

        /* View-only pill badges */
        .perm-view-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 11px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 500;
        }
        .perm-view-pill.granted {
            background: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
            font-weight: 600;
        }
        .perm-view-pill.denied {
            background: #f8fafc;
            color: #94a3b8;
            border: 1px solid #e2e8f0;
            text-decoration: line-through;
        }
    </style>
    @endpush

    @php
        $stats = $stats ?? [
            'total_roles'     => $roles->count(),
            'protected_roles' => count($protectedRoles),
            'custom_roles'    => max(0, $roles->count() - count($protectedRoles)),
            'total_users'     => $roles->sum('users_count'),
            'total_modules'   => count($groupedPermissions),
            'total_perms'     => \Spatie\Permission\Models\Permission::count(),
        ];
    @endphp

    <div class="w-full">

        {{-- ==================== 1. HEADER (STANDAR PT ARSIKON) ==================== --}}
        <header class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mt-1">
            <div>
                <h1 class="text-[24px] leading-tight font-extrabold tracking-tight text-[#111111]">Jabatan & Hak Akses</h1>
                <p class="text-[13px] text-gray-500 mt-1">Kelola peran pengguna dan pembagian wewenang sistem PT Arsikon</p>
            </div>
            <div>
                <button type="button" onclick="openCreateRoleModal()" class="h-11 px-6 inline-flex w-full sm:w-auto items-center justify-center rounded-full bg-[#ea580c] hover:bg-[#d9480c] text-white text-[13px] font-semibold transition shadow-sm gap-2 cursor-pointer">
                    <x-lucide-plus class="w-4 h-4" />
                    Tambah Jabatan
                </button>
            </div>
        </header>

        {{-- ALERT NOTIFICATIONS --}}
        @if(session('success'))
            <div class="mt-4 flex items-center gap-3 p-4 rounded-xl bg-[#ecfdf5] border border-[#a7f3d0] text-[#065f46]">
                <x-lucide-circle-check class="w-5 h-5 flex-shrink-0 text-[#10b981]" />
                <span class="text-[13px] font-semibold">{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="mt-4 flex items-center gap-3 p-4 rounded-xl bg-[#fef2f2] border border-[#fecaca] text-[#991b1b]">
                <x-lucide-triangle-alert class="w-5 h-5 flex-shrink-0 text-[#ef4444]" />
                <span class="text-[13px] font-semibold">{{ session('error') }}</span>
            </div>
        @endif

        @if(isset($errors) && $errors->any())
            <div class="mt-4 p-4 rounded-xl bg-[#fef2f2] border border-[#fecaca] text-[#991b1b]">
                <div class="font-bold text-[13px] mb-1 flex items-center gap-2">
                    <x-lucide-triangle-alert class="w-4 h-4 text-[#ef4444]" /> Terjadi kendala saat menyimpan data jabatan:
                </div>
                <ul class="list-disc list-inside text-[12.5px] space-y-0.5">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- ==================== 2. KPI STRIP ==================== --}}
        <section class="mt-5 bg-white border border-[#e9e9e7] rounded-2xl grid grid-cols-2 md:grid-cols-4 divide-x divide-y md:divide-y-0 divide-[#efefef] overflow-hidden">
            <div class="p-5">
                <div class="flex items-start justify-between gap-2">
                    <span class="text-[12px] text-gray-500 font-medium">Total Jabatan</span>
                    <span class="w-8 h-8 rounded-[9px] bg-[#e9efff] text-[#2f6df6] flex items-center justify-center flex-shrink-0">
                        <x-lucide-shield class="w-4 h-4" />
                    </span>
                </div>
                <div class="text-[26px] leading-none font-extrabold text-[#111111] tabular-nums mt-2">{{ $stats['total_roles'] }}</div>
                <div class="text-[11px] text-gray-500 mt-2">{{ $stats['total_users'] }} akun staf terhubung</div>
            </div>

            <div class="p-5">
                <div class="flex items-start justify-between gap-2">
                    <span class="text-[12px] text-gray-500 font-medium">Sistem Inti</span>
                    <span class="w-8 h-8 rounded-[9px] bg-[#e6f7ec] text-[#16a34a] flex items-center justify-center flex-shrink-0">
                        <x-lucide-lock class="w-4 h-4" />
                    </span>
                </div>
                <div class="text-[26px] leading-none font-extrabold text-[#111111] tabular-nums mt-2">{{ $stats['protected_roles'] }}</div>
                <div class="text-[11px] text-gray-500 mt-2">Baku & terproteksi sistem</div>
            </div>

            <div class="p-5">
                <div class="flex items-start justify-between gap-2">
                    <span class="text-[12px] text-gray-500 font-medium">Jabatan Kustom</span>
                    <span class="w-8 h-8 rounded-[9px] bg-[#fdf3dd] text-[#d97706] flex items-center justify-center flex-shrink-0">
                        <x-lucide-users class="w-4 h-4" />
                    </span>
                </div>
                <div class="text-[26px] leading-none font-extrabold text-[#111111] tabular-nums mt-2">{{ $stats['custom_roles'] }}</div>
                <div class="text-[11px] text-gray-500 mt-2">Dapat dikustomisasi wewenang</div>
            </div>

            <div class="p-5">
                <div class="flex items-start justify-between gap-2">
                    <span class="text-[12px] text-gray-500 font-medium">Titik Wewenang</span>
                    <span class="w-8 h-8 rounded-[9px] bg-[#fdeaea] text-[#ea580c] flex items-center justify-center flex-shrink-0">
                        <x-lucide-key class="w-4 h-4" />
                    </span>
                </div>
                <div class="text-[26px] leading-none font-extrabold text-[#111111] tabular-nums mt-2">{{ $stats['total_perms'] }}</div>
                <div class="text-[11px] text-gray-500 mt-2">Di {{ $stats['total_modules'] }} kluster hak akses</div>
            </div>
        </section>

        {{-- ==================== 3. TABS NAVIGASI ==================== --}}
        <div class="mt-6 border-b border-[#e7e7e5] flex items-center gap-6">
            <button type="button" id="tab-btn-all" onclick="filterRolesTable('all')" class="roles-tab-btn pb-2.5 -mb-px border-b-2 border-[#ea580c] text-[13px] text-[#111111] font-semibold transition inline-flex items-center gap-1.5 cursor-pointer">
                Semua Jabatan <span class="text-[12px] text-gray-400 font-normal tabular-nums">{{ $roles->count() }}</span>
            </button>
            <button type="button" id="tab-btn-protected" onclick="filterRolesTable('protected')" class="roles-tab-btn pb-2.5 -mb-px border-b-2 border-transparent text-[13px] text-gray-500 hover:text-[#111111] transition inline-flex items-center gap-1.5 cursor-pointer">
                Sistem Inti <span class="text-[12px] text-gray-400 font-normal tabular-nums">{{ $stats['protected_roles'] }}</span>
            </button>
            <button type="button" id="tab-btn-custom" onclick="filterRolesTable('custom')" class="roles-tab-btn pb-2.5 -mb-px border-b-2 border-transparent text-[13px] text-gray-500 hover:text-[#111111] transition inline-flex items-center gap-1.5 cursor-pointer">
                Jabatan Kustom <span class="text-[12px] text-gray-400 font-normal tabular-nums">{{ $stats['custom_roles'] }}</span>
            </button>
        </div>

        {{-- ==================== 4. SEARCH FILTER ==================== --}}
        <div class="mt-4 flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <x-lucide-search class="w-4 h-4 absolute left-4 top-1/2 -translate-y-1/2 pointer-events-none text-gray-400" />
                <input type="text" id="rolesSearchInput" oninput="liveSearchRoles(this.value)"
                    placeholder="Cari nama jabatan, deskripsi kategori, atau wewenang..."
                    class="w-full h-12 pl-11 pr-4 rounded-xl bg-white border border-[#e2e2e0] text-[13px] text-[#111111] placeholder-gray-400 focus:outline-none focus:border-[#ea580c] focus:ring-2 focus:ring-[#ea580c]/20 transition">
            </div>
        </div>

        {{-- ==================== 5. TABEL JABATAN (CARD & DATA-TABLE RESMI) ==================== --}}
        <div class="card mt-4" style="background:#ffffff;border:1px solid #e9e9e7;border-radius:16px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,0.03);">
            <div class="table-wrap" style="overflow-x:auto;">
                <table class="data-table" style="width:100%;border-collapse:collapse;">
                    <thead>
                        <tr style="background:#fcfcfb;border-bottom:1px solid #ececec;">
                            <th style="width:50px;text-align:center;padding:12px 16px;font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;color:#94a3b8;">#</th>
                            <th style="padding:12px 16px;font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;color:#94a3b8;">Jabatan & Kategori Operasional</th>
                            <th style="padding:12px 16px;font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;color:#94a3b8;">Deskripsi Wewenang</th>
                            <th style="width:150px;text-align:center;padding:12px 16px;font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;color:#94a3b8;">Pengguna Aktif</th>
                            <th style="width:150px;text-align:center;padding:12px 16px;font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;color:#94a3b8;">Izin Akses</th>
                            <th style="width:130px;text-align:center;padding:12px 16px;font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;color:#94a3b8;">Tipe Status</th>
                            <th style="width:130px;text-align:center;padding:12px 16px;font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;color:#94a3b8;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="rolesTableBody">
                        @forelse($roles as $i => $role)
                        @php
                            $isProtected = in_array($role->name, $protectedRoles, true);
                            $meta = $roleMetadata[$role->name] ?? [
                                'category'    => 'Jabatan Kustom',
                                'icon'        => 'shield',
                                'description' => 'Jabatan operasional tambahan yang didefinisikan secara khusus untuk proyek.',
                            ];
                        @endphp
                        <tr class="role-table-row" data-type="{{ $isProtected ? 'protected' : 'custom' }}" data-search="{{ strtolower($role->name) }} {{ strtolower($meta['category']) }} {{ strtolower($meta['description']) }}" style="border-bottom:1px solid #edf0f5;">
                            {{-- Nomor --}}
                            <td class="text-muted" style="text-align:center;vertical-align:middle;font-weight:600;font-size:12px;color:#94a3b8;padding:14px 16px;">
                                {{ $i + 1 }}
                            </td>

                            {{-- Jabatan --}}
                            <td style="vertical-align:middle;padding:14px 16px;">
                                <div style="display:flex;align-items:center;gap:12px;">
                                    <div style="width:36px;height:36px;border-radius:10px;background:{{ $isProtected ? '#e9efff' : '#fff7ed' }};color:{{ $isProtected ? '#2f6df6' : '#ea580c' }};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                        <x-lucide-shield style="width:16px;height:16px;" />
                                    </div>
                                    <div style="display:flex;flex-direction:column;gap:3px;">
                                        <div style="font-weight:700;font-size:13.5px;color:#0f172a;">
                                            {{ $role->name }}
                                        </div>
                                        <div>
                                            <span class="badge" style="background:#f1f5f9;color:#475569;border:1px solid #cbd5e1;font-size:11px;font-weight:600;padding:2px 8px;border-radius:6px;display:inline-flex;align-items:center;gap:4px;">
                                                <x-lucide-tag style="width:10px;height:10px;color:#64748b;" /> {{ $meta['category'] }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- Deskripsi --}}
                            <td style="vertical-align:middle;font-size:12.5px;color:#64748b;max-width:320px;line-height:1.4;padding:14px 16px;">
                                {{ $meta['description'] }}
                            </td>

                            {{-- Pengguna --}}
                            <td style="text-align:center;vertical-align:middle;padding:14px 16px;">
                                <a href="{{ route('users.index', ['role' => $role->name]) }}" class="badge badge-secondary" style="font-size:12px;font-weight:600;text-decoration:none;padding:5px 10px;border-radius:6px;display:inline-flex;align-items:center;gap:4px;" title="Lihat staf dengan peran {{ $role->name }}">
                                    <x-lucide-users style="width:12px;height:12px;" /> {{ $role->users_count }} Pengguna
                                </a>
                            </td>

                            {{-- Izin Akses --}}
                            <td style="text-align:center;vertical-align:middle;padding:14px 16px;">
                                <button type="button" class="btn btn-sm btn-light" onclick="viewPermissionsModal('{{ $role->id }}')" style="font-size:12px;font-weight:600;border:1px solid #e2e8f0;padding:4px 10px;border-radius:20px;cursor:pointer;background:#f8fafc;display:inline-flex;align-items:center;gap:5px;">
                                    <span style="width:6px;height:6px;border-radius:50%;background:{{ $role->permissions_count > 0 ? '#10b981' : '#cbd5e1' }};display:inline-block;"></span>
                                    {{ $role->permissions_count }} Izin
                                </button>
                            </td>

                            {{-- Status Tipe --}}
                            <td style="text-align:center;vertical-align:middle;padding:14px 16px;">
                                @if($isProtected)
                                    <span class="badge" style="background:#eff6ff;color:#2563eb;border:1px solid #bfdbfe;font-size:11px;font-weight:700;padding:3px 8px;border-radius:6px;display:inline-flex;align-items:center;gap:3px;">
                                        <x-lucide-lock style="width:10px;height:10px;" /> Sistem Inti
                                    </span>
                                @else
                                    <span class="badge" style="background:#ecfdf5;color:#059669;border:1px solid #a7f3d0;font-size:11px;font-weight:700;padding:3px 8px;border-radius:6px;display:inline-flex;align-items:center;gap:3px;">
                                        <x-lucide-check style="width:10px;height:10px;" /> Kustom
                                    </span>
                                @endif
                            </td>

                            {{-- Aksi --}}
                            <td style="text-align:center;vertical-align:middle;padding:14px 16px;">
                                <div style="display:flex;align-items:center;justify-content:center;gap:6px;">
                                    <button type="button" class="btn btn-sm btn-light" onclick="viewPermissionsModal('{{ $role->id }}')" style="font-size:11.5px;padding:4px 9px;border:1px solid #e2e8f0;border-radius:6px;background:#ffffff;cursor:pointer;display:inline-flex;align-items:center;gap:3px;" title="Lihat wewenang">
                                        <x-lucide-eye style="width:12px;height:12px;" /> Lihat
                                    </button>
                                    @if(!$isProtected)
                                        <button type="button" class="btn btn-sm btn-secondary btn-icon" onclick="openEditRoleModal('{{ $role->id }}', '{{ addslashes($role->name) }}')" title="Edit Hak Akses" style="padding:4px 7px;border-radius:6px;cursor:pointer;">
                                            <x-lucide-square-pen style="width:13px;height:13px;" />
                                        </button>
                                        <form method="POST" action="{{ route('roles.destroy', $role) }}" data-confirm="Apakah Anda yakin ingin menghapus jabatan kustom '{{ $role->name }}'?" data-confirm-title="Konfirmasi Hapus Jabatan" data-confirm-btn="Hapus Jabatan" data-confirm-danger="true" style="margin:0;display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger btn-icon" title="Hapus Jabatan" style="padding:4px 7px;border-radius:6px;cursor:pointer;">
                                                <x-lucide-trash-2 style="width:13px;height:13px;" />
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted" style="font-size:12.5px;padding:24px;">Tidak ada data jabatan yang cocok dengan kriteria pencarian.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ======================================================== --}}
    {{-- MODAL 1: VIEW PERMISSIONS (LIHAT MATRIKS WEWENANG)       --}}
    {{-- ======================================================== --}}
    <div class="modal-overlay" id="modalViewPermissions" onclick="if(event.target === this) closeViewPermissionsModal()">
        <div class="modal-box">
            <div class="modal-header">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-9 h-9 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center flex-shrink-0">
                        <x-lucide-key class="w-4 h-4" />
                    </div>
                    <div class="min-w-0">
                        <h3 class="modal-title truncate" id="permModalTitle">Matriks Hak Akses</h3>
                        <p class="text-[12px] text-gray-500 mt-0.5" id="permModalSubtitle">Daftar wewenang jabatan</p>
                    </div>
                </div>
                <button type="button" class="btn-close-modal" onclick="closeViewPermissionsModal()">
                    <x-lucide-x class="w-4 h-4" />
                </button>
            </div>

            {{-- Filter Bar Inside Modal --}}
            <div class="px-5 py-2.5 bg-slate-50 border-b border-slate-200 flex items-center justify-between text-xs gap-3">
                <div class="inline-flex rounded-lg bg-slate-200/80 p-0.5 text-xs font-semibold">
                    <button type="button" id="btnPermActiveOnly" onclick="togglePermView(true)" class="px-3 py-1 rounded-md bg-white text-slate-800 shadow-xs">Hanya Izin Aktif</button>
                    <button type="button" id="btnPermAll" onclick="togglePermView(false)" class="px-3 py-1 rounded-md text-slate-600 hover:text-slate-900">Semua Izin (67)</button>
                </div>
                <div class="relative w-44">
                    <input type="text" id="viewPermSearchInput" oninput="filterViewPermissionsLive(this.value)" placeholder="Filter izin..." class="w-full h-7 pl-7 pr-2 rounded-lg border border-slate-200 bg-white text-xs text-slate-800 focus:outline-none focus:border-orange-500">
                    <x-lucide-search class="w-3.5 h-3.5 absolute left-2 top-1/2 -translate-y-1/2 text-slate-400" />
                </div>
            </div>

            <div class="modal-body" id="permModalBody">
                <!-- Rendered dynamically by JS -->
            </div>

            <div class="modal-footer">
                <button type="button" class="h-10 px-5 rounded-full border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-[13px] font-semibold transition cursor-pointer" onclick="closeViewPermissionsModal()">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    {{-- ======================================================== --}}
    {{-- MODAL 2: TAMBAH JABATAN KUSTOM BARU                      --}}
    {{-- ======================================================== --}}
    <div class="modal-overlay" id="modalCreateRole" onclick="if(event.target === this) closeCreateRoleModal()">
        <div class="modal-box">
            <div class="modal-header">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-orange-50 text-[#ea580c] flex items-center justify-center flex-shrink-0">
                        <x-lucide-shield-plus class="w-4 h-4" />
                    </div>
                    <div>
                        <h3 class="modal-title">Tambah Jabatan Kustom</h3>
                        <p class="text-[12px] text-gray-500 mt-0.5">Definisikan peran baru beserta batasan wewenangnya</p>
                    </div>
                </div>
                <button type="button" class="btn-close-modal" onclick="closeCreateRoleModal()">
                    <x-lucide-x class="w-4 h-4" />
                </button>
            </div>

            <form method="POST" action="{{ route('roles.store') }}" class="flex flex-col flex-1 overflow-hidden">
                @csrf
                <div class="modal-body space-y-4">
                    {{-- Input Nama Jabatan --}}
                    <div>
                        <label class="block text-[12px] font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Nama Jabatan / Peran <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" class="w-full h-11 px-3.5 rounded-xl bg-white border border-[#e2e2e0] text-[13px] text-[#111111] placeholder-gray-400 focus:outline-none focus:border-[#ea580c] focus:ring-2 focus:ring-[#ea580c]/20 transition" placeholder="Contoh: Surveyor Proyek, Koordinator Mutu, Staf Gudang Besi" required>
                    </div>

                    {{-- Salin Dari Template --}}
                    <div class="p-3.5 rounded-xl bg-[#f8fafc] border border-slate-200">
                        <label class="block text-[12px] font-bold text-slate-700 mb-1">
                            Salin Wewenang Dari Template (Opsional)
                        </label>
                        <select id="createBaseRoleSelect" onchange="clonePermissionsFromRole(this.value, 'create')" class="w-full h-10 px-3 rounded-lg bg-white border border-slate-200 text-[12.5px] text-slate-800 focus:outline-none focus:border-[#ea580c]">
                            <option value="">-- Atur Mandiri / Kosong --</option>
                            @foreach($roles as $r)
                                <option value="{{ $r->name }}">{{ $r->name }} ({{ $r->permissions_count }} wewenang aktif)</option>
                            @endforeach
                        </select>
                        <p class="text-[11px] text-gray-500 mt-1">Pilih jabatan di atas untuk mencentang otomatis wewenang yang sejenis.</p>
                    </div>

                    {{-- Matriks Wewenang Granular --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <span class="text-[12px] font-bold uppercase tracking-wider text-slate-700">Matriks Wewenang</span>
                                <span id="createPermCount" class="text-[11px] font-bold text-[#ea580c] bg-orange-50 border border-orange-200 px-2.5 py-0.5 rounded-full">0 izin dipilih</span>
                            </div>
                            <div class="flex items-center gap-2 text-xs">
                                <button type="button" onclick="toggleAllPermissions('create', true)" class="text-[11px] font-bold text-[#ea580c] hover:underline cursor-pointer">Pilih Semua</button>
                                <span class="text-slate-300">|</span>
                                <button type="button" onclick="toggleAllPermissions('create', false)" class="text-[11px] text-slate-500 hover:underline cursor-pointer">Kosongkan</button>
                            </div>
                        </div>

                        {{-- Modul Groups --}}
                        <div class="space-y-3">
                            @foreach($groupedPermissions as $groupName => $gData)
                            <div class="perm-group-section">
                                <div class="perm-group-header">
                                    <div class="text-[12.5px] font-bold text-slate-800 flex items-center gap-2">
                                        <x-lucide-boxes class="w-4 h-4 text-slate-500" />
                                        <span>{{ $groupName }}</span>
                                    </div>
                                    <div class="flex items-center gap-2 text-[11px]">
                                        <button type="button" onclick="toggleGroupCheckboxes('create', '{{ \Illuminate\Support\Str::slug($groupName) }}', true)" class="font-bold text-[#ea580c] hover:underline cursor-pointer">Pilih Semua</button>
                                        <span class="text-slate-300">|</span>
                                        <button type="button" onclick="toggleGroupCheckboxes('create', '{{ \Illuminate\Support\Str::slug($groupName) }}', false)" class="text-slate-500 hover:underline cursor-pointer">Batal</button>
                                    </div>
                                </div>
                                <div class="perm-chips-wrap">
                                    @foreach($gData['permissions'] as $p)
                                    <label class="perm-check-chip" id="lbl-create-{{ \Illuminate\Support\Str::slug($p->name) }}">
                                        <input type="checkbox" name="permissions[]" value="{{ $p->name }}" class="create-perm-checkbox perm-group-create-{{ \Illuminate\Support\Str::slug($groupName) }}" onchange="onPermCheckboxChange(this, 'create')">
                                        <span>{{ $p->label ?? $p->name }}</span>
                                    </label>
                                    @endforeach
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="h-10 px-5 rounded-full border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-[13px] font-semibold transition cursor-pointer" onclick="closeCreateRoleModal()">
                        Batal
                    </button>
                    <button type="submit" class="h-10 px-6 rounded-full bg-[#ea580c] hover:bg-[#d9480c] text-white text-[13px] font-semibold transition shadow-sm inline-flex items-center gap-2 cursor-pointer">
                        <x-lucide-save class="w-4 h-4" />
                        <span>Simpan Jabatan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ======================================================== --}}
    {{-- MODAL 3: EDIT JABATAN KUSTOM                             --}}
    {{-- ======================================================== --}}
    <div class="modal-overlay" id="modalEditRole" onclick="if(event.target === this) closeEditRoleModal()">
        <div class="modal-box">
            <div class="modal-header">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-orange-50 text-[#ea580c] flex items-center justify-center flex-shrink-0">
                        <x-lucide-pencil class="w-4 h-4" />
                    </div>
                    <div>
                        <h3 class="modal-title flex items-center gap-1.5">
                            <span>Edit Wewenang:</span>
                            <span id="editModalRoleNameHeader" class="text-[#ea580c]"></span>
                        </h3>
                        <p class="text-[12px] text-gray-500 mt-0.5">Sesuaikan matriks izin dan otoritas peran</p>
                    </div>
                </div>
                <button type="button" class="btn-close-modal" onclick="closeEditRoleModal()">
                    <x-lucide-x class="w-4 h-4" />
                </button>
            </div>

            <form id="editRoleForm" method="POST" action="" class="flex flex-col flex-1 overflow-hidden">
                @csrf
                @method('PUT')
                <input type="hidden" name="sync_permissions" value="1">

                <div class="modal-body space-y-4">
                    {{-- Input Nama Jabatan --}}
                    <div>
                        <label class="block text-[12px] font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Nama Jabatan / Peran <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="editRoleNameInput" name="name" class="w-full h-11 px-3.5 rounded-xl bg-white border border-[#e2e2e0] text-[13px] text-[#111111] placeholder-gray-400 focus:outline-none focus:border-[#ea580c] focus:ring-2 focus:ring-[#ea580c]/20 transition" required>
                    </div>

                    {{-- Matriks Wewenang Granular --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <span class="text-[12px] font-bold uppercase tracking-wider text-slate-700">Matriks Wewenang</span>
                                <span id="editPermCount" class="text-[11px] font-bold text-[#ea580c] bg-orange-50 border border-orange-200 px-2.5 py-0.5 rounded-full">0 izin dipilih</span>
                            </div>
                            <div class="flex items-center gap-2 text-xs">
                                <button type="button" onclick="toggleAllPermissions('edit', true)" class="text-[11px] font-bold text-[#ea580c] hover:underline cursor-pointer">Pilih Semua</button>
                                <span class="text-slate-300">|</span>
                                <button type="button" onclick="toggleAllPermissions('edit', false)" class="text-[11px] text-slate-500 hover:underline cursor-pointer">Kosongkan</button>
                            </div>
                        </div>

                        {{-- Modul Groups --}}
                        <div class="space-y-3">
                            @foreach($groupedPermissions as $groupName => $gData)
                            <div class="perm-group-section">
                                <div class="perm-group-header">
                                    <div class="text-[12.5px] font-bold text-slate-800 flex items-center gap-2">
                                        <x-lucide-boxes class="w-4 h-4 text-slate-500" />
                                        <span>{{ $groupName }}</span>
                                    </div>
                                    <div class="flex items-center gap-2 text-[11px]">
                                        <button type="button" onclick="toggleGroupCheckboxes('edit', '{{ \Illuminate\Support\Str::slug($groupName) }}', true)" class="font-bold text-[#ea580c] hover:underline cursor-pointer">Pilih Semua</button>
                                        <span class="text-slate-300">|</span>
                                        <button type="button" onclick="toggleGroupCheckboxes('edit', '{{ \Illuminate\Support\Str::slug($groupName) }}', false)" class="text-slate-500 hover:underline cursor-pointer">Batal</button>
                                    </div>
                                </div>
                                <div class="perm-chips-wrap">
                                    @foreach($gData['permissions'] as $p)
                                    <label class="perm-check-chip" id="lbl-edit-{{ \Illuminate\Support\Str::slug($p->name) }}">
                                        <input type="checkbox" name="permissions[]" value="{{ $p->name }}" class="edit-perm-checkbox perm-group-edit-{{ \Illuminate\Support\Str::slug($groupName) }}" onchange="onPermCheckboxChange(this, 'edit')">
                                        <span>{{ $p->label ?? $p->name }}</span>
                                    </label>
                                    @endforeach
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="h-10 px-5 rounded-full border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-[13px] font-semibold transition cursor-pointer" onclick="closeEditRoleModal()">
                        Batal
                    </button>
                    <button type="submit" class="h-10 px-6 rounded-full bg-[#ea580c] hover:bg-[#d9480c] text-white text-[13px] font-semibold transition shadow-sm inline-flex items-center gap-2 cursor-pointer">
                        <x-lucide-save class="w-4 h-4" />
                        <span>Simpan Perubahan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    @php
        $rolesDataJson = $roles->mapWithKeys(fn($r) => [
            $r->id => [
                'id' => $r->id,
                'name' => $r->name,
                'is_protected' => in_array($r->name, $protectedRoles, true),
                'permissions' => $r->permissions->pluck('name')->values()->all(),
            ]
        ])->all();
    @endphp

    {{-- ==================== CLIENT JAVASCRIPT ==================== --}}
    <script>
        const ROLES_DATA = @json($rolesDataJson);
        const GROUPED_PERMISSIONS = @json($groupedPermissions);
        let activeFilterOnly = true;
        let currentViewingRoleId = null;
        let currentPermSearchQuery = '';

        // TAB FILTERING (Semua / Sistem Inti / Kustom)
        function filterRolesTable(type) {
            document.querySelectorAll('#rolesTableBody tr.role-table-row').forEach(row => {
                const match = (type === 'all' || row.getAttribute('data-type') === type);
                row.style.display = match ? '' : 'none';
            });
            ['all', 'protected', 'custom'].forEach(t => {
                const btn = document.getElementById(`tab-btn-${t}`);
                if (btn) {
                    if (t === type) {
                        btn.className = 'roles-tab-btn pb-2.5 -mb-px border-b-2 border-[#ea580c] text-[13px] text-[#111111] font-semibold transition inline-flex items-center gap-1.5 cursor-pointer';
                    } else {
                        btn.className = 'roles-tab-btn pb-2.5 -mb-px border-b-2 border-transparent text-[13px] text-gray-500 hover:text-[#111111] transition inline-flex items-center gap-1.5 cursor-pointer';
                    }
                }
            });
        }

        // LIVE SEARCH FILTER
        function liveSearchRoles(q) {
            const term = q.toLowerCase().trim();
            document.querySelectorAll('#rolesTableBody tr.role-table-row').forEach(row => {
                const text = row.getAttribute('data-search') || '';
                row.style.display = (!term || text.includes(term)) ? '' : 'none';
            });
        }

        // VIEW PERMISSIONS MODAL
        function viewPermissionsModal(roleId) {
            currentViewingRoleId = roleId;
            const role = ROLES_DATA[roleId];
            if (!role) return;

            document.getElementById('permModalTitle').innerText = 'Hak Akses: ' + role.name;
            document.getElementById('permModalSubtitle').innerText = `${role.permissions.length} dari 67 wewenang aktif`;

            document.getElementById('viewPermSearchInput').value = '';
            currentPermSearchQuery = '';

            renderViewPermissionsBody();
            document.getElementById('modalViewPermissions').classList.add('show');
        }

        function togglePermView(activeOnly) {
            activeFilterOnly = activeOnly;
            const btnActive = document.getElementById('btnPermActiveOnly');
            const btnAll = document.getElementById('btnPermAll');

            if (activeOnly) {
                btnActive.className = 'px-3 py-1 rounded-md bg-white text-slate-800 shadow-xs';
                btnAll.className = 'px-3 py-1 rounded-md text-slate-600 hover:text-slate-900';
            } else {
                btnAll.className = 'px-3 py-1 rounded-md bg-white text-slate-800 shadow-xs';
                btnActive.className = 'px-3 py-1 rounded-md text-slate-600 hover:text-slate-900';
            }

            renderViewPermissionsBody();
        }

        function filterViewPermissionsLive(q) {
            currentPermSearchQuery = q.toLowerCase().trim();
            renderViewPermissionsBody();
        }

        function renderViewPermissionsBody() {
            const role = ROLES_DATA[currentViewingRoleId];
            if (!role) return;

            const modalBody = document.getElementById('permModalBody');
            modalBody.innerHTML = '';
            const userPerms = new Set(role.permissions);

            for (const [groupName, groupData] of Object.entries(GROUPED_PERMISSIONS)) {
                let perms = groupData.permissions;

                if (currentPermSearchQuery) {
                    perms = perms.filter(p => {
                        const lbl = (p.label || '').toLowerCase();
                        const nm = (p.name || '').toLowerCase();
                        return lbl.includes(currentPermSearchQuery) || nm.includes(currentPermSearchQuery);
                    });
                }

                const grantedPerms = perms.filter(p => userPerms.has(p.name));
                if (activeFilterOnly && grantedPerms.length === 0) continue;
                if (perms.length === 0) continue;

                const section = document.createElement('div');
                section.className = 'perm-group-section';

                const header = document.createElement('div');
                header.className = 'perm-group-header';
                header.innerHTML = `
                    <div style="font-size:12.5px;font-weight:700;color:#0f172a;display:flex;align-items:center;gap:6px;">
                        <span style="width:6px;height:6px;border-radius:50%;background:${grantedPerms.length > 0 ? '#10b981' : '#cbd5e1'};display:inline-block;"></span>
                        ${groupName}
                    </div>
                    <span style="font-size:11px;font-weight:600;color:${grantedPerms.length > 0 ? '#047857' : '#94a3b8'};">
                        ${grantedPerms.length} / ${groupData.permissions.length} Aktif
                    </span>
                `;
                section.appendChild(header);

                const pillsWrap = document.createElement('div');
                pillsWrap.className = 'perm-chips-wrap';

                perms.forEach(p => {
                    const isGranted = userPerms.has(p.name);
                    if (activeFilterOnly && !isGranted) return;

                    const pill = document.createElement('span');
                    const labelText = p.label || p.name;
                    if (isGranted) {
                        pill.className = 'perm-view-pill granted';
                        pill.innerHTML = `<span>✓</span> <span>${labelText}</span>`;
                    } else {
                        pill.className = 'perm-view-pill denied';
                        pill.innerHTML = `<span>✕</span> <span>${labelText}</span>`;
                    }
                    pillsWrap.appendChild(pill);
                });

                section.appendChild(pillsWrap);
                modalBody.appendChild(section);
            }

            if (modalBody.children.length === 0) {
                modalBody.innerHTML = `<div style="text-align:center;padding:32px 16px;color:#94a3b8;font-size:12.5px;">Tidak ada wewenang yang sesuai dengan filter.</div>`;
            }
        }

        function closeViewPermissionsModal() {
            document.getElementById('modalViewPermissions').classList.remove('show');
        }

        // CREATE ROLE MODAL
        function openCreateRoleModal() {
            document.querySelectorAll('.create-perm-checkbox').forEach(cb => {
                cb.checked = false;
                onPermCheckboxChange(cb, 'create');
            });
            document.getElementById('createBaseRoleSelect').value = '';
            updateSelectedCount('create');
            document.getElementById('modalCreateRole').classList.add('show');
        }

        function closeCreateRoleModal() {
            document.getElementById('modalCreateRole').classList.remove('show');
        }

        // EDIT ROLE MODAL
        function openEditRoleModal(roleId, roleName) {
            const role = ROLES_DATA[roleId];
            if (!role) return;

            document.getElementById('editModalRoleNameHeader').innerText = role.name;
            document.getElementById('editRoleNameInput').value = role.name;
            document.getElementById('editRoleForm').action = `/roles/${roleId}`;

            const userPerms = new Set(role.permissions);
            document.querySelectorAll('.edit-perm-checkbox').forEach(cb => {
                cb.checked = userPerms.has(cb.value);
                onPermCheckboxChange(cb, 'edit');
            });

            updateSelectedCount('edit');
            document.getElementById('modalEditRole').classList.add('show');
        }

        function closeEditRoleModal() {
            document.getElementById('modalEditRole').classList.remove('show');
        }

        // HELPER CHIP SELECTION
        function onPermCheckboxChange(cb, scope) {
            const label = cb.closest('.perm-check-chip');
            if (!label) return;
            if (cb.checked) {
                label.classList.add('is-active');
            } else {
                label.classList.remove('is-active');
            }
            updateSelectedCount(scope);
        }

        function toggleGroupCheckboxes(scope, groupSlug, check) {
            document.querySelectorAll(`.perm-group-${scope}-${groupSlug}`).forEach(cb => {
                cb.checked = check;
                onPermCheckboxChange(cb, scope);
            });
            updateSelectedCount(scope);
        }

        function toggleAllPermissions(scope, check) {
            document.querySelectorAll(`.${scope}-perm-checkbox`).forEach(cb => {
                cb.checked = check;
                onPermCheckboxChange(cb, scope);
            });
            updateSelectedCount(scope);
        }

        function updateSelectedCount(scope) {
            const count = document.querySelectorAll(`.${scope}-perm-checkbox:checked`).length;
            const counter = document.getElementById(`${scope}PermCount`);
            if (counter) counter.innerText = `${count} izin dipilih`;
        }

        function clonePermissionsFromRole(roleName, scope) {
            if (!roleName) {
                document.querySelectorAll(`.${scope}-perm-checkbox`).forEach(cb => {
                    cb.checked = false;
                    onPermCheckboxChange(cb, scope);
                });
                updateSelectedCount(scope);
                return;
            }
            const found = Object.values(ROLES_DATA).find(r => r.name === roleName);
            if (!found) return;

            const perms = new Set(found.permissions);
            document.querySelectorAll(`.${scope}-perm-checkbox`).forEach(cb => {
                cb.checked = perms.has(cb.value);
                onPermCheckboxChange(cb, scope);
            });
            updateSelectedCount(scope);
        }

        // KEYBOARD ESC CLOSE
        window.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeViewPermissionsModal();
                closeCreateRoleModal();
                closeEditRoleModal();
            }
        });
    </script>
</x-app-layout>