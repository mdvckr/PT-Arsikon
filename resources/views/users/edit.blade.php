<x-app-layout>
    <x-slot name="title">Edit User</x-slot>

    <div class="breadcrumb">
        <a href="{{ route('users.index') }}">Data Pengguna</a>
        <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:10px;"></i></span>
        <span>Edit: {{ $user->name }}</span>
    </div>

    <div class="card" style="max-width:600px;">
        <div class="card-header">
            <i class="fas fa-user-pen text-warning"></i> <span class="card-title">Form Edit Pengguna</span>
        </div>
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger mb-3" style="background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:12px;border-radius:6px;">
                    <strong style="display:block;margin-bottom:4px;"><i class="fas fa-exclamation-circle"></i> Terjadi kesalahan validasi:</strong>
                    <ul style="margin:0;padding-left:20px;font-size:13px;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('users.update', $user) }}">
                @csrf @method('PUT')
                <div class="mb-3">
                    <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control" required>
                </div>
                <div class="grid grid-2">
                    <div class="mb-3">
                        <label class="form-label">Password Baru <span class="text-muted" style="font-weight:normal;font-size:12px;">(Kosongkan jika tetap)</span></label>
                        <input type="password" name="password" class="form-control" placeholder="Minimal 8 karakter">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Konfirmasi Password Baru</label>
                        <input type="password" name="password_confirmation" class="form-control" placeholder="Ulangi password">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Status Akun</label>
                    <select name="is_active" class="form-control">
                        <option value="1" {{ old('is_active', $user->is_active ? '1' : '0') == '1' ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ old('is_active', $user->is_active ? '1' : '0') == '0' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="form-label">Role / Peran Akses <span class="text-danger">*</span></label>
                    <div class="grid grid-2" style="gap:10px;background:#f8fafc;padding:12px;border-radius:6px;border:1px solid #e2e8f0;">
                        @php $userRoles = $user->roles->pluck('name')->toArray(); @endphp
                        @foreach($roles as $role)
                        <label class="flex items-center gap-2" style="cursor:pointer;font-size:13px;">
                            <input type="checkbox" name="roles[]" value="{{ $role->name }}" 
                                {{ in_array($role->name, old('roles', $userRoles)) ? 'checked' : '' }}>
                            {{ ucfirst($role->name) }}
                        </label>
                        @endforeach
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label">Penugasan Gudang Akses</label>
                    <p class="text-muted" style="font-size:12px;margin-top:-4px;margin-bottom:8px;">Pilih gudang yang dapat diakses oleh user ini.</p>
                    <div class="grid grid-2" style="gap:10px;background:#f8fafc;padding:12px;border-radius:6px;border:1px solid #e2e8f0;">
                        @php $assignedWarehouses = $user->warehouses->pluck('id')->toArray(); @endphp
                        @foreach($warehouses as $wh)
                        <label class="flex items-center gap-2" style="cursor:pointer;font-size:13px;">
                            <input type="checkbox" name="warehouse_ids[]" value="{{ $wh->id }}"
                                {{ in_array($wh->id, old('warehouse_ids', $assignedWarehouses)) ? 'checked' : '' }}>
                            <span>{{ $wh->name }} <span class="badge badge-secondary" style="font-size:10px;">{{ $wh->is_central ? 'Pusat' : 'Proyek' }}</span></span>
                        </label>
                        @endforeach
                    </div>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Perubahan</button>
                    <a href="{{ route('users.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
