<x-app-layout>
    <x-slot name="title">Manajemen Jabatan & Peran (Role)</x-slot>

    <div class="flex items-center justify-between mb-4">
        <div>
            <h2 class="fw-700" style="font-size:20px;color:#0f172a;">Manajemen Jabatan & Hak Akses (RBAC)</h2>
            <p class="text-muted" style="font-size:13px;margin-top:2px;">
                Struktur peran kerja kanonikal, pembatasan wewenang, dan matriks hak akses pengguna PT Arsikon
            </p>
        </div>
        <div>
            <button type="button" class="btn btn-primary" onclick="openCreateRoleModal()">
                <i class="fas fa-plus"></i> Tambah Jabatan Kustom
            </button>
        </div>
    </div>

    {{-- Alert Messages --}}
    @if(session('success'))
        <div class="alert alert-success mb-3" style="display:flex;align-items:center;gap:10px;padding:12px 16px;border-radius:10px;background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0;">
            <i class="fas fa-circle-check" style="font-size:16px;"></i>
            <span style="font-size:13px;font-weight:600;">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger mb-3" style="display:flex;align-items:center;gap:10px;padding:12px 16px;border-radius:10px;background:#fef2f2;color:#991b1b;border:1px solid #fecaca;">
            <i class="fas fa-triangle-exclamation" style="font-size:16px;"></i>
            <span style="font-size:13px;font-weight:600;">{{ session('error') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger mb-3" style="padding:12px 16px;border-radius:10px;background:#fef2f2;color:#991b1b;border:1px solid #fecaca;">
            <div style="font-weight:700;font-size:13px;margin-bottom:4px;">Gagal menyimpan jabatan:</div>
            <ul style="margin:0;padding-left:20px;font-size:12.5px;">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Info Card Arsitektur Role --}}
    <div class="card mb-4" style="background:linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);border:1px solid #e2e8f0;border-left:4px solid #0284c7;">
        <div class="card-body" style="padding:16px 20px;display:flex;align-items:flex-start;gap:14px;">
            <div style="width:40px;height:40px;border-radius:10px;background:#0284c7;color:#fff;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="fas fa-shield-halved" style="font-size:18px;"></i>
            </div>
            <div style="font-size:12.5px;color:#1e293b;line-height:1.5;">
                <div style="font-weight:700;font-size:13.5px;color:#0f172a;margin-bottom:2px;">Arsitektur Hak Akses Terpadu (Single Source of Truth)</div>
                Sistem menggunakan 6 Jabatan Sistem Inti kanonikal untuk mencegah duplikasi peran dan celah *unauthorized access*. Role sistem inti dilindungi (*locked*) dari penghapusan demi menjaga integritas alur operasional gudang, pengadaan, dan approval proyek.
            </div>
        </div>
    </div>

    {{-- Filter & Search Bar --}}
    <div class="card mb-3">
        <div class="card-body" style="padding:14px 18px;">
            <form method="GET" action="{{ route('roles.index') }}" style="display:flex;gap:12px;align-items:center;">
                <div style="position:relative;flex:1;">
                    <i class="fas fa-magnifying-glass" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:13px;"></i>
                    <input type="text" name="search" class="form-control" placeholder="Cari nama jabatan..." value="{{ request('search') }}" style="padding-left:36px;font-size:13px;">
                </div>
                <button type="submit" class="btn btn-secondary" style="font-size:13px;">
                    <i class="fas fa-filter"></i> Filter
                </button>
                @if(request('search'))
                    <a href="{{ route('roles.index') }}" class="btn btn-light" style="font-size:13px;">Reset</a>
                @endif
            </form>
        </div>
    </div>

    {{-- Roles Table --}}
    <div class="card">
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width:50px;text-align:center;">#</th>
                        <th>Jabatan & Kategori Operasional</th>
                        <th>Deskripsi Wewenang</th>
                        <th style="width:150px;text-align:center;">Pengguna Aktif</th>
                        <th style="width:160px;text-align:center;">Izin Akses</th>
                        <th style="width:140px;text-align:center;">Tipe Role</th>
                        <th style="width:110px;text-align:center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($roles as $i => $role)
                    @php
                        $isProtected = in_array($role->name, $protectedRoles, true);
                        $meta = $roleMetadata[$role->name] ?? [
                            'category'    => 'Jabatan Kustom',
                            'badge_color' => '#0f766e',
                            'badge_bg'    => '#ccfbf1',
                            'icon'        => 'fa-user-tag',
                            'description' => 'Jabatan operasional tambahan yang didefinisikan secara khusus.',
                        ];
                    @endphp
                    <tr>
                        <td class="text-muted" style="text-align:center;vertical-align:middle;font-weight:600;">{{ $i + 1 }}</td>
                        <td style="vertical-align:middle;">
                            <div style="display:flex;flex-direction:column;gap:4px;">
                                <div style="display:flex;align-items:center;gap:8px;">
                                    <span class="fw-700" style="font-size:14px;color:#0f172a;">
                                        {{ $role->name }}
                                    </span>
                                </div>
                                <div>
                                    <span class="badge" style="background:{{ $meta['badge_bg'] }};color:{{ $meta['badge_color'] }};border:1px solid {{ $meta['badge_color'] }}33;font-size:10.5px;font-weight:600;padding:2px 8px;border-radius:6px;">
                                        <i class="fas {{ $meta['icon'] }}" style="margin-right:4px;"></i> {{ $meta['category'] }}
                                    </span>
                                </div>
                            </div>
                        </td>
                        <td style="vertical-align:middle;font-size:12.5px;color:#64748b;max-width:320px;line-height:1.4;">
                            {{ $meta['description'] }}
                        </td>
                        <td style="text-align:center;vertical-align:middle;">
                            <a href="{{ route('users.index', ['role' => $role->name]) }}" class="badge badge-secondary" style="font-size:12px;font-weight:600;text-decoration:none;padding:5px 10px;" title="Lihat pengguna dengan jabatan {{ $role->name }}">
                                <i class="fas fa-users" style="font-size:10.5px;margin-right:4px;"></i> {{ $role->users_count }} Pengguna
                            </a>
                        </td>
                        <td style="text-align:center;vertical-align:middle;">
                            <button type="button" class="btn btn-sm btn-light" onclick="viewPermissionsModal('{{ $role->id }}')" style="font-size:11.5px;font-weight:600;border:1px solid #cbd5e1;padding:4px 10px;border-radius:6px;cursor:pointer;">
                                <i class="fas fa-key" style="color:#0284c7;margin-right:4px;"></i> {{ $role->permissions_count }} Izin Akses
                            </button>
                        </td>
                        <td style="text-align:center;vertical-align:middle;">
                            @if($isProtected)
                                <span class="badge badge-success" style="font-size:11px;padding:4px 9px;">
                                    <i class="fas fa-lock" style="font-size:9.5px;margin-right:3px;"></i> Sistem Inti
                                </span>
                            @else
                                <span class="badge badge-info" style="font-size:11px;padding:4px 9px;">
                                    <i class="fas fa-puzzle-piece" style="font-size:9.5px;margin-right:3px;"></i> Kustom
                                </span>
                            @endif
                        </td>
                        <td style="text-align:center;vertical-align:middle;">
                            <div style="display:flex;align-items:center;justify-content:center;gap:6px;">
                                @if(!$isProtected)
                                    <button type="button" class="btn btn-sm btn-secondary btn-icon" onclick="openEditRoleModal('{{ $role->id }}', '{{ addslashes($role->name) }}')" title="Edit Hak Akses">
                                        <i class="fas fa-pen-to-square"></i>
                                    </button>
                                    <form method="POST" action="{{ route('roles.destroy', $role) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus jabatan kustom {{ addslashes($role->name) }}?');" style="margin:0;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger btn-icon" title="Hapus Jabatan">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @else
                                    <span class="text-muted" style="font-size:11px;font-style:italic;background:#f8fafc;padding:3px 8px;border-radius:4px;border:1px solid #e2e8f0;" title="Jabatan sistem inti dilindungi dari penghapusan">
                                        <i class="fas fa-shield-halved" style="color:#94a3b8;margin-right:2px;"></i> Terkunci
                                    </span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">Belum ada data jabatan yang cocok dengan kriteria pencarian.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal Tambah Jabatan Baru --}}
    <div id="modalCreateRole" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.6);z-index:9999;align-items:center;justify-content:center;padding:16px;">
        <div style="background:#fff;border-radius:14px;width:100%;max-width:540px;box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);overflow:hidden;max-height:90vh;display:flex;flex-direction:column;">
            <div style="padding:16px 22px;border-bottom:1px solid #e2e8f0;display:flex;align-items:center;justify-content:space-between;background:#f8fafc;">
                <h3 style="font-size:16px;font-weight:700;color:#0f172a;margin:0;display:flex;align-items:center;gap:8px;">
                    <i class="fas fa-id-badge text-primary"></i> Tambah Jabatan Kustom Baru
                </h3>
                <button type="button" onclick="closeCreateRoleModal()" style="border:none;background:none;font-size:20px;color:#94a3b8;cursor:pointer;">&times;</button>
            </div>
            <form method="POST" action="{{ route('roles.store') }}" style="display:flex;flex-direction:column;overflow:hidden;flex:1;">
                @csrf
                <div style="padding:22px;overflow-y:auto;flex:1;">
                    <div class="mb-3">
                        <label class="form-label" style="font-weight:600;font-size:13px;">Nama Jabatan / Peran <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="Contoh: Pengawas Mutu, Koordinator Lapangan, Staff Keuangan" required style="font-size:13px;">
                        <div style="font-size:11.5px;color:#64748b;margin-top:4px;">Gunakan nama yang jelas dan unik. Nama tidak boleh duplikat dengan role sistem inti.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" style="font-weight:600;font-size:13px;">Salin Template Hak Akses Dari</label>
                        <select name="base_role" class="form-control" style="font-size:13px;">
                            <option value="">-- Buat Kosong (Atur Izin Akses Secara Manual) --</option>
                            @foreach($roles as $r)
                            <option value="{{ $r->name }}">{{ $r->name }} ({{ $r->permissions_count }} izin akses)</option>
                            @endforeach
                        </select>
                        <div style="font-size:11.5px;color:#64748b;margin-top:4px;">Mempercepat konfigurasi dengan menduplikasi seluruh matriks hak akses dari jabatan yang ada.</div>
                    </div>
                </div>

                <div style="display:flex;justify-content:flex-end;gap:10px;border-top:1px solid #e2e8f0;padding:14px 22px;background:#f8fafc;">
                    <button type="button" onclick="closeCreateRoleModal()" class="btn btn-secondary" style="font-size:13px;">Batal</button>
                    <button type="submit" class="btn btn-primary" style="font-size:13px;"><i class="fas fa-save"></i> Simpan Jabatan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Lihat Izin Akses --}}
    <div id="modalViewPermissions" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.6);z-index:9999;align-items:center;justify-content:center;padding:16px;">
        <div style="background:#fff;border-radius:14px;width:100%;max-width:680px;box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);overflow:hidden;max-height:85vh;display:flex;flex-direction:column;">
            <div style="padding:16px 22px;border-bottom:1px solid #e2e8f0;display:flex;align-items:center;justify-content:space-between;background:#f8fafc;">
                <div>
                    <h3 style="font-size:16px;font-weight:700;color:#0f172a;margin:0;" id="permModalTitle">
                        Detail Hak Akses
                    </h3>
                    <p style="font-size:12px;color:#64748b;margin:2px 0 0;" id="permModalSubtitle">Daftar wewenang operasional yang diberikan pada jabatan ini</p>
                </div>
                <button type="button" onclick="closeViewPermissionsModal()" style="border:none;background:none;font-size:20px;color:#94a3b8;cursor:pointer;">&times;</button>
            </div>
            <div style="padding:20px 22px;overflow-y:auto;flex:1;" id="permModalBody">
                <!-- Konten dinamis dirender via JavaScript -->
            </div>
            <div style="display:flex;justify-content:flex-end;border-top:1px solid #e2e8f0;padding:12px 22px;background:#f8fafc;">
                <button type="button" onclick="closeViewPermissionsModal()" class="btn btn-secondary" style="font-size:13px;">Tutup</button>
            </div>
        </div>
    </div>

    {{-- Data script JSON untuk modal interaktif --}}
    <script>
        const ROLES_DATA = @json($roles->mapWithKeys(fn($r) => [
            $r->id => [
                'id' => $r->id,
                'name' => $r->name,
                'permissions' => $r->permissions->pluck('name')->toArray(),
            ]
        ]));

        const GROUPED_PERMISSIONS = @json($groupedPermissions);

        function openCreateRoleModal() {
            document.getElementById('modalCreateRole').style.display = 'flex';
        }
        function closeCreateRoleModal() {
            document.getElementById('modalCreateRole').style.display = 'none';
        }

        function viewPermissionsModal(roleId) {
            const role = ROLES_DATA[roleId];
            if (!role) return;

            document.getElementById('permModalTitle').innerText = 'Hak Akses: ' + role.name;
            document.getElementById('permModalSubtitle').innerText = role.permissions.length + ' hak akses terdaftar untuk jabatan ini';

            const modalBody = document.getElementById('permModalBody');
            modalBody.innerHTML = '';

            const userPerms = new Set(role.permissions);

            for (const [groupName, groupData] of Object.entries(GROUPED_PERMISSIONS)) {
                const matchingPerms = groupData.permissions.filter(p => userPerms.has(p.name));

                const section = document.createElement('div');
                section.style.marginBottom = '18px';

                const header = document.createElement('div');
                header.style.display = 'flex';
                header.style.alignItems = 'center';
                header.style.justifyContent = 'space-between';
                header.style.marginBottom = '8px';
                header.style.paddingBottom = '4px';
                header.style.borderBottom = '1px solid #f1f5f9';

                header.innerHTML = `
                    <div style="font-size:13px;font-weight:700;color:#0f172a;display:flex;align-items:center;gap:6px;">
                        <i class="fas ${groupData.icon}" style="color:#0284c7;"></i> ${groupName}
                    </div>
                    <span style="font-size:11px;font-weight:600;color:${matchingPerms.length > 0 ? '#0284c7' : '#94a3b8'};">
                        ${matchingPerms.length} / ${groupData.permissions.length} Aktif
                    </span>
                `;
                section.appendChild(header);

                const pillsContainer = document.createElement('div');
                pillsContainer.style.display = 'flex';
                pillsContainer.style.flexWrap = 'wrap';
                pillsContainer.style.gap = '6px';

                groupData.permissions.forEach(p => {
                    const isGranted = userPerms.has(p.name);
                    const pill = document.createElement('span');
                    pill.style.fontSize = '11.5px';
                    pill.style.padding = '3px 9px';
                    pill.style.borderRadius = '6px';
                    pill.style.display = 'inline-flex';
                    pill.style.alignItems = 'center';
                    pill.style.gap = '4px';

                    if (isGranted) {
                        pill.style.background = '#e0f2fe';
                        pill.style.color = '#0369a1';
                        pill.style.border = '1px solid #bae6fd';
                        pill.style.fontWeight = '600';
                        pill.innerHTML = `<i class="fas fa-check" style="font-size:9px;"></i> ${p.name}`;
                    } else {
                        pill.style.background = '#f8fafc';
                        pill.style.color = '#94a3b8';
                        pill.style.border = '1px solid #e2e8f0';
                        pill.innerHTML = `<i class="fas fa-xmark" style="font-size:9px;"></i> ${p.name}`;
                    }
                    pillsContainer.appendChild(pill);
                });

                section.appendChild(pillsContainer);
                modalBody.appendChild(section);
            }

            document.getElementById('modalViewPermissions').style.display = 'flex';
        }

        function closeViewPermissionsModal() {
            document.getElementById('modalViewPermissions').style.display = 'none';
        }
    </script>
</x-app-layout>
