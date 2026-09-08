<x-app-layout>
    <x-slot name="title">Buat Surat Jalan / Pengiriman Barang & Alat</x-slot>

    <div class="breadcrumb">
        <a href="{{ route('distributions.index') }}">Distribusi & Surat Jalan</a>
        <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:10px;"></i></span>
        <span>Buat Surat Jalan Baru</span>
    </div>

    <div id="ajax-alert" style="display:none;margin-bottom:16px;padding:12px 16px;border-radius:6px;font-size:14px;"></div>

    <form id="distribution-form" method="POST" action="{{ route('distributions.store') }}">
        @csrf
        @if($mr)
        <input type="hidden" name="material_request_id" value="{{ $mr->id }}">
        @endif

        <div class="grid" style="grid-template-columns:1fr 340px;gap:20px;align-items:start;">

            <div class="card">
                <div class="card-header flex justify-between items-center">
                    <div>
                        <i class="fas fa-boxes-stacked text-primary"></i>
                        <span class="card-title">Daftar Barang / Alat yang Dikirim</span>
                    </div>
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
                                <th style="width:55%;">Barang / Alat (Kategori & Spesifikasi/Tipe)</th>
                                <th style="width:35%;">Jumlah (Qty)</th>
                                @if(!$mr)<th style="width:10%;"></th>@endif
                            </tr>
                        </thead>
                        <tbody id="itemsBody">
                            @if($mr)
                                @foreach($mr->items as $i => $item)
                                <tr>
                                    <td>
                                        <input type="hidden" name="items[{{ $i }}][material_id]" value="{{ $item->material_id }}">
                                        <div class="fw-600">{{ $item->material?->name }} {{ $item->material?->type ? '('.$item->material->type.')' : '' }}</div>
                                        <div class="text-muted" style="font-size:11.5px;">
                                            Kategori: {{ $item->material?->category?->name ?? 'Material' }} | Permintaan: {{ $item->quantity }} {{ $item->material?->unit?->abbreviation }}
                                        </div>
                                    </td>
                                    <td>
                                        <div style="display:flex;align-items:center;gap:8px;">
                                            <input type="number" name="items[{{ $i }}][quantity]" class="form-control"
                                                value="{{ $item->quantity }}" min="0.01" max="{{ $item->quantity }}" step="0.01" required style="width:120px;">
                                            <span class="text-muted" style="font-size:13px;">{{ $item->material?->unit?->abbreviation }}</span>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            @else
                                <tr id="row-0">
                                    <td>
                                        <select name="items[0][material_id]" class="form-control item-select" required>
                                            <option value="">-- Pilih Barang / Alat --</option>
                                            @if(isset($materials) && count($materials) > 0)
                                                <optgroup label="📦 MATERIAL (BAHAN BANGUNAN)">
                                                    @foreach($materials as $mat)
                                                        <option value="{{ $mat->id }}">
                                                            {{ $mat->name }} {{ $mat->type ? '['.$mat->type.']' : '' }} - (Kategori: {{ $mat->category?->name ?? 'Material' }})
                                                        </option>
                                                    @endforeach
                                                </optgroup>
                                            @endif
                                            @if(isset($tools) && count($tools) > 0)
                                                <optgroup label="🛠 ALAT (PERALATAN KERJA)">
                                                    @foreach($tools as $t)
                                                        <option value="{{ $t->id }}">
                                                            {{ $t->name }} {{ $t->type ? '['.$t->type.']' : '' }} - (S/N: {{ $t->serial_number ?? '-' }})
                                                        </option>
                                                    @endforeach
                                                </optgroup>
                                            @endif
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" name="items[0][quantity]" class="form-control" placeholder="Qty" min="0.01" step="0.01" required>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-danger btn-icon" disabled onclick="removeRow(this)"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <i class="fas fa-truck text-primary"></i> <span class="card-title">Informasi Surat Jalan</span>
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
                        <input type="text" name="driver_name" value="{{ old('driver_name') }}" placeholder="Pak Supir" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">No. Kendaraan (Plat)</label>
                        <input type="text" name="vehicle_number" value="{{ old('vehicle_number') }}" placeholder="B 1234 CD" class="form-control">
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Catatan Pengiriman</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Catatan tambahan...">{{ old('notes') }}</textarea>
                    </div>
                    <button type="submit" id="btn-submit" class="btn btn-primary w-full" style="justify-content:center;">
                        <i class="fas fa-paper-plane"></i> Buat Surat Jalan (AJAX)
                    </button>
                </div>
            </div>

        </div>
    </form>

    @push('scripts')
    <script>
        let rowCount = 1;

        function addRow() {
            const tbody = document.getElementById('itemsBody');
            const firstRow = document.getElementById('row-0');
            if(!firstRow) return;

            const newRow = firstRow.cloneNode(true);
            newRow.id = 'row-' + rowCount;
            
            const select = newRow.querySelector('select');
            if(select) {
                select.name = `items[${rowCount}][material_id]`;
                select.value = '';
            }

            const input = newRow.querySelector('input[type="number"]');
            if(input) {
                input.name = `items[${rowCount}][quantity]`;
                input.value = '';
            }

            const btn = newRow.querySelector('button');
            if(btn) {
                btn.disabled = false;
                btn.onclick = function() { newRow.remove(); };
            }

            tbody.appendChild(newRow);
            rowCount++;
        }

        function removeRow(btn) {
            const row = btn.closest('tr');
            if(row && row.id !== 'row-0') {
                row.remove();
            }
        }

        // AJAX Form Submission
        document.getElementById('distribution-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const form = this;
            const btn = document.getElementById('btn-submit');
            const alertBox = document.getElementById('ajax-alert');
            
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
            alertBox.style.display = 'none';

            const formData = new FormData(form);

            fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-paper-plane"></i> Buat Surat Jalan (AJAX)';

                if(data.success) {
                    alertBox.style.display = 'block';
                    alertBox.style.background = '#dcfce7';
                    alertBox.style.color = '#15803d';
                    alertBox.style.border = '1px solid #bbf7d0';
                    alertBox.innerHTML = `<strong>Berhasil!</strong> ${data.message} Mengalihkan...`;

                    setTimeout(() => {
                        window.location.href = data.redirect_url;
                    }, 1200);
                } else {
                    throw new Error(data.message || 'Terjadi kesalahan saat menyimpan data.');
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-paper-plane"></i> Buat Surat Jalan (AJAX)';
                alertBox.style.display = 'block';
                alertBox.style.background = '#fef2f2';
                alertBox.style.color = '#b91c1c';
                alertBox.style.border = '1px solid #fecaca';
                alertBox.innerHTML = `<strong>Gagal:</strong> ${err.message || 'Periksa kembali inputan Anda.'}`;
            });
        });
    </script>
    @endpush
</x-app-layout>
