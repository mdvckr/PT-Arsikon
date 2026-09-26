<x-app-layout>
    <x-slot name="title">Manajemen Pengguna & Hak Akses</x-slot>

    <div class="flex items-center justify-between mb-4">
        <div>
            <h2 class="fw-700" style="font-size:20px;color:#0f172a;">Data Pengguna</h2>
            <p class="text-muted" style="font-size:13px;margin-top:2px;">Kelola akun pengguna, peran / jabatan, dan penugasan gudang aktif</p>
        </div>
        @if(auth()->user()->can('create users') || auth()->user()->hasAnyRole(['Owner', 'Admin Pusat', 'Admin']))
        <div class="flex items-center gap-2">
            <a href="{{ route('roles.index') }}" class="btn btn-secondary" style="font-weight:600;display:inline-flex;align-items:center;gap:6px;">
                <i class="fas fa-id-badge"></i> Kelola Jabatan
            </a>
            <a href="{{ route('users.create') }}" class="btn btn-primary" style="display:inline-flex;align-items:center;gap:6px;">
                <i class="fas fa-plus"></i> Tambah Pengguna
            </a>
        </div>
        @endif
    </div>

    {{-- Filter Card --}}
    <div class="card mb-4">
        <div class="card-body" style="padding:16px 20px;">
            <form method="GET" class="flex gap-3" style="flex-wrap:wrap;align-items:flex-end;">
                <div style="flex:1;min-width:220px;">
                    <label class="form-label" style="font-size:12px;font-weight:600;color:#475569;">Cari Pengguna</label>
                    <div style="position:relative;">
                        <i class="fas fa-search" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:13px;"></i>
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control" style="padding-left:34px;" placeholder="Nama atau email pengguna...">
                    </div>
                </div>
                <div style="min-width:180px;">
                    <label class="form-label" style="font-size:12px;font-weight:600;color:#475569;">Filter Jabatan / Role</label>
                    <select name="role" class="form-control" onchange="this.form.submit()">
                        <option value="">Semua Jabatan</option>
                        @foreach($roles as $role)
                        <option value="{{ $role->name }}" {{ request('role') == $role->name ? 'selected' : '' }}>{{ ucfirst($role->name) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="btn btn-secondary" style="height:38px;">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    @if(request('search') || request('role'))
                    <a href="{{ route('users.index') }}" class="btn btn-secondary" style="height:38px;" title="Reset Filter">
                        <i class="fas fa-rotate-left"></i>
                    </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Users Table --}}
    <div class="card">
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width:45px;text-align:center;">#</th>
                        <th>Nama & Kontak Pengguna</th>
                        <th>Role / Jabatan</th>
                        <th>Gudang Akses</th>
                        <th style="width:110px;text-align:center;">Status</th>
                        <th style="width:90px;text-align:center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $i => $user)
                    <tr>
                        <td class="text-muted" style="text-align:center;vertical-align:middle;font-size:12px;">
                            {{ $users->firstItem() + $i }}
                        </td>
                        <td style="vertical-align:middle;">
                            <div class="flex items-center gap-3">
                                <div style="width:36px;height:36px;border-radius:50%;background:#f1f5f9;border:1px solid #cbd5e1;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:13px;color:#334155;flex-shrink:0;">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="fw-700" style="color:#0f172a;font-size:13px;line-height:1.35;">{{ $user->name }}</div>
                                    <div class="text-muted" style="font-size:11.5px;margin-top:1px;">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td style="vertical-align:middle;">
                            <div class="flex gap-1" style="flex-wrap:wrap;">
                                @foreach($user->roles as $role)
                                    @if($role->name === 'Owner')
                                        <span class="badge" style="background:#fef3c7;color:#92400e;font-size:11px;font-weight:600;"><i class="fas fa-crown"></i> Owner</span>
                                    @elseif($role->name === 'Admin Pusat')
                                        <span class="badge" style="background:#dbeafe;color:#1e40af;font-size:11px;font-weight:600;"><i class="fas fa-user-shield"></i> Admin Pusat</span>
                                    @elseif($role->name === 'Admin Gudang Pusat')
                                        <span class="badge" style="background:#f1f5f9;color:#334155;border:1px solid #cbd5e1;font-size:11px;font-weight:600;"><i class="fas fa-building"></i> Admin Gudang Pusat</span>
                                    @elseif($role->name === 'Admin Gudang Proyek')
                                        <span class="badge" style="background:#f1f5f9;color:#334155;border:1px solid #cbd5e1;font-size:11px;font-weight:600;"><i class="fas fa-helmet-safety"></i> Admin Gudang Proyek</span>
                                    @elseif($role->name === 'Admin PO')
                                        <span class="badge" style="background:#ecfdf5;color:#065f46;font-size:11px;font-weight:600;"><i class="fas fa-truck-ramp-box"></i> Admin PO</span>
                                    @else
                                        <span class="badge badge-secondary" style="font-size:11px;font-weight:600;">{{ ucfirst($role->name) }}</span>
                                    @endif
                                @endforeach
                            </div>
                        </td>
                        <td style="vertical-align:middle;">
                            @if($user->hasAnyRole(['Owner', 'Admin Pusat']))
                                <span class="badge" style="background:#f8fafc;border:1px solid #e2e8f0;color:#475569;font-size:11px;">
                                    <i class="fas fa-globe text-primary"></i> Multi-Gudang (Penuh)
                                </span>
                            @elseif($user->warehouses->count() > 0)
                                <div class="flex gap-1" style="flex-wrap:wrap;">
                                    @foreach($user->warehouses as $wh)
                                        <span class="badge badge-gray" style="font-size:11px;">
                                            <i class="{{ $wh->is_central ? 'fas fa-building' : 'fas fa-location-dot' }}" style="font-size:9px;"></i>
                                            {{ $wh->name }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-muted" style="font-size:11.5px;font-style:italic;">Belum Ditugaskan</span>
                            @endif
                        </td>
                        <td style="text-align:center;vertical-align:middle;">
                            @if($user->is_active)
                                <span class="badge badge-success" style="font-size:11px;">
                                    <i class="fas fa-check-circle" style="font-size:9.5px;"></i> Aktif
                                </span>
                            @else
                                <span class="badge badge-danger" style="font-size:11px;">
                                    <i class="fas fa-times-circle" style="font-size:9.5px;"></i> Nonaktif
                                </span>
                            @endif
                        </td>
                        <td style="text-align:center;vertical-align:middle;">
                            <div class="flex gap-1" style="justify-content:center;">
                                @if(auth()->user()->can('edit users') || auth()->user()->hasAnyRole(['Owner', 'Admin Pusat', 'Admin']))
                                <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-warning btn-icon" title="Edit Pengguna">
                                    <i class="fas fa-pen"></i>
                                </a>
                                @endif
                                @if((auth()->user()->can('delete users') || auth()->user()->hasAnyRole(['Owner', 'Admin Pusat', 'Admin'])) && $user->id !== auth()->id())
                                <form method="POST" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun pengguna {{ addslashes($user->name) }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger btn-icon" title="Hapus Pengguna">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @elseif($user->id === auth()->id())
                                <span class="text-muted" style="font-size:10.5px;font-style:italic;align-self:center;" title="Akun yang sedang login">Saya</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted p-4">Belum ada data pengguna yang sesuai pencarian.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
        <div style="padding:12px;border-top:1px solid #f1f5f9;">{{ $users->links() }}</div>
        @endif
    </div>
</x-app-layout>
