<x-app-layout>
    <x-slot name="title">{{ $transfer->title }} - Pemindahan Barang - Arsikon</x-slot>

    <div x-data="{
        showAddItemModal: false,
        showEditTransferModal: false,
        showEditItemModal: false,
        editItem: {
            id: null,
            transfer_date: '',
            item_name: '',
            unit: 'Buah',
            quantity: 1,
            notes: ''
        },
        openEditItem(item) {
            this.editItem = {
                id: item.id,
                transfer_date: item.transfer_date ? item.transfer_date.substring(0, 10) : '',
                item_name: item.item_name,
                unit: item.unit,
                quantity: item.quantity,
                notes: item.notes || ''
            };
            this.showEditItemModal = true;
        }
    }" class="space-y-6" data-animate="panel">

        {{-- 1. Top Breadcrumb & Actions --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs">
            <div class="flex items-center gap-2 flex-wrap">
                <a href="{{ route('project-notes.index', ['project_id' => $transfer->project_id]) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-200 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 transition">
                    <x-lucide-arrow-left class="w-3.5 h-3.5" /> Kembali ke Buku Catatan Proyek
                </a>
                <span class="text-slate-300 dark:text-slate-700 hidden sm:inline">|</span>
                <span class="inline-flex items-center gap-1 text-xs font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 px-2.5 py-1 rounded-lg border border-emerald-200/60 dark:border-emerald-900">
                    <x-lucide-building-2 class="w-3.5 h-3.5" /> {{ $transfer->project?->name ?? ($transfer->project_name ?? 'Proyek') }}
                </span>
                <span class="inline-flex items-center gap-1 text-xs font-bold bg-teal-50 text-teal-800 dark:bg-teal-950/40 dark:text-teal-300 px-2.5 py-1 rounded-lg border border-teal-200/70 dark:border-teal-900">
                    <x-lucide-arrow-right-left class="w-3.5 h-3.5 text-teal-600" /> Mobilisasi Barang Antar Proyek
                </span>
            </div>

            <div class="flex items-center gap-2 flex-wrap">
                <button type="button" @click="showEditTransferModal = true"
                    class="btn btn-sm btn-light border inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1.5 rounded-xl text-slate-700 hover:bg-slate-100 transition shadow-2xs">
                    <x-lucide-pencil class="w-3.5 h-3.5 text-slate-500" /> Edit Surat Jalan
                </button>
                <button type="button" @click="window.print()"
                    class="btn btn-sm bg-slate-900 hover:bg-slate-800 text-white inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1.5 rounded-xl transition shadow-2xs">
                    <x-lucide-printer class="w-3.5 h-3.5 text-amber-400" /> Cetak Surat Jalan
                </button>
                <button type="button" @click="showAddItemModal = true"
                    class="btn btn-sm bg-teal-600 hover:bg-teal-700 text-white inline-flex items-center gap-1.5 text-xs font-bold px-3.5 py-1.5 rounded-xl shadow-xs transition">
                    <x-lucide-plus class="w-4 h-4" /> + Tambah Item Barang
                </button>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success p-3.5 rounded-xl bg-emerald-50 border border-emerald-300 text-emerald-800 text-xs font-semibold flex items-center justify-between">
                <span class="flex items-center gap-2"><x-lucide-circle-check class="w-4 h-4 text-emerald-600" /> {{ session('success') }}</span>
            </div>
        @endif

        {{-- 2. Header Ringkasan & Metric Cards --}}
        <div class="bg-white dark:bg-slate-900 p-5 rounded-3xl border border-slate-200/90 dark:border-slate-800 shadow-xs space-y-4">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 dark:border-slate-800 pb-4">
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="px-2.5 py-1 rounded-md text-[10px] font-extrabold bg-teal-600 text-white uppercase tracking-wider">
                            SURAT JALAN PEMINDAHAN BARANG
                        </span>
                        <span class="text-xs text-slate-400 font-mono">#SJ-{{ $transfer->id }}</span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white mt-1.5 tracking-tight" data-animate="header">
                        {{ $transfer->title }}
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 flex items-center gap-1.5 flex-wrap">
                        <span>Dari: <strong class="text-slate-800 dark:text-slate-200">{{ $transfer->origin_location ?: '-' }}</strong></span>
                        <span class="text-slate-300 dark:text-slate-700">&rarr;</span>
                        <span>Tujuan: <strong class="text-slate-800 dark:text-slate-200">{{ $transfer->destination_location ?: '-' }}</strong></span>
                        @if($transfer->docket_number)
                            <span class="text-slate-300 dark:text-slate-700">&bull;</span>
                            <span>No. SJ: <strong class="font-mono text-slate-800 dark:text-slate-200">{{ $transfer->docket_number }}</strong></span>
                        @endif
                        <span class="text-slate-300 dark:text-slate-700">&bull;</span>
                        <span>Tanggal: <strong>{{ $transfer->transfer_date ? $transfer->transfer_date->translatedFormat('d M Y') : '-' }}</strong></span>
                    </p>
                </div>
                <div class="flex items-center gap-2 self-start md:self-center">
                    <span class="px-3.5 py-1.5 rounded-xl text-xs font-extrabold border bg-teal-50 text-teal-800 border-teal-200 dark:bg-teal-950/40 dark:text-teal-300">
                        STATUS: {{ strtoupper($transfer->status ?: 'RECEIVED') }}
                    </span>
                </div>
            </div>

            {{-- 3 Metric Cards --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                <div class="p-4 rounded-2xl bg-teal-50/70 dark:bg-teal-950/30 border border-teal-200/70 dark:border-teal-900">
                    <span class="text-[11px] text-teal-700 dark:text-teal-300 block font-bold uppercase tracking-wider">Total Item Dimobilisasi</span>
                    <span class="text-xl font-black text-teal-900 dark:text-teal-200 mt-1 block tracking-tight">
                        {{ $transfer->items->count() }} Jenis Item
                    </span>
                </div>
                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700">
                    <span class="text-[11px] text-slate-500 dark:text-slate-400 block font-semibold uppercase tracking-wider">Total Kuantitas Barang</span>
                    <span class="text-xl font-black text-slate-900 dark:text-white mt-1 block tracking-tight">
                        {{ number_format($transfer->items->sum('quantity'), 0, ',', '.') }} <span class="text-xs font-normal text-slate-400">Unit / Pcs / Btg</span>
                    </span>
                </div>
                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700">
                    <span class="text-[11px] text-slate-500 dark:text-slate-400 block font-semibold uppercase tracking-wider">Pengemudi / Ekspedisi</span>
                    <span class="text-sm font-bold text-slate-800 dark:text-slate-200 mt-1 block">
                        {{ $transfer->driver_or_transporter ?: '(Armada Internal Proyek)' }}
                    </span>
                </div>
            </div>
        </div>

        {{-- 3. TABEL ITEM SURAT JALAN PEMINDAHAN (PERSIS REKAP PEMINDAHAN BARANG.xlsx) --}}
        <section class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/90 dark:border-slate-800 shadow-xs overflow-hidden">
            <div class="bg-slate-100/90 dark:bg-slate-800/90 px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-1.5 h-6 bg-teal-600 rounded-full"></div>
                    <div>
                        <h3 class="text-sm sm:text-base font-extrabold text-slate-900 dark:text-white tracking-tight uppercase">
                            Daftar Rincian Barang & Alat Dimobilisasi
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            {{ $transfer->title }} &bull; Dari {{ $transfer->origin_location ?: 'Proyek' }} ke {{ $transfer->destination_location ?: 'Gudang' }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-200/75 dark:bg-slate-800 text-slate-800 dark:text-slate-200 font-extrabold uppercase text-[11px] border-b border-slate-300 dark:border-slate-700 tracking-wider">
                            <th class="py-3 px-3 text-center border-r border-slate-300/80" style="width: 45px;">No</th>
                            <th class="py-3 px-3 border-r border-slate-300/80 text-center" style="width: 130px;">Tanggal</th>
                            <th class="py-3 px-4 border-r border-slate-300/80">Nama Barang / Material / Alat</th>
                            <th class="py-3 px-3 border-r border-slate-300/80 text-center" style="width: 90px;">Satuan</th>
                            <th class="py-3 px-3 border-r border-slate-300/80 text-center" style="width: 100px;">Jumlah</th>
                            <th class="py-3 px-4 border-r border-slate-300/80">Keterangan</th>
                            <th class="py-3 px-3 text-center" style="width: 90px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800 font-medium">
                        @forelse($transfer->items as $idx => $item)
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition">
                                <td class="py-2.5 px-3 border-r border-slate-200 text-center font-bold text-slate-400">
                                    {{ $idx + 1 }}
                                </td>
                                <td class="py-2.5 px-3 border-r border-slate-200 text-center font-bold text-slate-800 dark:text-slate-100 whitespace-nowrap">
                                    {{ $item->transfer_date ? $item->transfer_date->translatedFormat('d M Y') : '-' }}
                                </td>
                                <td class="py-2.5 px-4 border-r border-slate-200 font-bold text-slate-900 dark:text-white">
                                    {{ $item->item_name }}
                                </td>
                                <td class="py-2.5 px-3 border-r border-slate-200 text-center text-slate-600 dark:text-slate-300 font-semibold">
                                    {{ $item->unit }}
                                </td>
                                <td class="py-2.5 px-3 border-r border-slate-200 text-center font-black text-teal-700 dark:text-teal-400 text-sm">
                                    {{ (float)$item->quantity }}
                                </td>
                                <td class="py-2.5 px-4 border-r border-slate-200 text-slate-600 dark:text-slate-400">
                                    {{ $item->notes ?: '-' }}
                                </td>
                                <td class="py-2.5 px-3 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button type="button" @click="openEditItem({{ json_encode($item) }})"
                                            class="inline-flex items-center justify-center w-7 h-7 rounded-lg text-slate-600 dark:text-slate-300 hover:text-teal-600 hover:bg-slate-100 dark:hover:bg-slate-800 transition shadow-2xs"
                                            title="Edit Item">
                                            <x-lucide-pencil class="w-3.5 h-3.5" />
                                        </button>
                                        <form method="POST" action="{{ route('project-notes.transfer-items.destroy', $item) }}" onsubmit="return confirm('Hapus item {{ $item->item_name }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="inline-flex items-center justify-center w-7 h-7 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-slate-800 transition shadow-2xs"
                                                title="Hapus Item">
                                                <x-lucide-trash-2 class="w-3.5 h-3.5" />
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-10 text-center text-slate-400">
                                    <x-lucide-package class="w-8 h-8 mx-auto mb-2 text-slate-300 dark:text-slate-600" />
                                    <div class="font-bold text-slate-700 dark:text-slate-300 text-sm">Belum ada rincian barang yang dicatat dalam surat jalan ini.</div>
                                    <div class="text-xs text-slate-400 mt-0.5">Gunakan tombol <strong>+ Tambah Item Barang</strong> di atas untuk menambahkan barang.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($transfer->items->isNotEmpty())
                        <tfoot>
                            <tr class="bg-teal-100/80 dark:bg-teal-950/50 text-teal-950 dark:text-teal-100 font-black border-t-2 border-teal-300 dark:border-teal-800 text-xs">
                                <td colspan="4" class="py-3 px-3 text-right uppercase tracking-wider">
                                    TOTAL BARANG DIMOBILISASI:
                                </td>
                                <td class="py-3 px-3 text-center font-black text-sm text-teal-900 dark:text-teal-200">
                                    {{ (float)$transfer->items->sum('quantity') }}
                                </td>
                                <td colspan="2" class="py-3 px-4 text-slate-600 dark:text-slate-300">
                                    Total {{ $transfer->items->count() }} baris item surat jalan
                                </td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </section>

        {{-- MODAL: TAMBAH ITEM BARANG --}}
        <div x-show="showAddItemModal" x-cloak class="fixed inset-0 flex items-center justify-center bg-slate-950/65 backdrop-blur-xs p-4 overflow-y-auto" style="z-index: 9999;">
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-2xl w-full p-5 space-y-3.5 my-auto" style="max-width: 500px;" @click.away="showAddItemModal = false">
                <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-3">
                    <h3 class="text-sm font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                        <x-lucide-package-plus class="w-4 h-4 text-teal-600" /> Tambah Item Barang Surat Jalan
                    </h3>
                    <button type="button" @click="showAddItemModal = false" class="text-slate-400 hover:text-slate-600 font-bold">&times;</button>
                </div>

                <form method="POST" action="{{ route('project-notes.transfer-items.store', $transfer) }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Tanggal Kirim</label>
                        <input type="date" name="transfer_date" value="{{ $transfer->transfer_date ? $transfer->transfer_date->format('Y-m-d') : date('Y-m-d') }}" class="form-control text-xs w-full">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Nama Barang / Material / Alat *</label>
                        <input type="text" name="item_name" class="form-control text-xs w-full" placeholder="Contoh: Blok beton 1,2x1x0,6, WF 600 7m, Jack HYD 300T..." required>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Kuantitas / Jumlah *</label>
                            <input type="number" step="any" min="0.01" name="quantity" value="1" class="form-control text-xs w-full font-black" required>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Satuan *</label>
                            <input type="text" name="unit" value="Buah" class="form-control text-xs w-full" placeholder="Buah, Batang, Unit, Lembar..." required>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Keterangan / Kondisi</label>
                        <input type="text" name="notes" class="form-control text-xs w-full" placeholder="Catatan kondisi barang atau penempatan...">
                    </div>

                    <div class="flex justify-end gap-2 pt-3 border-t border-slate-200 dark:border-slate-800">
                        <button type="button" @click="showAddItemModal = false" class="btn btn-light btn-sm">Batal</button>
                        <button type="submit" class="btn btn-primary btn-sm bg-teal-600 hover:bg-teal-700 border-teal-600 text-white font-bold">
                            Simpan Item Barang
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
