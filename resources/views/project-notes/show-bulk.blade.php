<x-app-layout>
    <x-slot name="title">{{ $bulkMaterial->title }} - Arsikon</x-slot>

    <div x-data="{
        showAddItemModal: false,
        showEditBulkModal: false,
        showEditItemModal: false,
        editItem: {
            id: null,
            delivery_date: '',
            ticket_number: '',
            quantity: 1,
            unit: 'Rit',
            material_type: 'Pasir',
            unit_price: '',
            notes: ''
        },
        openEditItem(item) {
            this.editItem = {
                id: item.id,
                delivery_date: item.delivery_date ? item.delivery_date.substring(0, 10) : '',
                ticket_number: item.ticket_number || '',
                quantity: item.quantity,
                unit: item.unit || 'Rit',
                material_type: item.material_type || 'Pasir',
                unit_price: item.unit_price > 0 ? item.unit_price : '',
                notes: item.notes || ''
            };
            this.showEditItemModal = true;
        }
    }" class="space-y-4" data-animate="panel">

        {{-- 1. Top Breadcrumb & Quick Nav --}}
        <div class="flex items-center justify-between gap-3 text-xs flex-wrap">
            <div class="flex items-center gap-2">
                <a href="{{ route('project-notes.index', ['project_id' => $bulkMaterial->project_id]) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 font-semibold transition">
                    <x-lucide-arrow-left class="w-3.5 h-3.5 text-slate-400" />
                    <span>Kembali ke Buku Catatan Proyek</span>
                </a>
            </div>
            <div class="text-[11px] text-slate-400 font-mono">
                ID Rekap: #MC-{{ $bulkMaterial->id }}
            </div>
        </div>

        {{-- Alerts --}}
        @if(session('success'))
            <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center justify-between">
                <span class="flex items-center gap-2"><x-lucide-circle-check class="w-4 h-4 text-emerald-600" /> {{ session('success') }}</span>
            </div>
        @endif
        @if(session('error'))
            <div class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold flex items-center justify-between">
                <span class="flex items-center gap-2"><x-lucide-circle-alert class="w-4 h-4 text-rose-600" /> {{ session('error') }}</span>
            </div>
        @endif

        {{-- 2. Document Header & Compact Summary Strip --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 p-5 shadow-xs">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2 text-xs font-medium text-slate-500">
                        <span class="font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider text-[11px]">Material Curah Lapangan</span>
                        <span class="text-slate-300 dark:text-slate-700">/</span>
                        <span class="font-bold text-blue-700 dark:text-blue-400">{{ $bulkMaterial->project?->name ?? ($bulkMaterial->project_name ?? 'Proyek') }}</span>
                    </div>
                    <h1 class="text-lg sm:text-xl font-extrabold text-slate-900 dark:text-white tracking-tight mt-1" data-animate="header">
                        {{ $bulkMaterial->title }}
                    </h1>
                    <div class="flex items-center gap-3 mt-2 text-xs text-slate-600 dark:text-slate-400 flex-wrap">
                        <div class="flex items-center gap-1.5">
                            <span class="text-slate-400 font-medium">Suplier:</span>
                            <span class="font-bold text-slate-900 dark:text-white">{{ $bulkMaterial->supplier_name }}</span>
                        </div>
                        <span class="text-slate-300 dark:text-slate-700">&bull;</span>
                        <div class="flex items-center gap-1.5">
                            <span class="text-slate-400 font-medium">Periode:</span>
                            <span class="font-semibold text-slate-800 dark:text-slate-200">
                                {{ $bulkMaterial->start_date ? $bulkMaterial->start_date->translatedFormat('d M Y') : '-' }}
                                @if($bulkMaterial->end_date && $bulkMaterial->end_date != $bulkMaterial->start_date)
                                    – {{ $bulkMaterial->end_date->translatedFormat('d M Y') }}
                                @endif
                            </span>
                        </div>
                        @if($bulkMaterial->notes)
                            <span class="text-slate-300 dark:text-slate-700">&bull;</span>
                            <div class="text-slate-500 italic max-w-md truncate" title="{{ $bulkMaterial->notes }}">
                                {{ $bulkMaterial->notes }}
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center gap-2 flex-wrap shrink-0">
                    <button type="button" @click="showEditBulkModal = true"
                        class="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 text-slate-700 dark:text-slate-200 text-xs font-semibold inline-flex items-center gap-1.5 transition">
                        <x-lucide-pencil class="w-3.5 h-3.5 text-slate-400" />
                        <span>Edit</span>
                    </button>
                    <a href="{{ route('project-notes.bulk.print', $bulkMaterial) }}" target="_blank"
                        class="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 text-slate-700 dark:text-slate-200 text-xs font-semibold inline-flex items-center gap-1.5 transition">
                        <x-lucide-printer class="w-3.5 h-3.5 text-slate-400" />
                        <span>Cetak A4</span>
                    </a>
                    <a href="{{ route('project-notes.bulk.export', $bulkMaterial) }}"
                        class="px-3 py-1.5 rounded-lg border border-emerald-200 bg-emerald-50 hover:bg-emerald-100/70 text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800 text-xs font-semibold inline-flex items-center gap-1.5 transition">
                        <x-lucide-file-spreadsheet class="w-3.5 h-3.5 text-emerald-600" />
                        <span>Export Excel</span>
                    </a>
                    <button type="button" @click="showAddItemModal = true"
                        class="px-3.5 py-1.5 rounded-lg bg-slate-900 hover:bg-slate-800 dark:bg-white dark:hover:bg-slate-100 text-white dark:text-slate-900 text-xs font-bold inline-flex items-center gap-1.5 transition shadow-xs">
                        <x-lucide-plus class="w-3.5 h-3.5" />
                        <span>+ Catat Ritase</span>
                    </button>
                </div>
            </div>

            {{-- Compact Summary Strip --}}
            <div class="mt-4 pt-3.5 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between flex-wrap gap-4 text-xs">
                <div class="flex items-center gap-4 flex-wrap">
                    <div class="flex items-baseline gap-1.5">
                        <span class="text-slate-400 font-medium">Total Ritase:</span>
                        <span class="text-base font-extrabold text-slate-900 dark:text-white tabular-nums">{{ number_format($bulkMaterial->total_rit, 0) }}</span>
                        <span class="text-slate-500 font-semibold">Rit</span>
                        <span class="text-[11px] text-slate-400">({{ $bulkMaterial->items->count() }} pengiriman)</span>
                    </div>
                    <div class="h-3 w-px bg-slate-200 dark:bg-slate-700 hidden sm:block"></div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-slate-400 font-medium">Rincian Jenis:</span>
                        @forelse($bulkMaterial->summary_by_type as $typeName => $info)
                            <span class="px-2.5 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 font-semibold text-[11px] border border-slate-200/60 dark:border-slate-700">
                                <strong>{{ $info['total_qty'] }}</strong> {{ $info['unit'] }} {{ $typeName }}
                            </span>
                        @empty
                            <span class="text-slate-400 text-xs italic">Belum ada baris ritase</span>
                        @endforelse
                    </div>
                </div>

                @if($bulkMaterial->total_amount > 0)
                <div class="flex items-baseline gap-1.5">
                    <span class="text-slate-400 font-medium">Total Nilai:</span>
                    <span class="font-extrabold text-slate-900 dark:text-white tabular-nums">Rp {{ number_format($bulkMaterial->total_amount, 0, ',', '.') }}</span>
                </div>
                @endif
            </div>
        </div>

        {{-- 3. Tabel Detail Ritase (Clean, High Precision, Zero Slop) --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 overflow-hidden shadow-xs">
            <div class="px-5 py-3 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between flex-wrap gap-2">
                <div class="flex items-center gap-2">
                    <h2 class="text-xs font-extrabold text-slate-900 dark:text-white uppercase tracking-wider">
                        Rincian Pengiriman Lapangan
                    </h2>
                    <span class="text-[11px] font-mono text-slate-400">({{ $bulkMaterial->items->count() }} baris)</span>
                </div>
                <button type="button" @click="showAddItemModal = true"
                    class="text-xs font-semibold text-slate-600 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white inline-flex items-center gap-1 transition">
                    <x-lucide-plus class="w-3.5 h-3.5 text-slate-400" />
                    <span>Tambah Baris</span>
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs border-collapse">
                    <thead>
                        <tr class="bg-slate-50/80 dark:bg-slate-800/60 border-b border-slate-200/90 dark:border-slate-700/80 text-[11px] font-bold text-slate-600 dark:text-slate-300">
                            <th class="py-2.5 px-3 text-center" style="width: 40px;">No</th>
                            <th class="py-2.5 px-4 text-start" style="width: 150px;">Tanggal</th>
                            <th class="py-2.5 px-3 text-start" style="width: 140px;">No. Nota / Tiket</th>
                            <th class="py-2.5 px-4 text-start">Jenis Material</th>
                            <th class="py-2.5 px-3 text-center" style="width: 90px;">Qty</th>
                            <th class="py-2.5 px-3 text-center" style="width: 70px;">Satuan</th>
                            <th class="py-2.5 px-3 text-end" style="width: 120px;">Harga Satuan</th>
                            <th class="py-2.5 px-3 text-end" style="width: 120px;">Subtotal</th>
                            <th class="py-2.5 px-4 text-start">Keterangan</th>
                            <th class="py-2.5 px-3 text-center" style="width: 80px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                        @forelse($bulkMaterial->items as $idx => $item)
                        <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition">
                            <td class="text-center font-mono text-[11px] text-slate-400 py-2.5 px-3">
                                {{ $idx + 1 }}
                            </td>
                            <td class="py-2.5 px-4 font-medium text-slate-800 dark:text-slate-200 whitespace-nowrap">
                                {{ $item->delivery_date ? $item->delivery_date->translatedFormat('d M Y') : '-' }}
                            </td>
                            <td class="py-2.5 px-3 font-mono text-slate-600 dark:text-slate-400">
                                {{ $item->ticket_number ?: '-' }}
                            </td>
                            <td class="py-2.5 px-4 font-bold text-slate-900 dark:text-white">
                                {{ $item->material_type }}
                            </td>
                            <td class="text-center py-2.5 px-3 font-extrabold text-slate-900 dark:text-white tabular-nums">
                                {{ (float)$item->quantity }}
                            </td>
                            <td class="text-center py-2.5 px-3 text-slate-500">
                                {{ $item->unit ?: 'Rit' }}
                            </td>
                            <td class="text-end py-2.5 px-3 text-slate-600 dark:text-slate-400 tabular-nums">
                                {{ $item->unit_price > 0 ? 'Rp ' . number_format($item->unit_price, 0, ',', '.') : '-' }}
                            </td>
                            <td class="text-end py-2.5 px-3 font-semibold text-slate-900 dark:text-white tabular-nums">
                                {{ $item->subtotal > 0 ? 'Rp ' . number_format($item->subtotal, 0, ',', '.') : '-' }}
                            </td>
                            <td class="py-2.5 px-4 text-slate-500 dark:text-slate-400">
                                {{ $item->notes ?: '-' }}
                            </td>
                            <td class="py-2.5 px-3 text-center">
                                <div class="inline-flex items-center gap-1">
                                    <button type="button" @click="openEditItem({{ json_encode($item) }})"
                                        class="p-1 rounded text-slate-400 hover:text-blue-600 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                                        title="Edit Baris">
                                        <x-lucide-pencil class="w-3.5 h-3.5" />
                                    </button>
                                    <form method="POST" action="{{ route('project-notes.bulk-items.destroy', $item) }}"
                                          onsubmit="return confirm('Hapus baris {{ $item->quantity }} {{ $item->unit }} {{ $item->material_type }}?')"
                                          class="m-0 p-0 inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1 rounded text-slate-400 hover:text-rose-600 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                                            title="Hapus Baris">
                                            <x-lucide-trash-2 class="w-3.5 h-3.5" />
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-8 text-slate-400">
                                <div class="font-medium text-slate-500">Belum ada data pengiriman material curah.</div>
                                <div class="text-[11px] mt-0.5">Klik <strong>+ Catat Ritase</strong> untuk memasukkan baris baru.</div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($bulkMaterial->items->isNotEmpty())
                    <tfoot>
                        <tr class="bg-slate-50/90 dark:bg-slate-800/80 font-bold border-t-2 border-slate-200 dark:border-slate-700 text-xs">
                            <td colspan="4" class="py-2.5 px-4 text-end uppercase text-[11px] text-slate-500 tracking-wider">
                                TOTAL KESELURUHAN:
                            </td>
                            <td class="text-center py-2.5 px-3 font-black text-slate-900 dark:text-white text-sm tabular-nums">
                                {{ number_format($bulkMaterial->total_rit, 0) }}
                            </td>
                            <td class="text-center py-2.5 px-3 text-slate-500 text-xs">Rit</td>
                            <td></td>
                            <td class="text-end py-2.5 px-3 font-black text-slate-900 dark:text-white tabular-nums">
                                {{ $bulkMaterial->total_amount > 0 ? 'Rp ' . number_format($bulkMaterial->total_amount, 0, ',', '.') : '-' }}
                            </td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- MODAL: TAMBAH ITEM RITASE                                                 --}}
        {{-- ========================================================================= --}}
        <div x-show="showAddItemModal" x-cloak class="fixed inset-0 flex items-center justify-center bg-slate-950/65 backdrop-blur-xs p-3 sm:p-4 overflow-y-auto" style="z-index: 9999;">
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-2xl w-full overflow-hidden my-auto flex flex-col" style="max-width: 480px;" @click.away="showAddItemModal = false">
                <div class="px-4 py-3 bg-slate-50/90 dark:bg-slate-800/80 border-b border-slate-200/80 dark:border-slate-800 flex items-center justify-between shrink-0">
                    <h3 class="text-xs font-extrabold text-slate-900 dark:text-white uppercase tracking-wider">
                        Catat Pengiriman Ritase
                    </h3>
                    <button type="button" @click="showAddItemModal = false" class="text-slate-400 hover:text-slate-600">
                        <x-lucide-x class="w-4 h-4" />
                    </button>
                </div>

                <form method="POST" action="{{ route('project-notes.bulk-items.store', $bulkMaterial) }}" class="flex flex-col m-0">
                    @csrf
                    <div class="p-4 space-y-3">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                    Tanggal Pengiriman <span class="text-rose-500">*</span>
                                </label>
                                <input type="date" name="delivery_date" value="{{ date('Y-m-d') }}" class="w-full text-xs h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800 font-medium" required>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                    No. Nota / Tiket
                                </label>
                                <input type="text" name="ticket_number" class="w-full text-xs h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800 font-medium" placeholder="Misal: T-27">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                    Qty <span class="text-rose-500">*</span>
                                </label>
                                <input type="number" step="0.01" name="quantity" value="1" min="0.01" class="w-full text-xs h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800 font-extrabold" required>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                    Satuan <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" name="unit" value="Rit" class="w-full text-xs h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800 font-medium" required>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                    Jenis Material <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" name="material_type" list="clean_material_types" class="w-full text-xs h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800 font-bold text-slate-900 dark:text-white" placeholder="Pasir / Batu" required>
                                <datalist id="clean_material_types">
                                    <option value="Pasir">
                                    <option value="Batu">
                                    <option value="Batu & 20 Liter">
                                    <option value="Sirtu">
                                    <option value="Tanah Urug">
                                    <option value="Base Course">
                                    <option value="Abu Batu">
                                </datalist>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                    Harga Satuan (Rp) <span class="text-[10px] text-slate-400 font-normal">(Opsional)</span>
                                </label>
                                <input type="number" step="0.01" name="unit_price" class="w-full text-xs h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800" placeholder="0">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                    Keterangan <span class="text-[10px] text-slate-400 font-normal">(Opsional)</span>
                                </label>
                                <input type="text" name="notes" class="w-full text-xs h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800" placeholder="Lokasi penumpukan atau peruntukan">
                            </div>
                        </div>
                    </div>

                    <div class="px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border-t border-slate-200/80 dark:border-slate-800 flex items-center justify-end gap-2 shrink-0">
                        <button type="button" @click="showAddItemModal = false" class="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-semibold">
                            Batal
                        </button>
                        <button type="submit" class="px-3.5 py-1.5 rounded-lg bg-slate-900 hover:bg-slate-800 dark:bg-white dark:hover:bg-slate-100 text-white dark:text-slate-900 text-xs font-bold inline-flex items-center gap-1.5">
                            <x-lucide-check class="w-3.5 h-3.5" />
                            <span>Simpan Baris</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- MODAL: EDIT ITEM RITASE                                                   --}}
        {{-- ========================================================================= --}}
        <div x-show="showEditItemModal" x-cloak class="fixed inset-0 flex items-center justify-center bg-slate-950/65 backdrop-blur-xs p-3 sm:p-4 overflow-y-auto" style="z-index: 9999;">
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-2xl w-full overflow-hidden my-auto flex flex-col" style="max-width: 480px;" @click.away="showEditItemModal = false">
                <div class="px-4 py-3 bg-slate-50/90 dark:bg-slate-800/80 border-b border-slate-200/80 dark:border-slate-800 flex items-center justify-between shrink-0">
                    <h3 class="text-xs font-extrabold text-slate-900 dark:text-white uppercase tracking-wider">
                        Edit Baris Ritase
                    </h3>
                    <button type="button" @click="showEditItemModal = false" class="text-slate-400 hover:text-slate-600">
                        <x-lucide-x class="w-4 h-4" />
                    </button>
                </div>

                <form method="POST" :action="`{{ url('/project-notes/bulk-items') }}/${editItem.id}`" class="flex flex-col m-0">
                    @csrf
                    @method('PUT')
                    <div class="p-4 space-y-3">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                    Tanggal Pengiriman <span class="text-rose-500">*</span>
                                </label>
                                <input type="date" name="delivery_date" x-model="editItem.delivery_date" class="w-full text-xs h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800 font-medium" required>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                    No. Nota / Tiket
                                </label>
                                <input type="text" name="ticket_number" x-model="editItem.ticket_number" class="w-full text-xs h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800 font-medium">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                    Qty <span class="text-rose-500">*</span>
                                </label>
                                <input type="number" step="0.01" name="quantity" x-model="editItem.quantity" min="0.01" class="w-full text-xs h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800 font-extrabold" required>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                    Satuan <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" name="unit" x-model="editItem.unit" class="w-full text-xs h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800 font-medium" required>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                    Jenis Material <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" name="material_type" x-model="editItem.material_type" list="clean_material_types" class="w-full text-xs h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800 font-bold text-slate-900 dark:text-white" required>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                    Harga Satuan (Rp)
                                </label>
                                <input type="number" step="0.01" name="unit_price" x-model="editItem.unit_price" class="w-full text-xs h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                    Keterangan
                                </label>
                                <input type="text" name="notes" x-model="editItem.notes" class="w-full text-xs h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800">
                            </div>
                        </div>
                    </div>

                    <div class="px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border-t border-slate-200/80 dark:border-slate-800 flex items-center justify-end gap-2 shrink-0">
                        <button type="button" @click="showEditItemModal = false" class="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-semibold">
                            Batal
                        </button>
                        <button type="submit" class="px-3.5 py-1.5 rounded-lg bg-slate-900 hover:bg-slate-800 dark:bg-white dark:hover:bg-slate-100 text-white dark:text-slate-900 text-xs font-bold inline-flex items-center gap-1.5">
                            <x-lucide-check class="w-3.5 h-3.5" />
                            <span>Perbarui Baris</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- MODAL: EDIT DATA REKAP                                                    --}}
        {{-- ========================================================================= --}}
        <div x-show="showEditBulkModal" x-cloak class="fixed inset-0 flex items-center justify-center bg-slate-950/65 backdrop-blur-xs p-3 sm:p-4 overflow-y-auto" style="z-index: 9999;">
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-2xl w-full overflow-hidden my-auto flex flex-col" style="max-width: 480px;" @click.away="showEditBulkModal = false">
                <div class="px-4 py-3 bg-slate-50/90 dark:bg-slate-800/80 border-b border-slate-200/80 dark:border-slate-800 flex items-center justify-between shrink-0">
                    <h3 class="text-xs font-extrabold text-slate-900 dark:text-white uppercase tracking-wider">
                        Edit Rekap Material Curah
                    </h3>
                    <button type="button" @click="showEditBulkModal = false" class="text-slate-400 hover:text-slate-600">
                        <x-lucide-x class="w-4 h-4" />
                    </button>
                </div>

                <form method="POST" action="{{ route('project-notes.bulk.update', $bulkMaterial) }}" class="flex flex-col m-0">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="status" value="{{ $bulkMaterial->status ?? 'active' }}">
                    
                    <div class="p-4 space-y-3">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                Judul Rekap <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="title" value="{{ $bulkMaterial->title }}" class="w-full text-xs h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800 font-medium" required>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                Suplier <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="supplier_name" value="{{ $bulkMaterial->supplier_name }}" class="w-full text-xs h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800 font-medium" required>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                    Tanggal Mulai <span class="text-rose-500">*</span>
                                </label>
                                <input type="date" name="start_date" value="{{ $bulkMaterial->start_date ? $bulkMaterial->start_date->format('Y-m-d') : date('Y-m-d') }}" class="w-full text-xs h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800" required>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                    Tanggal Selesai
                                </label>
                                <input type="date" name="end_date" value="{{ $bulkMaterial->end_date ? $bulkMaterial->end_date->format('Y-m-d') : '' }}" class="w-full text-xs h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800">
                            </div>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                Catatan
                            </label>
                            <input type="text" name="notes" value="{{ $bulkMaterial->notes }}" class="w-full text-xs h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800" placeholder="Keterangan peruntukan ritase...">
                        </div>
                    </div>
                    <div class="px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border-t border-slate-200/80 dark:border-slate-800 flex items-center justify-end gap-2 shrink-0">
                        <button type="button" @click="showEditBulkModal = false" class="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-semibold">
                            Batal
                        </button>
                        <button type="submit" class="px-3.5 py-1.5 rounded-lg bg-slate-900 hover:bg-slate-800 dark:bg-white dark:hover:bg-slate-100 text-white dark:text-slate-900 text-xs font-bold inline-flex items-center gap-1.5">
                            <x-lucide-check class="w-3.5 h-3.5" />
                            <span>Simpan Perubahan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
