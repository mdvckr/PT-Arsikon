<x-app-layout>
    <x-slot name="title">Data Alat</x-slot>

    <div class="flex items-center justify-between mb-4">
        <div>
            <h2 class="fw-700" style="font-size:20px;color:#0f172a;">Data Inventaris Alat</h2>
            <p class="text-muted" style="font-size:13px;margin-top:2px;">Kelola inventaris alat kerja dan stok pemakaian per kategori</p>
        </div>
    </div>

    {{-- Filter --}}
    <div class="card mb-4">
        <div class="card-body" style="padding:16px 20px;">
            <form method="GET" class="flex gap-3" style="flex-wrap:wrap;align-items:flex-end;">
                <div style="flex:1;min-width:200px;">
                    <label class="form-label">Cari Alat</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                        placeholder="Nama, kode alat, merk, kelompok, atau ukuran...">
                </div>
                <div style="min-width:180px;">
                    <label class="form-label">Kategori</label>
                    <select name="category_id" class="form-control" onchange="this.form.submit()">
                        <option value="">Semua Kategori</option>
                        @foreach($filterCategories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                 @can('create tools')
        <a href="{{ route('tools.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Alat
        </a>
        @endcan
            </form>
        </div>
    </div>

    {{-- Grouped Accordion Table --}}
    <div class="card">
        <div class="table-wrap">
            <table class="data-table mb-0">
                <thead>
                    <tr style="background:#f8fafc;border-bottom:1px solid #e2e8f0;">
                        <th style="width:130px;white-space:nowrap;padding:11px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#64748b;">Kode Alat</th>
                        <th style="padding:11px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#64748b;">Nama Alat & Model</th>
                        <th style="padding:11px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#64748b;">Merk & Spesifikasi</th>
                        <th style="padding:11px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#64748b;">Keterangan</th>
                        <th style="white-space:nowrap;padding:11px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#64748b;">Tgl Input</th>
                        <th style="text-align:center;white-space:nowrap;padding:11px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#64748b;">Total Stock</th>
                        <th style="text-align:center;white-space:nowrap;padding:11px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#64748b;">Dipinjam</th>
                        <th style="text-align:center;white-space:nowrap;padding:11px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#64748b;">Stock Sisa</th>
                        <th style="text-align:center;width:95px;white-space:nowrap;padding:11px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#64748b;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @php $dataToIterate = $categoriesData ?? $categories; @endphp
                    @forelse($dataToIterate as $category)
                    @php
                        // Group tools by type (kelompok barang)
                        $typeGroups = $category->tools->groupBy(function($t) {
                            if (!empty($t->type)) {
                                return $t->type;
                            }
                            if (!empty($t->size) && str_ends_with($t->name, $t->size)) {
                                $inferred = trim(substr($t->name, 0, -strlen($t->size)));
                                if (!empty($inferred)) return $inferred;
                            }
                            return $t->category?->name ?? 'Lainnya';
                        });
                        $totalTools = $category->tools->count();
                        $totalGroups = $typeGroups->count();
                    @endphp
                    {{-- Level 1: Category Header --}}
                    <tr class="group-toggle {{ !request('search') ? 'collapsed' : '' }}" data-group="group-cat-{{ $category->id }}" style="background:#f8fafc !important;cursor:pointer;user-select:none;border-top:1px solid #e2e8f0;border-bottom:1px solid #e2e8f0;">
                        <td colspan="9" style="padding:8px 14px !important;">
                            <div class="flex items-center justify-between" style="gap:12px;flex-wrap:nowrap;">
                                <div class="flex items-center" style="gap:8px;min-width:0;">
                                    <i class="fas fa-chevron-down group-chev" style="font-size:9.5px;color:#64748b;transition:transform .2s;{{ !request('search') ? 'transform:rotate(-90deg);' : '' }}" aria-hidden="true"></i>
                                    <span class="fw-700" style="font-size:12.5px;text-transform:uppercase;letter-spacing:.03em;color:#1e293b;white-space:nowrap;">
                                        {{ $category->name }}
                                    </span>
                                    <span style="font-size:11.5px;color:#64748b;font-weight:500;">
                                        ({{ $totalTools }} alat)
                                    </span>
                                </div>
                                <div class="flex items-center" style="gap:6px;flex-shrink:0;">
                                    @can('create tools')
                                    <a href="{{ route('tools.create', ['category_id' => $category->id]) }}" style="display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:5px;font-size:11.5px;font-weight:600;background:#ffffff;border:1px solid #cbd5e1;color:#2563eb;text-decoration:none;box-shadow:0 1px 2px rgba(0,0,0,0.04);" title="Tambah Alat pada {{ $category->name }}" onclick="event.stopPropagation();">
                                        <i class="fas fa-plus" style="font-size:9.5px;"></i> Tambah
                                    </a>
                                    @endcan
                                    @if(auth()->user()->can('delete categories') || auth()->user()->hasAnyRole(['Owner', 'Admin Pusat', 'Admin', 'Admin Gudang Pusat']))
                                    <button type="button" 
                                        onclick="event.stopPropagation(); openDeleteCategoryModal({{ $category->id }}, '{{ addslashes($category->name) }}', {{ $totalTools }})" 
                                        style="display:inline-flex;align-items:center;gap:4px;padding:3px 8px;border-radius:5px;font-size:11.5px;font-weight:600;background:#ffffff;border:1px solid #fecaca;color:#dc2626;box-shadow:0 1px 2px rgba(0,0,0,0.04);cursor:pointer;" 
                                        title="Hapus Kategori {{ $category->name }} jika salah memasukkan">
                                        <i class="fas fa-trash-can" style="font-size:10px;"></i> Hapus
                                    </button>
                                    @endif
                                </div>
                            </div>
                        </td>
                    </tr>

                    @foreach($typeGroups as $toolTypeName => $toolsInType)
                    @php $subKey = 'sub-' . $category->id . '-' . Str::slug($toolTypeName); @endphp
                    {{-- Level 2: Sub-Group Header (Kelompok Alat / Type) --}}
                    <tr class="group-rows group-cat-{{ $category->id }} subgroup-toggle {{ !request('search') ? 'collapsed' : '' }}" data-group="{{ $subKey }}" style="background:#fafbfc !important;cursor:pointer;user-select:none;border-bottom:1px solid #f1f5f9;border-left:3px solid #cbd5e1;{{ !request('search') ? 'display:none;' : '' }}">
                        <td colspan="9" style="padding:6px 14px 6px 28px !important;">
                            <div class="flex items-center justify-between" style="gap:8px;">
                                <div class="flex items-center" style="gap:7px;">
                                    <i class="fas fa-chevron-down subgroup-chev" style="font-size:8.5px;color:#94a3b8;transition:transform .2s;{{ !request('search') ? 'transform:rotate(-90deg);' : '' }}" aria-hidden="true"></i>
                                    <span class="fw-600" style="font-size:12px;color:#334155;">
                                        {{ $toolTypeName }}
                                    </span>
                                    <span style="font-size:11px;color:#94a3b8;">
                                        ({{ $toolsInType->count() }})
                                    </span>
                                </div>
                                <div class="flex items-center" style="gap:10px;">
                                    @if(auth()->user()->can('delete tools') || auth()->user()->hasAnyRole(['Owner', 'Admin Pusat', 'Admin', 'Admin Gudang Pusat']))
                                    <button type="button" 
                                        onclick="event.stopPropagation(); openDeleteGroupModal({{ $category->id }}, '{{ addslashes($category->name) }}', '{{ addslashes($toolTypeName) }}', {{ $toolsInType->count() }})" 
                                        style="display:inline-flex;align-items:center;gap:3px;padding:2px 7px;border-radius:4px;font-size:11px;font-weight:600;background:#ffffff;border:1px solid #fecaca;color:#dc2626;cursor:pointer;" 
                                        title="Hapus kelompok {{ $toolTypeName }} jika salah memasukkan">
                                        <i class="fas fa-trash-can" style="font-size:9.5px;"></i> Hapus Kelompok
                                    </button>
                                    @endif
                                </div>
                            </div>
                        </td>
                    </tr>

                    {{-- Level 3: Individual Tool Rows --}}
                    @foreach($toolsInType as $tool)
                    <tr class="group-rows group-cat-{{ $category->id }} subgroup-rows {{ $subKey }}" style="border-bottom:1px solid #f1f5f9;{{ !request('search') ? 'display:none;' : '' }}">
                        <td style="width:130px;white-space:nowrap;padding:9px 14px;vertical-align:middle;">
                            <span style="font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,monospace;font-size:12px;font-weight:600;color:#334155;letter-spacing:0.02em;">
                                {{ $tool->code ?? '-' }}
                            </span>
                        </td>
                        <td style="padding:9px 14px;vertical-align:middle;">
                            <a href="{{ route('tools.show', $tool) }}" class="fw-600" style="color:#0f172a;font-size:13px;text-decoration:none;display:inline-block;line-height:1.35;">
                                {{ $tool->name }}
                            </a>
                            @if($tool->type)
                            <div style="font-size:11.5px;color:#64748b;margin-top:2px;">{{ $tool->type }}</div>
                            @endif
                        </td>
                        <td style="padding:9px 14px;font-size:12.5px;color:#334155;vertical-align:middle;">
                            @if($tool->brand && $tool->size)
                                <span class="fw-600">{{ $tool->brand }}</span> <span class="text-muted" style="font-size:11.5px;">· {{ $tool->size }}</span>
                            @elseif($tool->brand)
                                <span class="fw-600">{{ $tool->brand }}</span>
                            @elseif($tool->size)
                                <span>{{ $tool->size }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td style="padding:9px 14px;font-size:12px;color:#475569;line-height:1.4;max-width:240px;vertical-align:middle;">
                            {{ $tool->notes ?: '-' }}
                        </td>
                        <td style="padding:9px 14px;font-size:12px;color:#64748b;white-space:nowrap;vertical-align:middle;">
                            {{ $tool->created_at ? $tool->created_at->format('d/m/Y') : '-' }}
                        </td>
                        <td style="text-align:center;padding:9px 14px;vertical-align:middle;white-space:nowrap;">
                            <span class="fw-700" style="font-size:13px;color:#0f172a;font-variant-numeric:tabular-nums;">{{ number_format($tool->stock_total, 0, ',', '.') }}</span>
                            <span class="text-muted" style="font-size:11px;"> unit</span>
                        </td>
                        <td style="text-align:center;padding:9px 14px;vertical-align:middle;white-space:nowrap;">
                            <span style="font-size:12.5px;color:#64748b;font-variant-numeric:tabular-nums;">{{ number_format($tool->stock_borrowed, 0, ',', '.') }}</span>
                            <span class="text-muted" style="font-size:11px;"> unit</span>
                        </td>
                        <td style="text-align:center;padding:9px 14px;vertical-align:middle;white-space:nowrap;">
                            <div class="fw-700" style="font-size:13px;color:#0f172a;font-variant-numeric:tabular-nums;">
                                {{ number_format($tool->stock_available, 0, ',', '.') }} <span class="text-muted" style="font-size:11px;font-weight:normal;">unit</span>
                            </div>
                            @if($tool->stock_available > 0)
                                <span class="badge" style="background:#f8fafc;color:#475569;border:1px solid #e2e8f0;font-size:10px;padding:2px 7px;border-radius:4px;font-weight:600;margin-top:2px;display:inline-block;">Tersedia</span>
                            @else
                                <span class="badge" style="background:#fef2f2;color:#991b1b;border:1px solid #fecaca;font-size:10px;padding:2px 7px;border-radius:4px;font-weight:600;margin-top:2px;display:inline-block;">Habis</span>
                            @endif
                        </td>
                        <td style="text-align:center;padding:9px 14px;vertical-align:middle;white-space:nowrap;">
                            <div class="flex items-center justify-center" style="gap:4px;">
                                <a href="{{ route('tools.show', $tool) }}" class="btn btn-sm btn-light border" style="width:28px;height:28px;padding:0;display:inline-flex;align-items:center;justify-content:center;color:#475569;border-radius:5px;background:#f8fafc;" title="Detail Alat">
                                    <i class="fas fa-eye" style="font-size:11px;"></i>
                                </a>
                                @can('edit tools')
                                <a href="{{ route('tools.edit', $tool) }}" class="btn btn-sm btn-light border" style="width:28px;height:28px;padding:0;display:inline-flex;align-items:center;justify-content:center;color:#475569;border-radius:5px;background:#f8fafc;" title="Edit Alat">
                                    <i class="fas fa-pen" style="font-size:11px;"></i>
                                </a>
                                @endcan
                                @can('delete tools')
                                <form method="POST" action="{{ route('tools.destroy', $tool) }}"
                                    onsubmit="return confirm('Hapus alat {{ addslashes($tool->name) }}?')" style="display:inline-block;margin:0;">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-light border" style="width:28px;height:28px;padding:0;display:inline-flex;align-items:center;justify-content:center;color:#dc2626;border-radius:5px;background:#f8fafc;" title="Hapus Alat">
                                        <i class="fas fa-trash" style="font-size:11px;"></i>
                                    </button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @endforeach
                    @endforeach
                    @empty
                    <tr>
                        <td colspan="9">
                            <div class="empty-state">
                                <i class="fas fa-tools"></i>
                                <h3>Belum Ada Alat</h3>
                                <p>Tambahkan alat kerja pertama untuk memulai inventaris.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal Hapus Kategori Alat --}}
    <div id="modalDeleteCategory" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.55);backdrop-filter:blur(3px);z-index:9999;align-items:center;justify-content:center;padding:16px;">
        <div style="background:#fff;border-radius:12px;width:100%;max-width:480px;box-shadow:0 20px 25px -5px rgba(0,0,0,0.1),0 8px 10px -6px rgba(0,0,0,0.1);overflow:hidden;">
            <form id="formDeleteCategory" method="POST" action="">
                @csrf
                @method('DELETE')
                
                {{-- Modal Header --}}
                <div style="padding:16px 20px;border-bottom:1px solid #fee2e2;background:#fef2f2;display:flex;align-items:center;justify-content:space-between;">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div style="width:36px;height:36px;border-radius:50%;background:#fee2e2;color:#dc2626;display:flex;align-items:center;justify-content:center;font-size:15px;flex-shrink:0;">
                            <i class="fas fa-trash-can"></i>
                        </div>
                        <div>
                            <div class="fw-700" style="font-size:15px;color:#991b1b;">Hapus Kategori Alat</div>
                            <div class="text-muted" style="font-size:11.5px;">Hapus atau bersihkan kategori yang salah dimasukkan</div>
                        </div>
                    </div>
                    <button type="button" onclick="closeDeleteCategoryModal()" style="background:none;border:none;color:#94a3b8;font-size:18px;cursor:pointer;line-height:1;padding:4px;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                {{-- Modal Body --}}
                <div style="padding:18px 20px;">
                    <div style="margin-bottom:14px;padding:12px 14px;border-radius:8px;background:#f8fafc;border:1px solid #e2e8f0;">
                        <div style="font-size:11px;color:#64748b;text-transform:uppercase;font-weight:700;letter-spacing:0.5px;">Kategori yang Dipilih</div>
                        <div id="delCatName" style="font-size:15px;font-weight:700;color:#0f172a;margin-top:2px;">-</div>
                        <div id="delCatCountBadge" style="margin-top:6px;font-size:12px;color:#475569;">
                            <span class="badge badge-gray" id="delCatCountText">0 Alat</span>
                        </div>
                    </div>

                    {{-- Case 1: Kategori Kosong --}}
                    <div id="delEmptySection" style="display:none;">
                        <p style="font-size:13px;color:#475569;margin:0;line-height:1.5;">
                            Kategori ini belum memiliki alat kerja di dalamnya. Anda dapat langsung menghapusnya tanpa mempengaruhi data inventaris lainnya.
                        </p>
                    </div>

                    {{-- Case 2: Kategori ada isinya --}}
                    <div id="delHasItemsSection" style="display:none;">
                        <div style="padding:10px 12px;border-radius:6px;background:#fffbeb;border:1px solid #fef3c7;color:#92400e;font-size:12px;line-height:1.45;margin-bottom:14px;">
                            <i class="fas fa-triangle-exclamation me-1" style="color:#d97706;"></i>
                            Kategori ini masih memiliki <strong id="delCountHighlight">0</strong> alat kerja. Agar data inventaris tetap utuh, pilih kategori pengganti untuk memindahkan seluruh alat:
                        </div>

                        <div style="margin-bottom:12px;">
                            <label class="form-label" style="font-size:12px;font-weight:700;color:#334155;">Pindahkan Seluruh Alat ke Kategori:</label>
                            <select name="transfer_to_category_id" id="delTargetCatSelect" class="form-control" style="height:38px;border-radius:6px;font-size:13px;">
                                <option value="">— Pilih Kategori Tujuan —</option>
                                @foreach($filterCategories as $fCat)
                                <option value="{{ $fCat->id }}">{{ $fCat->name }}</option>
                                @endforeach
                            </select>
                            <div class="text-muted" style="font-size:11px;margin-top:4px;">Seluruh alat akan dialihkan ke kategori ini, lalu kategori yang salah akan dihapus.</div>
                        </div>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div style="background:#f8fafc;padding:12px 20px;border-top:1px solid #e2e8f0;display:flex;justify-content:flex-end;gap:8px;">
                    <button type="button" onclick="closeDeleteCategoryModal()" class="btn btn-secondary" style="height:36px;padding:0 14px;border-radius:6px;font-size:12.5px;font-weight:600;">
                        Batal
                    </button>
                    <button type="submit" id="delSubmitBtn" class="btn btn-danger" style="height:36px;padding:0 16px;border-radius:6px;font-size:12.5px;font-weight:600;display:inline-flex;align-items:center;gap:6px;">
                        <i class="fas fa-trash-can"></i> Hapus Kategori
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Hapus Kelompok Alat (Type) --}}
    <div id="modalDeleteGroup" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.55);backdrop-filter:blur(3px);z-index:9999;align-items:center;justify-content:center;padding:16px;">
        <div style="background:#fff;border-radius:12px;width:100%;max-width:480px;box-shadow:0 20px 25px -5px rgba(0,0,0,0.1),0 8px 10px -6px rgba(0,0,0,0.1);overflow:hidden;">
            <form id="formDeleteGroup" method="POST" action="{{ route('tools.delete-group') }}">
                @csrf
                <input type="hidden" name="category_id" id="groupDelCatId">
                <input type="hidden" name="type_name" id="groupDelTypeName">
                
                {{-- Modal Header --}}
                <div style="padding:16px 20px;border-bottom:1px solid #fee2e2;background:#fef2f2;display:flex;align-items:center;justify-content:space-between;">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div style="width:36px;height:36px;border-radius:50%;background:#fee2e2;color:#dc2626;display:flex;align-items:center;justify-content:center;font-size:15px;flex-shrink:0;">
                            <i class="fas fa-trash-can"></i>
                        </div>
                        <div>
                            <div class="fw-700" style="font-size:15px;color:#991b1b;">Hapus Kelompok Alat</div>
                            <div class="text-muted" style="font-size:11.5px;">Hapus atau bersihkan kelompok yang salah dimasukkan</div>
                        </div>
                    </div>
                    <button type="button" onclick="closeDeleteGroupModal()" style="background:none;border:none;color:#94a3b8;font-size:18px;cursor:pointer;line-height:1;padding:4px;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                {{-- Modal Body --}}
                <div style="padding:18px 20px;">
                    <div style="margin-bottom:14px;padding:12px 14px;border-radius:8px;background:#f8fafc;border:1px solid #e2e8f0;">
                        <div style="font-size:11px;color:#64748b;text-transform:uppercase;font-weight:700;letter-spacing:0.5px;">Kategori: <span id="groupDelCatDisplay" style="color:#0f172a;">-</span></div>
                        <div style="font-size:16px;font-weight:700;color:#0f172a;margin-top:4px;" id="groupDelTypeDisplay">-</div>
                        <div style="margin-top:6px;font-size:12px;color:#475569;">
                            <span class="badge badge-gray" id="groupDelCountText">0 Alat</span>
                        </div>
                    </div>

                    <div style="margin-bottom:14px;">
                        <label class="form-label" style="font-size:12px;font-weight:700;color:#334155;margin-bottom:8px;">PILIH METODE PENGHAPUSAN:</label>
                        
                        <div style="display:flex;flex-direction:column;gap:10px;">
                            {{-- Option 1: Pindahkan ke kelompok lain --}}
                            <label style="display:flex;align-items:flex-start;gap:10px;padding:10px 12px;border:1px solid #cbd5e1;border-radius:8px;cursor:pointer;background:#fff;" id="labelOptTransfer">
                                <input type="radio" name="action_type" value="transfer" checked onchange="toggleGroupAction(this.value)" style="margin-top:3px;">
                                <div style="flex:1;">
                                    <div style="font-size:13px;font-weight:700;color:#0f172a;">Pindahkan ke Kelompok Lain (Direkomendasikan)</div>
                                    <div style="font-size:11.5px;color:#64748b;margin-top:2px;">Ubah nama kelompok untuk seluruh alat ini ke kelompok lain (misal jika typo atau salah ketik).</div>
                                    
                                    <div id="targetTypeInputWrap" style="margin-top:10px;">
                                        <input type="text" name="target_type" id="groupTargetTypeInput" class="form-control" placeholder="Contoh: Genset, Molen Beton, Lainnya..." style="height:36px;border-radius:6px;font-size:13px;">
                                    </div>
                                </div>
                            </label>

                            {{-- Option 2: Hapus alat di dalamnya --}}
                            <label style="display:flex;align-items:flex-start;gap:10px;padding:10px 12px;border:1px solid #cbd5e1;border-radius:8px;cursor:pointer;background:#fff;" id="labelOptDelete">
                                <input type="radio" name="action_type" value="delete_items" onchange="toggleGroupAction(this.value)" style="margin-top:3px;">
                                <div style="flex:1;">
                                    <div style="font-size:13px;font-weight:700;color:#dc2626;">Hapus Seluruh Alat di Kelompok Ini</div>
                                    <div style="font-size:11.5px;color:#64748b;margin-top:2px;">Hanya dapat dilakukan jika alat di kelompok ini tidak sedang dalam peminjaman aktif.</div>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div style="background:#f8fafc;padding:12px 20px;border-top:1px solid #e2e8f0;display:flex;justify-content:flex-end;gap:8px;">
                    <button type="button" onclick="closeDeleteGroupModal()" class="btn btn-secondary" style="height:36px;padding:0 14px;border-radius:6px;font-size:12.5px;font-weight:600;">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-danger" style="height:36px;padding:0 16px;border-radius:6px;font-size:12.5px;font-weight:600;display:inline-flex;align-items:center;gap:6px;">
                        <i class="fas fa-trash-can"></i> Proses Hapus Kelompok
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function openDeleteCategoryModal(catId, catName, itemCount) {
            var modal = document.getElementById('modalDeleteCategory');
            var form = document.getElementById('formDeleteCategory');
            var nameEl = document.getElementById('delCatName');
            var countText = document.getElementById('delCatCountText');
            var emptySec = document.getElementById('delEmptySection');
            var hasItemsSec = document.getElementById('delHasItemsSection');
            var countHigh = document.getElementById('delCountHighlight');
            var targetSelect = document.getElementById('delTargetCatSelect');
            var submitBtn = document.getElementById('delSubmitBtn');

            form.action = "{{ url('/categories') }}/" + catId;
            nameEl.textContent = catName;
            countText.textContent = itemCount + ' Alat';

            if (targetSelect) {
                targetSelect.value = '';
                Array.from(targetSelect.options).forEach(function(opt) {
                    if (opt.value == catId) {
                        opt.style.display = 'none';
                        opt.disabled = true;
                    } else {
                        opt.style.display = '';
                        opt.disabled = false;
                    }
                });
            }

            if (itemCount > 0) {
                emptySec.style.display = 'none';
                hasItemsSec.style.display = 'block';
                countHigh.textContent = itemCount;
                submitBtn.innerHTML = '<i class="fas fa-right-left"></i> Pindahkan & Hapus';
                if (targetSelect) targetSelect.required = true;
            } else {
                emptySec.style.display = 'block';
                hasItemsSec.style.display = 'none';
                submitBtn.innerHTML = '<i class="fas fa-trash-can"></i> Ya, Hapus Kategori';
                if (targetSelect) targetSelect.required = false;
            }

            modal.style.display = 'flex';
        }

        function closeDeleteCategoryModal() {
            var modal = document.getElementById('modalDeleteCategory');
            if (modal) modal.style.display = 'none';
        }

        function openDeleteGroupModal(catId, catName, typeName, itemCount) {
            var modal = document.getElementById('modalDeleteGroup');
            document.getElementById('groupDelCatId').value = catId;
            document.getElementById('groupDelTypeName').value = typeName;
            document.getElementById('groupDelCatDisplay').textContent = catName;
            document.getElementById('groupDelTypeDisplay').textContent = typeName;
            document.getElementById('groupDelCountText').textContent = itemCount + ' Alat';
            document.getElementById('groupTargetTypeInput').value = '';

            modal.style.display = 'flex';
        }

        function closeDeleteGroupModal() {
            var modal = document.getElementById('modalDeleteGroup');
            if (modal) modal.style.display = 'none';
        }

        function toggleGroupAction(val) {
            var wrap = document.getElementById('targetTypeInputWrap');
            if (wrap) {
                wrap.style.display = val === 'transfer' ? 'block' : 'none';
            }
        }

        function initToolAccordion() {
            var hasSearch = {{ request('search') ? 'true' : 'false' }};
            var catToggleRows = document.querySelectorAll('.group-toggle');
            var subToggleRows = document.querySelectorAll('.subgroup-toggle');

            // 1. Kondisi awal saat pertama kali dibuka: tertutup sendiri secara default jika tidak sedang mencari
            if (!hasSearch) {
                catToggleRows.forEach(function (catRow) {
                    catRow.classList.add('collapsed');
                    var chev = catRow.querySelector('.group-chev');
                    if (chev) chev.style.transform = 'rotate(-90deg)';
                });

                subToggleRows.forEach(function (subRow) {
                    subRow.classList.add('collapsed');
                    var chev = subRow.querySelector('.subgroup-chev');
                    if (chev) chev.style.transform = 'rotate(-90deg)';
                });

                document.querySelectorAll('.group-rows').forEach(function (r) {
                    r.style.display = 'none';
                });
            } else {
                subToggleRows.forEach(function (subRow) {
                    var subKey = subRow.getAttribute('data-group');
                    var childRows = document.querySelectorAll('.' + subKey);
                    childRows.forEach(function (r) {
                        r.style.display = '';
                    });
                });
            }

            // 2. Level 1: Category Toggle (Kategori lain otomatis tertutup sendiri saat pindah kategori)
            catToggleRows.forEach(function (row) {
                var newRow = row.cloneNode(true);
                row.parentNode.replaceChild(newRow, row);

                var group = newRow.getAttribute('data-group');
                newRow.addEventListener('click', function (e) {
                    if (e.target.closest('.btn') || e.target.closest('a') || e.target.closest('button')) return;

                    var willOpen = newRow.classList.contains('collapsed');

                    if (willOpen) {
                        // Tutup kategori lainnya
                        document.querySelectorAll('.group-toggle').forEach(function (otherCat) {
                            if (otherCat !== newRow && !otherCat.classList.contains('collapsed')) {
                                otherCat.classList.add('collapsed');
                                var otherGroup = otherCat.getAttribute('data-group');
                                document.querySelectorAll('.' + otherGroup).forEach(function (r) {
                                    r.style.display = 'none';
                                });
                                var otherChev = otherCat.querySelector('.group-chev');
                                if (otherChev) otherChev.style.transform = 'rotate(-90deg)';
                            }
                        });

                        // Buka kategori yang diklik
                        newRow.classList.remove('collapsed');
                        var chev = newRow.querySelector('.group-chev');
                        if (chev) chev.style.transform = '';

                        // Tampilkan subgroup-toggle miliknya (item di dalam sub-kelompok tetap tertutup sampai sub-kelompok diklik)
                        document.querySelectorAll('.' + group).forEach(function (r) {
                            if (r.classList.contains('subgroup-toggle')) {
                                r.style.display = '';
                            } else if (r.classList.contains('subgroup-rows')) {
                                var subKey = null;
                                r.classList.forEach(function (cls) {
                                    if (cls.startsWith('sub-')) subKey = cls;
                                });
                                var subToggle = subKey ? document.querySelector('.subgroup-toggle[data-group="' + subKey + '"]') : null;
                                if (subToggle && !subToggle.classList.contains('collapsed')) {
                                    r.style.display = '';
                                } else {
                                    r.style.display = 'none';
                                }
                            }
                        });
                    } else {
                        // Tutup kategori ini
                        newRow.classList.add('collapsed');
                        var chev = newRow.querySelector('.group-chev');
                        if (chev) chev.style.transform = 'rotate(-90deg)';
                        document.querySelectorAll('.' + group).forEach(function (r) {
                            r.style.display = 'none';
                        });
                    }
                });
            });

            // 3. Level 2: Subgroup Toggle (Kelompok lain dalam kategori yang sama otomatis tertutup sendiri saat pindah kelompok)
            subToggleRows = document.querySelectorAll('.subgroup-toggle');
            subToggleRows.forEach(function (row) {
                var newSubRow = row.cloneNode(true);
                row.parentNode.replaceChild(newSubRow, row);

                var subKey = newSubRow.getAttribute('data-group');
                newSubRow.addEventListener('click', function (e) {
                    if (e.target.closest('.btn') || e.target.closest('a') || e.target.closest('button')) return;

                    var willOpen = newSubRow.classList.contains('collapsed');

                    if (willOpen) {
                        // Cari kategori induk
                        var parentCatClass = null;
                        newSubRow.classList.forEach(function (cls) {
                            if (cls.startsWith('group-cat-')) parentCatClass = cls;
                        });

                        // Tutup sub-kelompok lain di kategori yang sama
                        if (parentCatClass) {
                            document.querySelectorAll('.' + parentCatClass + '.subgroup-toggle').forEach(function (otherSub) {
                                if (otherSub !== newSubRow && !otherSub.classList.contains('collapsed')) {
                                    otherSub.classList.add('collapsed');
                                    var otherSubKey = otherSub.getAttribute('data-group');
                                    document.querySelectorAll('.' + otherSubKey).forEach(function (r) {
                                        r.style.display = 'none';
                                    });
                                    var otherChev = otherSub.querySelector('.subgroup-chev');
                                    if (otherChev) otherChev.style.transform = 'rotate(-90deg)';
                                }
                            });
                        }

                        // Buka sub-kelompok yang diklik
                        newSubRow.classList.remove('collapsed');
                        var chev = newSubRow.querySelector('.subgroup-chev');
                        if (chev) chev.style.transform = '';
                        document.querySelectorAll('.' + subKey).forEach(function (r) {
                            r.style.display = '';
                        });
                    } else {
                        // Tutup sub-kelompok ini
                        newSubRow.classList.add('collapsed');
                        var chev = newSubRow.querySelector('.subgroup-chev');
                        if (chev) chev.style.transform = 'rotate(-90deg)';
                        document.querySelectorAll('.' + subKey).forEach(function (r) {
                            r.style.display = 'none';
                        });
                    }
                });
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initToolAccordion);
        } else {
            initToolAccordion();
        }
    </script>
    @endpush
</x-app-layout>