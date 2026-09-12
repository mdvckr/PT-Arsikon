<x-app-layout>
    <x-slot name="title">Tambah Gudang</x-slot>

    <div class="breadcrumb">
        <a href="{{ route('warehouses.index') }}">Data Gudang</a>
        <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:10px;"></i></span>
        <span>Tambah Baru</span>
    </div>

    <div class="card" style="max-width:600px;">
        <div class="card-header">
            <i class="fas fa-plus-circle text-primary"></i> <span class="card-title">Form Tambah Gudang</span>
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

            <form method="POST" action="{{ route('warehouses.store') }}">
                @csrf
                <div class="grid grid-2">
                    <div class="mb-3">
                        <label class="form-label">Kode Gudang <span class="text-muted" style="font-size:12px;font-weight:normal;">(Opsional, otomatis jika kosong)</span></label>
                        <input type="text" name="code" value="{{ old('code') }}" class="form-control" placeholder="mis. W-PRJ-003">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Gudang <span class="text-danger">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" class="form-control" placeholder="mis. Gudang Proyek C" required>
                    </div>
                </div>
                <div class="grid grid-2">
                    <div class="mb-3">
                        <label class="form-label">Tipe Gudang <span class="text-danger">*</span></label>
                        <select name="type" class="form-control" id="warehouseType" required onchange="toggleProject()">
                            <option value="project" {{ old('type', 'project') == 'project' ? 'selected' : '' }}>Gudang Proyek (Project)</option>
                            <option value="central" {{ old('type') == 'central' ? 'selected' : '' }}>Gudang Pusat (Central)</option>
                        </select>
                    </div>
                    <div class="mb-3" id="projectWrapper" style="display:{{ old('type', 'project') == 'project' ? 'block' : 'none' }};">
                        <label class="form-label">Terkait Proyek</label>
                        <select name="project_id" class="form-control" id="projectId">
                            <option value="">Pilih Proyek</option>
                            @foreach(\App\Models\Project::where('status', '!=', 'completed')->get() as $proj)
                            <option value="{{ $proj->id }}" {{ old('project_id') == $proj->id ? 'selected' : '' }}>{{ $proj->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label">Alamat / Lokasi Gudang</label>
                    <textarea name="address" class="form-control" rows="3" placeholder="Alamat lengkap lokasi gudang...">{{ old('address', old('location')) }}</textarea>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Gudang</button>
                    <a href="{{ route('warehouses.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function toggleProject() {
            const type = document.getElementById('warehouseType').value;
            const projectWrapper = document.getElementById('projectWrapper');
            const projectId = document.getElementById('projectId');
            if (type === 'project') {
                projectWrapper.style.display = 'block';
                projectId.required = true;
            } else {
                projectWrapper.style.display = 'none';
                projectId.required = false;
                projectId.value = '';
            }
        }
        document.addEventListener('DOMContentLoaded', toggleProject);
    </script>
    @endpush
</x-app-layout>
