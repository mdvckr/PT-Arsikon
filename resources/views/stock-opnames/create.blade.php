<x-app-layout>
    <x-slot name="title">Buat Stock Opname</x-slot>

    <div class="breadcrumb">
        <a href="{{ route('stock-opnames.index') }}">Stock Opname</a>
        <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:10px;"></i></span>
        <span>Buat Baru</span>
    </div>

    <form method="POST" action="{{ route('stock-opnames.store') }}">
        @csrf
        <div class="grid" style="grid-template-columns:1fr 340px;gap:20px;align-items:start;">

            <div class="card">
                <div class="card-header">
                    <i class="fas fa-list-check text-primary"></i> <span class="card-title">Input Hasil Audit Fisik</span>
                </div>
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Material</th>
                                <th>Stok Sistem</th>
                                <th style="width:160px;">Stok Fisik Aktual <span class="text-danger">*</span></th>
                                <th>Satuan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($inventory as $i => $inv)
                            <tr>
                                <td>
                                    <input type="hidden" name="items[{{ $i }}][inventory_id]" value="{{ $inv->id }}">
                                    <div class="fw-600">{{ $inv->material?->name }}</div>
                                    <div class="text-muted" style="font-size:11.5px;">{{ $inv->material?->code }}</div>
                                </td>
                                <td>{{ number_format($inv->quantity, 2) }}</td>
                                <td>
                                    <input type="number" name="items[{{ $i }}][physical_quantity]" class="form-control"
                                        value="{{ old('items.'.$i.'.physical_quantity', $inv->quantity) }}" min="0" step="0.01" required>
                                </td>
                                <td>{{ $inv->material?->unit?->abbreviation }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-muted p-4">Tidak ada inventori di gudang ini yang dapat diaudit.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <i class="fas fa-file-alt text-primary"></i> <span class="card-title">Informasi Audit</span>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Gudang <span class="text-danger">*</span></label>
                        <select name="warehouse_id" class="form-control" required disabled>
                            <option value="">Pilih Gudang</option>
                            @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}" {{ $warehouseId == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                            @endforeach
                        </select>
                        <input type="hidden" name="warehouse_id" value="{{ $warehouseId }}">
                        <div class="text-muted mt-1" style="font-size:11px;">Hanya dapat membuat SO untuk gudang aktif.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal Audit <span class="text-danger">*</span></label>
                        <input type="date" name="opname_date" class="form-control" value="{{ old('opname_date', date('Y-m-d')) }}" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Catatan</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Alasan audit atau catatan auditor...">{{ old('notes') }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-full" style="justify-content:center;" {{ $inventory->isEmpty() ? 'disabled' : '' }}>
                        <i class="fas fa-save"></i> Simpan Draft Opname
                    </button>
                    <a href="{{ route('stock-opnames.index') }}" class="btn btn-secondary w-full mt-2" style="justify-content:center;">Batal</a>
                </div>
            </div>
        </div>
    </form>
</x-app-layout>
