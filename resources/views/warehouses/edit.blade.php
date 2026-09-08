<x-app-layout>
    <x-slot name="title">Edit Gudang</x-slot>

    <div class="breadcrumb">
        <a href="{{ route('warehouses.index') }}">Data Gudang</a>
        <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:10px;"></i></span>
        <span>Edit: {{ $warehouse->name }}</span>
    </div>

    <div class="card" style="max-width:600px;">
        <div class="card-header">
            <i class="fas fa-pen text-warning"></i> <span class="card-title">Form Edit Gudang</span>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('warehouses.update', $warehouse) }}">
                @csrf @method('PUT')
                <div class="mb-3">
                    <label class="form-label">Nama Gudang <span class="text-danger">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $warehouse->name) }}" class="form-control" required>
                </div>
                <div class="grid grid-2">
                    <div class="mb-3">
                        <label class="form-label">Tipe Gudang <span class="text-danger">*</span></label>
                        <select name="type" class="form-control" id="warehouseType" required onchange="toggleProject()">
                            <option value="main" {{ old('type', $warehouse->type) == 'main' ? 'selected' : '' }}>Gudang Pusat (Main)</option>
                            <option value="project" {{ old('type', $warehouse->type) == 'project' ? 'selected' : '' }}>Gudang Proyek (Project)</option>
                        </select>
                    </div>
                    <div class="mb-3" id="projectWrapper" style="display:{{ old('type', $warehouse->type) == 'project' ? 'block' : 'none' }};">
                        <label class="form-label">Terkait Proyek</label>
                        <select name="project_id" class="form-control" id="projectId">
                            <option value="">Pilih Proyek</option>
                            @foreach(\App\Models\Project::all() as $proj)
                            <option value="{{ $proj->id }}" {{ old('project_id', $warehouse->project_id) == $proj->id ? 'selected' : '' }}>{{ $proj->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Status Gudang</label>
                    <select name="is_active" class="form-control">
                        <option value="1" {{ old('is_active', $warehouse->is_active) ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ old('is_active', $warehouse->is_active) ? '' : 'selected' }}>Nonaktif</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="form-label">Lokasi / Alamat</label>
                    <textarea name="location" class="form-control" rows="3">{{ old('location', $warehouse->location) }}</textarea>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Perubahan</button>
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
