<x-app-layout>
    <x-slot name="title">Edit Supplier</x-slot>

    <div class="breadcrumb">
        <a href="{{ route('suppliers.index') }}">Supplier</a>
        <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:10px;"></i></span>
        <span>Edit: {{ $supplier->name }}</span>
    </div>

    <div class="card" style="max-width:600px;">
        <div class="card-header">
            <i class="fas fa-pen text-warning"></i> <span class="card-title">Form Edit Supplier</span>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('suppliers.update', $supplier) }}">
                @csrf @method('PUT')
                <div class="mb-3">
                    <label class="form-label">Nama Supplier <span class="text-danger">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $supplier->name) }}" class="form-control" required>
                </div>
                <div class="grid grid-2">
                    <div class="mb-3">
                        <label class="form-label">Telepon</label>
                        <input type="text" name="phone" value="{{ old('phone', $supplier->phone) }}" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" value="{{ old('email', $supplier->email) }}" class="form-control">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Contact Person</label>
                    <input type="text" name="contact_person" value="{{ old('contact_person', $supplier->contact_person) }}" class="form-control">
                </div>
                <div class="mb-4">
                    <label class="form-label">Alamat</label>
                    <textarea name="address" class="form-control" rows="3">{{ old('address', $supplier->address) }}</textarea>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Perubahan</button>
                    <a href="{{ route('suppliers.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
