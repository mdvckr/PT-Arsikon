<x-app-layout>
    <x-slot name="title">Form Pengembalian Material</x-slot>

    <div class="mb-4">
        <h2 style="font-size:20px;font-weight:700;color:#0f172a;">Form Pengembalian Material (Return)</h2>
        <p style="font-size:13px;color:#64748b;">Pengembalian sisa/kerusakan material dari proyek ke gudang pusat</p>
    </div>

    <div class="card" style="max-width: 800px;">
        <form method="POST" action="{{ route('returns.store') }}">
            @csrf
            <div style="display:grid;gap:16px;">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                    <div>
                        <label class="form-label required">Dari Gudang Proyek</label>
                        <select name="from_warehouse_id" class="form-select" required>
                            <option value="">-- Pilih Gudang Proyek --</option>
                            @foreach($warehouses as $wh)
                                @if(!$wh->is_central)
                                    <option value="{{ $wh->id }}" {{ old('from_warehouse_id')==$wh->id ? 'selected':'' }}>{{ $wh->name }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label required">Tujuan Pengembalian</label>
                        <select name="to_warehouse_id" class="form-select" required>
                            @if($central)
                                <option value="{{ $central->id }}" selected>{{ $central->name }} (Pusat)</option>
                            @else
                                @foreach($warehouses as $wh)
                                    @if($wh->is_central)
                                        <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                                    @endif
                                @endforeach
                            @endif
                        </select>
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                    <div>
                        <label class="form-label required">Alasan Pengembalian</label>
                        <select name="reason" class="form-select" required>
                            <option value="excess">Kelebihan Material / Sisa Proyek</option>
                            <option value="damaged">Material Rusak / Cacat</option>
                            <option value="wrong_item">Salah Kirim Material</option>
                            <option value="project_complete">Proyek Selesai</option>
                            <option value="other">Lainnya</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label required">Tanggal Pengembalian</label>
                        <input type="date" name="return_date" value="{{ old('return_date', date('Y-m-d')) }}" class="form-input" required>
                    </div>
                </div>

                <hr style="border:0;border-top:1px solid #e2e8f0;margin:8px 0;">
                <h4 style="font-size:15px;font-weight:600;color:#0f172a;">Item Material yang Dikembalikan</h4>

                <table class="table" style="font-size:13px;">
                    <thead>
                        <tr>
                            <th style="width:40%;">Material</th>
                            <th style="width:20%;">Jumlah (Qty)</th>
                            <th style="width:25%;">Kondisi</th>
                            <th style="width:15%;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="return-items-body">
                        <tr id="row-0">
                            <td>
                                <select name="items[0][material_id]" class="form-select" required>
                                    <option value="">-- Pilih Material --</option>
                                    @foreach($materials as $m)
                                        <option value="{{ $m->id }}">{{ $m->name }} {{ $m->type ? '['.$m->type.']' : '' }} ({{ $m->unit->abbreviation ?? 'unit' }})</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="number" name="items[0][quantity]" class="form-input" min="0.01" step="0.01" placeholder="Qty" required>
                            </td>
                            <td>
                                <select name="items[0][condition]" class="form-select" required>
                                    <option value="good">Baik (Dapat Dipakai)</option>
                                    <option value="damaged">Rusak (Perlu Perbaikan)</option>
                                    <option value="unusable">Hancur / Afkir</option>
                                </select>
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-danger" disabled onclick="removeRow(this)">Hapus</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <button type="button" class="btn btn-sm btn-secondary" style="width:fit-content;" onclick="addRow()">+ Tambah Material</button>

                <div>
                    <label class="form-label">Catatan Tambahan</label>
                    <textarea name="notes" class="form-input" rows="2">{{ old('notes') }}</textarea>
                </div>

                <div class="flex justify-end gap-2 mt-2">
                    <a href="{{ route('returns.index') }}" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary">Kirim Pengembalian</button>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        let rowIdx = 1;
        function addRow() {
            const tbody = document.getElementById('return-items-body');
            const row0 = document.getElementById('row-0');
            const newRow = row0.cloneNode(true);
            newRow.id = 'row-' + rowIdx;
            
            newRow.querySelector('select[name^="items[0][material_id]"]').name = `items[${rowIdx}][material_id]`;
            newRow.querySelector('input[name^="items[0][quantity]"]').name = `items[${rowIdx}][quantity]`;
            newRow.querySelector('select[name^="items[0][condition]"]').name = `items[${rowIdx}][condition]`;
            
            const btn = newRow.querySelector('button');
            btn.disabled = false;
            
            tbody.appendChild(newRow);
            rowIdx++;
        }
        function removeRow(btn) {
            btn.closest('tr').remove();
        }
    </script>
    @endpush
</x-app-layout>
