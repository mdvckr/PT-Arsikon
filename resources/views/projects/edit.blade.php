<x-app-layout>
    <x-slot name="title">Edit Proyek</x-slot>

    <div class="breadcrumb">
        <a href="{{ route('projects.index') }}">Data Proyek</a>
        <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:10px;"></i></span>
        <span>Edit: {{ $project->name }}</span>
    </div>

    <div class="card" style="max-width:700px;">
        <div class="card-header">
            <i class="fas fa-pen text-warning"></i> <span class="card-title">Form Edit Proyek</span>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('projects.update', $project) }}">
                @csrf @method('PUT')
                <div class="grid grid-2">
                    <div class="mb-3">
                        <label class="form-label">Nama Proyek <span class="text-danger">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $project->name) }}" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Klien / Owner</label>
                        <input type="text" name="client_name" value="{{ old('client_name', $project->client_name) }}" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
                        <input type="date" name="start_date" value="{{ old('start_date', optional($project->start_date)->format('Y-m-d')) }}" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Estimasi Tanggal Selesai</label>
                        <input type="date" name="end_date" value="{{ old('end_date', optional($project->end_date)->format('Y-m-d')) }}" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status Proyek <span class="text-danger">*</span></label>
                        <select name="status" class="form-control" required>
                            <option value="planning" {{ old('status', $project->status) == 'planning' ? 'selected' : '' }}>Perencanaan (Planning)</option>
                            <option value="ongoing" {{ old('status', $project->status) == 'ongoing' ? 'selected' : '' }}>Sedang Berjalan (Ongoing)</option>
                            <option value="completed" {{ old('status', $project->status) == 'completed' ? 'selected' : '' }}>Selesai (Completed)</option>
                            <option value="suspended" {{ old('status', $project->status) == 'suspended' ? 'selected' : '' }}>Ditangguhkan (Suspended)</option>
                        </select>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label">Lokasi Proyek</label>
                    <textarea name="location" class="form-control" rows="2">{{ old('location', $project->location) }}</textarea>
                </div>
                <div class="mb-4">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="description" class="form-control" rows="3">{{ old('description', $project->description) }}</textarea>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Perubahan</button>
                    <a href="{{ route('projects.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
