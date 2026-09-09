<x-app-layout>
    <x-slot name="title">Buat Permintaan Material</x-slot>

    <div class="breadcrumb">
        <a href="{{ route('material-requests.index') }}">Permintaan Material</a>
        <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:10px;"></i></span>
        <span>Buat Baru</span>
    </div>

    <form method="POST" action="{{ route('material-requests.store') }}">
        @csrf
        <div class="grid" style="grid-template-columns:1fr 320px;gap:20px;align-items:start;">

            <div class="card">
                <div class="card-header">
                    <i class="fas fa-list text-primary"></i>
                    <span class="card-title">Daftar Material yang Diminta</span>
                    <button type="button" class="btn btn-sm btn-primary" onclick="addRow()">
                        <i class="fas fa-plus"></i> Tambah Item
                    </button>
                </div>
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="width:45%;">Material</th>
                                <th style="width:20%;">Jumlah</th>
                                <th style="width:25%;">Catatan</th>
                                <th style="width:10%;"></th>
                            </tr>
                        </thead>
                        <tbody id="itemsBody">
                            <tr id="row-0">
                                <td>
                                    <select name="items[0][material_id]" class="form-control" required>
                                        <option value="">Pilih Material</option>
                                        @foreach($materials as $mat)
                                        <option value="{{ $mat->id }}">{{ $mat->code }} - {{ $mat->name }} ({{ $mat->unit?->abbreviation }})</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td><input type="number" name="items[0][quantity]" class="form-control" min="0.01" step="0.01" value="1" required></td>
                                <td><input type="text" name="items[0][notes]" class="form-control" placeholder="Opsional"></td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-danger btn-icon" disabled>
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <i class="fas fa-file-alt text-primary"></i>
                    <span class="card-title">Informasi Permintaan</span>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Gudang Pemohon <span class="text-danger">*</span></label>
                        <select name="warehouse_id" class="form-control" required>
                            <option value="">Pilih Gudang</option>
                            @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}" {{ $warehouseId == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Dibutuhkan Pada <span class="text-danger">*</span></label>
                        <input type="date" name="needed_at" class="form-control"
                            value="{{ old('needed_at') }}" min="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Catatan</label>
                        <textarea name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-full" style="justify-content:center;">
                        <i class="fas fa-paper-plane"></i> Ajukan Permintaan
                    </button>
                    <a href="{{ route('material-requests.index') }}" class="btn btn-secondary w-full mt-2" style="justify-content:center;">Batal</a>
                </div>
            </div>
        </div>
    </form>

    @push('scripts')
    <script>
        let rowCount = 1;
        const materials = @json($materialsJson);

        function buildSelect(idx) {
            let opts = '<option value="">Pilih Material</option>';
            materials.forEach(m => {
                opts += `<option value="${m.id}">${m.code} - ${m.name} (${m.abbr ?? ''})</option>`;
            });
            return `<select name="items[${idx}][material_id]" class="form-control" required>${opts}</select>`;
        }

        function addRow() {
            const idx = rowCount++;
            const tbody = document.getElementById('itemsBody');
            const tr = document.createElement('tr');
            tr.id = `row-${idx}`;
            tr.innerHTML = `
                <td>${buildSelect(idx)}</td>
                <td><input type="number" name="items[${idx}][quantity]" class="form-control" min="0.01" step="0.01" value="1" required></td>
                <td><input type="text" name="items[${idx}][notes]" class="form-control" placeholder="Opsional"></td>
                <td><button type="button" class="btn btn-sm btn-danger btn-icon" onclick="this.closest('tr').remove()"><i class="fas fa-trash"></i></button></td>
            `;
            tbody.appendChild(tr);
        }
    </script>
    @endpush
</x-app-layout>
