<x-app-layout>
    <x-slot name="title">Tambah User</x-slot>

    <div class="breadcrumb">
        <a href="{{ route('users.index') }}">Data Pengguna</a>
        <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:10px;"></i></span>
        <span>Tambah Baru</span>
    </div>

    <div class="card" style="max-width:600px;">
        <div class="card-header">
            <i class="fas fa-user-plus text-primary"></i> <span class="card-title">Form Tambah Pengguna</span>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('users.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password <span class="text-danger">*</span></label>
                    <input type="password" name="password" class="form-control" required minlength="8">
                </div>
                <div class="mb-3">
                    <label class="form-label">Konfirmasi Password <span class="text-danger">*</span></label>
                    <input type="password" name="password_confirmation" class="form-control" required minlength="8">
                </div>
                <div class="mb-4">
                    <label class="form-label">Role / Peran Akses <span class="text-danger">*</span></label>
                    <div class="grid grid-2" style="gap:10px;background:#f8fafc;padding:12px;border-radius:6px;">
                        @foreach($roles as $role)
                        <label class="flex items-center gap-2" style="cursor:pointer;">
                            <input type="checkbox" name="roles[]" value="{{ $role->name }}" 
                                {{ in_array($role->name, old('roles', [])) ? 'checked' : '' }}>
                            {{ ucfirst($role->name) }}
                        </label>
                        @endforeach
                    </div>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan User</button>
                    <a href="{{ route('users.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
