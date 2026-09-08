<x-app-layout>
    <x-slot name="title">Edit Alat</x-slot>

    <div class="breadcrumb">
        <a href="{{ route('tools.index') }}">Alat</a>
        <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:10px;"></i></span>
        <span>Edit: {{ $tool->name }}</span>
    </div>

    <div class="card" style="max-width:700px;">
        <div class="card-header">
            <i class="fas fa-pen text-warning"></i> <span class="card-title">Form Edit Alat</span>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('tools.update', $tool) }}">
                @csrf @method('PUT')
                <div class="grid grid-2">
                    <div>
                        <label class="form-label">Kode Alat <span class="text-danger">*</span></label>
                        <input type="text" name="code" value="{{ old('code', $tool->code) }}" class="form-control" required>
                    </div>
                    <div>
                        <label class="form-label">Nama Alat <span class="text-danger">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $tool->name) }}" class="form-control" required>
                    </div>
                    <div>
                        <label class="form-label">Kategori</label>
                        <select name="category_id" class="form-control">
                            <option value="">Pilih Kategori</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id', $tool->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Serial Number</label>
                        <input type="text" name="serial_number" value="{{ old('serial_number', $tool->serial_number) }}" class="form-control">
                    </div>
                    <div>
                        <label class="form-label">Merk / Brand</label>
                        <input type="text" name="brand" value="{{ old('brand', $tool->brand) }}" class="form-control">
                    </div>
                    <div>
                        <label class="form-label">Kondisi <span class="text-danger">*</span></label>
                        <select name="condition" class="form-control" required>
                            <option value="good" {{ old('condition', $tool->condition) == 'good' ? 'selected' : '' }}>Baik (Good)</option>
                            <option value="damaged" {{ old('condition', $tool->condition) == 'damaged' ? 'selected' : '' }}>Rusak (Damaged)</option>
                            <option value="under_maintenance" {{ old('condition', $tool->condition) == 'under_maintenance' ? 'selected' : '' }}>Dalam Perbaikan</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Tgl. Pembelian</label>
                        <input type="date" name="purchase_date" value="{{ old('purchase_date', optional($tool->purchase_date)->format('Y-m-d')) }}" class="form-control">
                    </div>
                    <div>
                        <label class="form-label">Harga Beli (Rp)</label>
                        <input type="number" name="purchase_price" value="{{ old('purchase_price', $tool->purchase_price) }}" class="form-control" min="0" step="100">
                    </div>
                </div>
                <div class="mt-3">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="description" class="form-control" rows="3">{{ old('description', $tool->description) }}</textarea>
                </div>
                <div class="flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Perubahan</button>
                    <a href="{{ route('tools.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
