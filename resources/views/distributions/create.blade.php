<x-app-layout>
    <x-slot name="title">Buat Surat Jalan / Distribusi</x-slot>

    <div class="breadcrumb">
        <a href="{{ route('distributions.index') }}">Distribusi</a>
        <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:10px;"></i></span>
        <span>Buat Baru</span>
    </div>

    <form method="POST" action="{{ route('distributions.store') }}">
        @csrf
        @if($mr)
        <input type="hidden" name="material_request_id" value="{{ $mr->id }}">
        @endif

        <div class="grid" style="grid-template-columns:1fr 340px;gap:20px;align-items:start;">

            <div class="card">
                <div class="card-header">
                    <i class="fas fa-list text-primary"></i>
                    <span class="card-title">Item yang Dikirim</span>
                    @if(!$mr)
                    <button type="button" class="btn btn-sm btn-primary" onclick="addRow()">
                        <i class="fas fa-plus"></i> Tambah Item
                    </button>
                    @endif
                </div>
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="width:50%;">Material</th>
                                <th style="width:30%;">Qty Dikirim</th>
                                @if(!$mr)<th style="width:10%;"></th>@endif
                            </tr>
                        </thead>
                        <tbody id="itemsBody">
                            @if($mr)
                                @foreach($mr->items as $i => $item)
                                <tr>
                                    <td>
                                        <input type="hidden" name="items[{{ $i }}][material_id]" value="{{ $item->material_id }}">
                                        <div class="fw-600">{{ $item->material?->name }}</div>
                                        <div class="text-muted" style="font-size:11.5px;">Permintaan: {{ $item->quantity }} {{ $item->material?->unit?->abbreviation }}</div>
                                    </td>
                                    <td>
                                        <div style="display:flex;align-items:center;gap:8px;">
                                            <input type="number" name="items[{{ $i }}][quantity]" class="form-control"
                                                value="{{ $item->quantity }}" min="0.01" max="{{ $item->quantity }}" step="0.01" required style="width:100px;">
                                            <span class="text-muted" style="font-size:13px;">{{ $item->material?->unit?->abbreviation }}</span>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            @else
                                <tr id="row-0">
                                    <td>
                                        <select name="items[0][material_id]" class="form-control" required>
                                            <option value="">Pilih Material</option>
                                            {{-- To be populated via JS --}}
                                        </select>
                                    </td>
                                    <td><input type="number" name="items[0][quantity]" class="form-control" min="0.01" step="0.01" required></td>
                                    <td><button type="button" class="btn btn-sm btn-danger btn-icon" disabled><i class="fas fa-trash"></i></button></td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <i class="fas fa-truck text-primary"></i> <span class="card-title">Info Pengiriman</span>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Gudang Asal <span class="text-danger">*</span></label>
                        <select name="from_warehouse_id" class="form-control" required>
                            <option value="">Pilih Gudang Asal</option>
                            @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}" {{ old('from_warehouse_id') == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Gudang Tujuan <span class="text-danger">*</span></label>
                        <select name="to_warehouse_id" class="form-control" required>
                            <option value="">Pilih Gudang Tujuan</option>
                            @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}" {{ ($mr && $mr->warehouse_id == $wh->id) ? 'selected' : (old('to_warehouse_id') == $wh->id ? 'selected' : '') }}>
                                {{ $wh->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal Kirim <span class="text-danger">*</span></label>
                        <input type="date" name="delivery_date" class="form-control" value="{{ old('delivery_date', date('Y-m-d')) }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Supir / Kurir</label>
                        <input type="text" name="driver_name" value="{{ old('driver_name') }}" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">No. Kendaraan (Plat)</label>
                        <input type="text" name="vehicle_number" value="{{ old('vehicle_number') }}" class="form-control">
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Catatan</label>
                        <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-full" style="justify-content:center;">
                        <i class="fas fa-save"></i> Buat Surat Jalan
                    </button>
                </div>
            </div>

        </div>
    </form>

    @if(!$mr)
    @push('scripts')
    <script>
        // Note: For a fully generic create (without MR), you'd load materials via ajax or inject all materials.
        // Simplified here for brevity.
        let rowCount = 1;
        function addRow() {
            const tbody = document.getElementById('itemsBody');
            const newRow = document.getElementById('row-0').cloneNode(true);
            newRow.id = 'row-' + rowCount;
            newRow.querySelector('select').name = `items[${rowCount}][material_id]`;
            newRow.querySelector('input').name = `items[${rowCount}][quantity]`;
            newRow.querySelector('button').disabled = false;
            newRow.querySelector('button').onclick = function() { newRow.remove(); };
            tbody.appendChild(newRow);
            rowCount++;
        }
    </script>
    @endpush
    @endif
</x-app-layout>
