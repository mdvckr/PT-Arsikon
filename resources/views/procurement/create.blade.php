<x-app-layout>
    <x-slot name="title">Buat Permintaan Pengadaan</x-slot>
    <div class="breadcrumb">
        <a href="{{ route("procurement.index") }}">Permintaan Pengadaan</a>
        <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:10px;"></i></span>
        <span>Buat Baru</span>
    </div>
    <form method="POST" action="{{ route("procurement.store") }}">
        @csrf
        <div class="grid" style="grid-template-columns:1fr 320px;gap:20px;align-items:start;">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-list text-primary"></i>
                    <span class="card-title">Daftar Item yang Dibutuhkan</span>
                    <button type="button" class="btn btn-sm btn-primary" onclick="addRow()"><i class="fas fa-plus"></i> Tambah Item</button>
                </div>
                <div class="table-wrap">
                    <table class="data-table" id="itemsTable">
                        <thead><tr>
                            <th style="width:40%;">Material</th>
                            <th style="width:15%;">Qty</th>
                            <th style="width:25%;">Est. Harga (Rp)</th>
                            <th style="width:15%;">Keterangan</th>
                            <th style="width:5%;"></th>
                        </tr></thead>
                        <tbody id="itemsBody">
                            <tr id="row-0">
                                <td><select name="items[0][material_id]" class="form-control" required>
                                    <option value="">Pilih Material</option>
                                    @foreach($materials as $m)
                                    <option value="{{ $m->id }}">{{ $m->code }} - {{ $m->name }} ({{ $m->unit?->abbreviation }})</option>
                                    @endforeach
                                </select></td>
                                <td><input type="number" name="items[0][quantity]" class="form-control" min="0.01" step="0.01" value="1" required></td>
                                <td><input type="number" name="items[0][estimated_price]" class="form-control" min="0" step="100" value="0"></td>
                                <td><input type="text" name="items[0][notes]" class="form-control" placeholder="Opsional"></td>
                                <td><button type="button" class="btn btn-sm btn-danger btn-icon" onclick="removeRow(0)" disabled><i class="fas fa-trash"></i></button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card">
                <div class="card-header"><i class="fas fa-file-alt text-primary"></i><span class="card-title">Informasi Pengadaan</span></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Dibutuhkan Sebelum</label>
                        <input type="date" name="needed_by" class="form-control" value="{{ old("needed_by") }}">
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Justifikasi / Alasan</label>
                        <textarea name="justification" class="form-control" rows="4" placeholder="Jelaskan alasan pengadaan...">{{ old("justification") }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-full" style="justify-content:center;"><i class="fas fa-paper-plane"></i> Ajukan PR</button>
                    <a href="{{ route("procurement.index") }}" class="btn btn-secondary w-full mt-2" style="justify-content:center;">Batal</a>
                </div>
            </div>
        </div>
    </form>
    @push("scripts")
    <script>
        let rowCount = 1;
        const materials = @json($materials->map(fn($m) => ["id"=>$m->id,"code"=>$m->code,"name"=>$m->name,"abbr"=>$m->unit?->abbreviation]));
        function buildSelect(idx) {
            let opts = "<option value=''>Pilih Material</option>";
            materials.forEach(m => { opts += `<option value="${m.id}">${m.code} - ${m.name} (${m.abbr ?? ""})</option>`; });
            return `<select name="items[${idx}][material_id]" class="form-control" required>${opts}</select>`;
        }
        function addRow() {
            const idx = rowCount++;
            const tr = document.createElement("tr");
            tr.id = "row-"+idx;
            tr.innerHTML = `<td>${buildSelect(idx)}</td>
                <td><input type="number" name="items[${idx}][quantity]" class="form-control" min="0.01" step="0.01" value="1" required></td>
                <td><input type="number" name="items[${idx}][estimated_price]" class="form-control" min="0" step="100" value="0"></td>
                <td><input type="text" name="items[${idx}][notes]" class="form-control"></td>
                <td><button type="button" class="btn btn-sm btn-danger btn-icon" onclick="removeRow(${idx})"><i class="fas fa-trash"></i></button></td>`;
            document.getElementById("itemsBody").appendChild(tr);
        }
        function removeRow(idx) { const r = document.getElementById("row-"+idx); if(r) r.remove(); }
    </script>
    @endpush
</x-app-layout>
