<x-app-layout>
    <x-slot name="title">Manajemen Jabatan & Peran (Role)</x-slot>

    <div class="flex items-center justify-between mb-4">
        <div>
            <h2 class="fw-700" style="font-size:20px;color:#0f172a;">Data Jabatan & Peran (Role)</h2>
            <p class="text-muted" style="font-size:13px;margin-top:2px;">Kelola jabatan kerja dan hak akses sistem untuk penugasan akun pengguna</p>
        </div>
        <div>
            <button type="button" class="btn btn-primary" onclick="openCreateRoleModal()">
                <i class="fas fa-plus"></i> Tambah Jabatan Baru
            </button>
        </div>
    </div>

    {{-- Info Card --}}
    <div class="card mb-4" style="background:linear-gradient(135deg, #f0fdf4 0%, #e0f2fe 100%);border:1px solid #bae6fd;">
        <div class="card-body" style="padding:14px 18px;display:flex;align-items:center;gap:12px;">
            <div style="width:36px;height:36px;border-radius:8px;background:#0284c7;color:#fff;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="fas fa-shield-halved" style="font-size:16px;"></i>
            </div>
            <div style="font-size:12.5px;color:#0f172a;line-height:1.45;">
                <strong>Admin Pusat</strong> memiliki wewenang penuh untuk membuat jabatan/role baru, membuat akun pengguna baru, serta mengatur penugasan gudang. Setiap akun dapat memiliki satu atau lebih jabatan sesuai peran operasionalnya.
            </div>
        </div>
    </div>

    {{-- Roles Table --}}
    <div class="card">
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width:50px;">#</th>
                        <th>Nama Jabatan / Peran</th>
                        <th style="width:160px;text-align:center;">Jumlah Pengguna</th>
                        <th style="width:160px;text-align:center;">Jumlah Permission</th>
                        <th style="width:140px;text-align:center;">Tipe Role</th>
                        <th style="width:100px;text-align:center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($roles as $i => $role)
                    @php
                        $isProtected = in_array($role->name, $protectedRoles);
                    @endphp
                    <tr>
                        <td class="text-muted" style="vertical-align:middle;">{{ $i + 1 }}</td>
                        <td style="vertical-align:middle;">
                            <div class="flex items-center gap-2">
                                <span class="fw-700" style="font-size:13.5px;color:#1e293b;">
                                    {{ $role->name }}
                                </span>
                                @if($role->name === 'Owner')
                                    <span class="badge" style="background:#fef3c7;color:#92400e;font-size:10px;"><i class="fas fa-crown"></i> Super Admin</span>
                                @elseif($role->name === 'Admin Pusat')
                                    <span class="badge" style="background:#dbeafe;color:#1e40af;font-size:10px;"><i class="fas fa-user-shield"></i> Full Akses</span>
                                @elseif($role->name === 'Admin Gudang Pusat')
                                    <span class="badge" style="background:#f1f5f9;color:#334155;font-size:10px;"><i class="fas fa-building"></i> Logistik Sentral</span>
                                @elseif($role->name === 'Admin Gudang Proyek')
                                    <span class="badge" style="background:#f1f5f9;color:#334155;font-size:10px;"><i class="fas fa-helmet-safety"></i> Site Proyek</span>
                                @endif
                            </div>
                        </td>
                        <td style="text-align:center;vertical-align:middle;">
                            <a href="{{ route('users.index', ['role' => $role->name]) }}" class="badge badge-secondary" style="font-size:12px;font-weight:600;text-decoration:none;" title="Lihat pengguna dengan jabatan ini">
                                <i class="fas fa-users" style="font-size:10px;"></i> {{ $role->users_count }} Pengguna
                            </a>
                        </td>
                        <td style="text-align:center;vertical-align:middle;">
                            <span class="badge" style="background:#f8fafc;border:1px solid #e2e8f0;color:#475569;font-size:11.5px;font-weight:600;">
                                {{ $role->permissions_count }} Izin Akses
                            </span>
                        </td>
                        <td style="text-align:center;vertical-align:middle;">
                            @if($isProtected)
                                <span class="badge badge-success" style="font-size:10.5px;">Sistem Inti</span>
                            @else
                                <span class="badge badge-info" style="font-size:10.5px;">Kustom</span>
                            @endif
                        </td>
                        <td style="text-align:center;vertical-align:middle;">
                            @if(!$isProtected)
                                <form method="POST" action="{{ route('roles.destroy', $role) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus jabatan {{ addslashes($role->name) }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger btn-icon" title="Hapus Jabatan">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            @else
                                <span class="text-muted" style="font-size:11px;font-style:italic;">Terkunci</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">Belum ada data jabatan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal Tambah Jabatan Baru --}}
    <div id="modalCreateRole" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.6);z-index:9999;align-items:center;justify-content:center;padding:16px;">
        <div style="background:#fff;border-radius:12px;width:100%;max-width:480px;box-shadow:0 20px 25px -5px rgba(0,0,0,0.2);overflow:hidden;">
            <div style="padding:16px 20px;border-bottom:1px solid #e2e8f0;display:flex;align-items:center;justify-content:between;">
                <h3 style="font-size:16px;font-weight:700;color:#0f172a;margin:0;">
                    <i class="fas fa-id-badge text-primary" style="margin-right:6px;"></i> Tambah Jabatan Baru
                </h3>
                <button type="button" onclick="closeCreateRoleModal()" style="border:none;background:none;font-size:18px;color:#94a3b8;cursor:pointer;">&times;</button>
            </div>
            <form method="POST" action="{{ route('roles.store') }}" style="padding:20px;">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Nama Jabatan / Peran <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" placeholder="Contoh: Pengawas Proyek, Staff QC, Logistik Lapangan" required>
                    <div style="font-size:11.5px;color:#64748b;margin-top:4px;">Nama jabatan yang akan muncul di formulir pembuatan akun pengguna.</div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Salin Template Hak Akses Dari</label>
                    <select name="base_role" class="form-control">
                        <option value="">-- Buat Baru (Tanpa Izin Akses Awal) --</option>
                        @foreach($roles as $r)
                        <option value="{{ $r->name }}">{{ $r->name }} ({{ $r->permissions_count }} izin akses)</option>
                        @endforeach
                    </select>
                    <div style="font-size:11.5px;color:#64748b;margin-top:4px;">Hak akses akan langsung disesuaikan mengikuti template jabatan yang dipilih.</div>
                </div>

                <div style="display:flex;justify-content:flex-end;gap:8px;border-top:1px solid #e2e8f0;padding-top:16px;">
                    <button type="button" onclick="closeCreateRoleModal()" class="btn btn-secondary">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Jabatan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openCreateRoleModal() {
            document.getElementById('modalCreateRole').style.display = 'flex';
        }
        function closeCreateRoleModal() {
            document.getElementById('modalCreateRole').style.display = 'none';
        }
    </script>
</x-app-layout>
