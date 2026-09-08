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
                <div class="mb-3">
                    <label class="form-label">Password <span class="text-muted" style="font-weight:normal;font-size:12px;">(Kosongkan jika tidak ingin mengubah password)</span></label>
                    <input type="password" name="password" class="form-control" minlength="8">
                </div>
                <div class="mb-3">
                    <label class="form-label">Konfirmasi Password</label>
                    <input type="password" name="password_confirmation" class="form-control" minlength="8">
                </div>
                <div class="mb-3">
                    <label class="form-label">Status Akun</label>
                    <select name="is_active" class="form-control">
                        <option value="1" {{ old('is_active', $user->is_active) ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ old('is_active', $user->is_active) ? '' : 'selected' }}>Nonaktif</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="form-label">Role / Peran Akses <span class="text-danger">*</span></label>
                    <div class="grid grid-2" style="gap:10px;background:#f8fafc;padding:12px;border-radius:6px;">
                        @php $userRoles = $user->roles->pluck('name')->toArray(); @endphp
                        @foreach($roles as $role)
                        <label class="flex items-center gap-2" style="cursor:pointer;">
                            <input type="checkbox" name="roles[]" value="{{ $role->name }}" 
                                {{ in_array($role->name, old('roles', $userRoles)) ? 'checked' : '' }}>
                            {{ ucfirst($role->name) }}
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
