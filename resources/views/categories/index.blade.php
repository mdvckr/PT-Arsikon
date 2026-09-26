<x-app-layout>
    <x-slot name="title">Kategori & Satuan</x-slot>

    <div class="mb-4">
        <h2 class="fw-700" style="font-size:20px;color:#0f172a;">Kategori & Satuan</h2>
        <p class="text-muted" style="font-size:13px;margin-top:2px;">Kelola pengelompokan material/alat dan satuan ukurnya</p>
    </div>

    <div class="grid grid-2" style="gap:24px; align-items:start;">

        {{-- Kategori Section --}}
        <div class="card">
            <div class="card-header">
                <i class="fas fa-tags text-primary"></i>
                <span class="card-title">Kategori</span>
                @can('create categories')
                <button class="btn btn-sm btn-primary" onclick="document.getElementById('modalAddCategory').classList.add('show')">
                    <i class="fas fa-plus"></i> Tambah
                </button>
                @endcan
            </div>
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Nama Kategori</th>
                            <th>Total Item</th>
                            <th style="width:80px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $cat)
                        <tr>
                            <td>
                                <div class="fw-600">{{ $cat->name }}</div>
                                <div class="text-muted" style="font-size:11px;">{{ $cat->description ?? '-' }}</div>
                            </td>
                            <td>
                                <span class="badge badge-gray">{{ $cat->materials_count }} Material</span>
                                <span class="badge badge-gray mt-1">{{ $cat->tools_count }} Alat</span>
                            </td>
                            <td>
                                <div class="flex gap-1">
                                    @can('edit categories')
                                    <button class="btn btn-sm btn-warning btn-icon" onclick="editCategory({{ $cat->id }}, '{{ addslashes($cat->name) }}', '{{ addslashes($cat->description) }}')">
                                        <i class="fas fa-pen"></i>
                                    </button>
                                    @endcan
                                    @can('delete categories')
                                    <button type="button" class="btn btn-sm btn-danger btn-icon" onclick="openDeleteCategory({{ $cat->id }}, '{{ addslashes($cat->name) }}', {{ $cat->materials_count + $cat->tools_count }})" title="Hapus Kategori">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-muted p-4">Belum ada kategori</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($categories->hasPages())
            <div style="padding:12px;border-top:1px solid #f1f5f9;">{{ $categories->links() }}</div>
            @endif
        </div>

        {{-- Satuan Section --}}
        <div class="card">
            <div class="card-header">
                <i class="fas fa-weight-scale text-primary"></i>
                <span class="card-title">Satuan (Unit)</span>
                @can('create categories')
                <button class="btn btn-sm btn-primary" onclick="document.getElementById('modalAddUnit').classList.add('show')">
                    <i class="fas fa-plus"></i> Tambah
                </button>
                @endcan
            </div>
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Nama Satuan</th>
                            <th>Singkatan</th>
                            <th>Digunakan</th>
                            <th style="width:80px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($units as $unit)
                        <tr>
                            <td class="fw-600">{{ $unit->name }}</td>
                            <td><span class="badge badge-primary">{{ $unit->abbreviation }}</span></td>
                            <td>{{ $unit->materials_count }} Material</td>
                            <td>
                                <div class="flex gap-1">
                                    @can('edit categories')
                                    <button class="btn btn-sm btn-warning btn-icon" onclick="editUnit({{ $unit->id }}, '{{ addslashes($unit->name) }}', '{{ addslashes($unit->abbreviation) }}')">
                                        <i class="fas fa-pen"></i>
                                    </button>
                                    @endcan
                                    @can('delete categories')
                                    <form method="POST" action="{{ route('units.destroy', $unit) }}" onsubmit="return confirm('Hapus satuan ini?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-danger btn-icon"><i class="fas fa-trash"></i></button>
                                    </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted p-4">Belum ada satuan</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($units->hasPages())
            <div style="padding:12px;border-top:1px solid #f1f5f9;">{{ $units->links() }}</div>
            @endif
        </div>

    </div>

    {{-- Modals for Category --}}
    <div class="modal-overlay" id="modalAddCategory">
        <div class="modal-box">
            <div class="modal-header">
                <i class="fas fa-plus text-primary"></i> <span class="modal-title">Tambah Kategori</span>
                <button class="btn-close-modal" onclick="this.closest('.modal-overlay').classList.remove('show')"><i class="fas fa-times"></i></button>
            </div>
            <form method="POST" action="{{ route('categories.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div>
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-overlay" id="modalEditCategory">
        <div class="modal-box">
            <div class="modal-header">
                <i class="fas fa-pen text-warning"></i> <span class="modal-title">Edit Kategori</span>
                <button class="btn-close-modal" onclick="this.closest('.modal-overlay').classList.remove('show')"><i class="fas fa-times"></i></button>
            </div>
            <form method="POST" id="formEditCategory">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="editCatName" class="form-control" required>
                    </div>
                    <div>
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description" id="editCatDesc" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modals for Unit --}}
    <div class="modal-overlay" id="modalAddUnit">
        <div class="modal-box">
            <div class="modal-header">
                <i class="fas fa-plus text-primary"></i> <span class="modal-title">Tambah Satuan</span>
                <button class="btn-close-modal" onclick="this.closest('.modal-overlay').classList.remove('show')"><i class="fas fa-times"></i></button>
            </div>
            <form method="POST" action="{{ route('units.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Satuan <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="mis. Kilogram" required>
                    </div>
                    <div>
                        <label class="form-label">Singkatan <span class="text-danger">*</span></label>
                        <input type="text" name="abbreviation" class="form-control" placeholder="mis. kg" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-overlay" id="modalEditUnit">
        <div class="modal-box">
            <div class="modal-header">
                <i class="fas fa-pen text-warning"></i> <span class="modal-title">Edit Satuan</span>
                <button class="btn-close-modal" onclick="this.closest('.modal-overlay').classList.remove('show')"><i class="fas fa-times"></i></button>
            </div>
            <form method="POST" id="formEditUnit">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Satuan <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="editUnitName" class="form-control" required>
                    </div>
                    <div>
                        <label class="form-label">Singkatan <span class="text-danger">*</span></label>
                        <input type="text" name="abbreviation" id="editUnitAbbr" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Hapus Kategori --}}
    <div class="modal-overlay" id="modalDeleteCategory">
        <div class="modal-box">
            <div class="modal-header" style="background:#fef2f2;border-bottom:1px solid #fee2e2;">
                <i class="fas fa-trash-can text-danger"></i> <span class="modal-title" style="color:#991b1b;">Hapus Kategori</span>
                <button class="btn-close-modal" onclick="this.closest('.modal-overlay').classList.remove('show')"><i class="fas fa-times"></i></button>
            </div>
            <form method="POST" id="formDeleteCategory">
                @csrf @method('DELETE')
                <div class="modal-body">
                    <div style="margin-bottom:14px;padding:12px;border-radius:8px;background:#f8fafc;border:1px solid #e2e8f0;">
                        <div style="font-size:11px;color:#64748b;text-transform:uppercase;font-weight:700;">Kategori yang Dipilih</div>
                        <div id="delCatName" style="font-size:15px;font-weight:700;color:#0f172a;margin-top:2px;">-</div>
                        <div id="delCatCount" style="margin-top:4px;font-size:12px;color:#64748b;">-</div>
                    </div>

                    <div id="delEmptyNotice" style="display:none;">
                        <p style="font-size:13px;color:#475569;margin:0;">Kategori ini tidak memiliki material atau alat terkait dan dapat langsung dihapus.</p>
                    </div>

                    <div id="delHasItemsNotice" style="display:none;">
                        <div class="alert alert-warning mb-3" style="font-size:12.5px;">
                            <i class="fas fa-triangle-exclamation"></i>
                            <div>Kategori ini masih memiliki item di dalamnya. Pilih kategori tujuan untuk memindahkan seluruh item sebelum kategori dihapus:</div>
                        </div>

                        <div>
                            <label class="form-label">Pindahkan Item ke Kategori:</label>
                            <select name="transfer_to_category_id" id="delTargetCatSelect" class="form-control">
                                <option value="">— Pilih Kategori Tujuan —</option>
                                @foreach($categories as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="this.closest('.modal-overlay').classList.remove('show')">Batal</button>
                    <button type="submit" id="delSubmitBtn" class="btn btn-danger">Hapus Kategori</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function editCategory(id, name, desc) {
            document.getElementById('formEditCategory').action = `/categories/${id}`;
            document.getElementById('editCatName').value = name;
            document.getElementById('editCatDesc').value = desc;
            document.getElementById('modalEditCategory').classList.add('show');
        }
        function editUnit(id, name, abbr) {
            document.getElementById('formEditUnit').action = `/units/${id}`;
            document.getElementById('editUnitName').value = name;
            document.getElementById('editUnitAbbr').value = abbr;
            document.getElementById('modalEditUnit').classList.add('show');
        }
        function openDeleteCategory(id, name, totalItems) {
            var modal = document.getElementById('modalDeleteCategory');
            document.getElementById('formDeleteCategory').action = `/categories/${id}`;
            document.getElementById('delCatName').textContent = name;
            document.getElementById('delCatCount').textContent = 'Total: ' + totalItems + ' item (Material & Alat)';

            var emptyNotice = document.getElementById('delEmptyNotice');
            var hasNotice = document.getElementById('delHasItemsNotice');
            var targetSelect = document.getElementById('delTargetCatSelect');
            var submitBtn = document.getElementById('delSubmitBtn');

            if (targetSelect) {
                targetSelect.value = '';
                Array.from(targetSelect.options).forEach(function(opt) {
                    if (opt.value == id) {
                        opt.style.display = 'none';
                        opt.disabled = true;
                    } else {
                        opt.style.display = '';
                        opt.disabled = false;
                    }
                });
            }

            if (totalItems > 0) {
                emptyNotice.style.display = 'none';
                hasNotice.style.display = 'block';
                if (targetSelect) targetSelect.required = true;
                submitBtn.textContent = 'Pindahkan Item & Hapus';
            } else {
                emptyNotice.style.display = 'block';
                hasNotice.style.display = 'none';
                if (targetSelect) targetSelect.required = false;
                submitBtn.textContent = 'Ya, Hapus Kategori';
            }

            modal.classList.add('show');
        }
    </script>
    @endpush
</x-app-layout>
