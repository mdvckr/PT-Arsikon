<x-app-layout>
    <x-slot name="title">Manajemen User & Hak Akses</x-slot>

    <div class="flex items-center justify-between mb-4">
        <div>
            <h2 class="fw-700" style="font-size:20px;color:#0f172a;">Data Pengguna</h2>
            <p class="text-muted" style="font-size:13px;margin-top:2px;">Kelola akun pengguna, peran (role), dan penugasan gudang aktif</p>
        </div>
        @can('manage users')
        <a href="{{ route('users.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah User
        </a>
        @endcan
    </div>

    <div class="card mb-4">
        <div class="card-body" style="padding:16px 20px;">
            <form method="GET" class="flex gap-3" style="flex-wrap:wrap;align-items:flex-end;">
                <div style="flex:1;min-width:200px;">
                    <label class="form-label">Cari User</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Nama atau email...">
                </div>
                <div style="min-width:160px;">
                    <label class="form-label">Role</label>
                    <select name="role" class="form-control">
                        <option value="">Semua Role</option>
                        @foreach($roles as $role)
                        <option value="{{ $role->name }}" {{ request('role') == $role->name ? 'selected' : '' }}>{{ ucfirst($role->name) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Cari</button>
                    <a href="{{ route('users.index') }}" class="btn btn-secondary"><i class="fas fa-rotate-left"></i></a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama User</th>
                        <th>Role / Peran</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $i => $user)
                    <tr>
                        <td class="text-muted">{{ $users->firstItem() + $i }}</td>
                        <td>
                            <div class="fw-600">{{ $user->name }}</div>
                            <div class="text-muted" style="font-size:11px;">{{ $user->email }}</div>
                        </td>
                        <td>
                            @foreach($user->roles as $role)
                                <span class="badge badge-primary">{{ ucfirst($role->name) }}</span>
                            @endforeach
                        </td>
                        <td>
                            @if($user->is_active)
                                <span class="badge badge-success">Aktif</span>
                            @else
                                <span class="badge badge-danger">Nonaktif</span>
                            @endif
                        </td>
                        <td>
                            <div class="flex gap-1">
                                <a href="{{ route('users.show', $user) }}" class="btn btn-sm btn-secondary btn-icon" title="Detail"><i class="fas fa-eye"></i></a>
                                @can('manage users')
                                <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-warning btn-icon" title="Edit"><i class="fas fa-pen"></i></a>
                                <form method="POST" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm('Hapus user {{ addslashes($user->name) }}?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-danger btn-icon" title="Hapus"><i class="fas fa-trash"></i></button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-muted p-4">Belum ada data user</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
        <div style="padding:12px;border-top:1px solid #f1f5f9;">{{ $users->links() }}</div>
        @endif
    </div>
</x-app-layout>
