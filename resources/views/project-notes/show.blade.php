<x-app-layout>
    <x-slot name="title">{{ $ledger->title }} - Arsikon</x-slot>

    <div x-data="{
        showAddEntryModal: false,
        showEditLedgerModal: false,
        showEditEntryModal: false,
        editEntry: {
            id: null,
            delivery_date: '',
            delivery_note_number: '',
            material_specification: '',
            deposit_volume: '',
            delivery_volume: '',
            unit: '',
            unit_price: '',
            batch_color: 'green',
            account_behalf: '',
            notes: ''
        },
        openEditEntry(entry) {
            this.editEntry = { ...entry };
            this.showEditEntryModal = true;
        }
    }" class="space-y-6" data-animate="panel">

        {{-- 1. Top Breadcrumb & Actions --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs">
            <div class="flex items-center gap-2 flex-wrap">
                <a href="{{ route('project-notes.index', ['project_id' => $ledger->project_id]) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-200 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 transition">
                    <x-lucide-arrow-left class="w-3.5 h-3.5" /> Kembali ke Daftar Rekap
                </a>
                <span class="text-slate-300 dark:text-slate-700 hidden sm:inline">|</span>
                <span class="inline-flex items-center gap-1 text-xs font-bold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-950/40 px-2.5 py-1 rounded-lg border border-blue-200/60 dark:border-blue-900">
                    <x-lucide-building-2 class="w-3.5 h-3.5" /> {{ $ledger->project?->name ?? ($ledger->project_name ?? 'Proyek') }}
                </span>
                <span class="inline-flex items-center gap-1 text-xs font-bold bg-emerald-50 text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300 px-2.5 py-1 rounded-lg border border-emerald-200/70 dark:border-emerald-900">
                    <x-lucide-calendar class="w-3.5 h-3.5 text-emerald-600" /> Minggu ke-{{ $ledger->week_number ?? $ledger->start_date?->isoWeek() }}
                </span>
            </div>

            <div class="flex items-center gap-2 flex-wrap">
                <button type="button" @click="showEditLedgerModal = true"
                    class="btn btn-sm btn-light border inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1.5 rounded-xl text-slate-700 hover:bg-slate-100 transition shadow-2xs">
                    <x-lucide-pencil class="w-3.5 h-3.5 text-slate-500" /> Edit Buku Catatan
                </button>
                <a href="{{ route('project-notes.ledgers.print', $ledger) }}"
                   target="_blank"
                   class="btn btn-sm bg-slate-900 hover:bg-slate-800 text-white inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1.5 rounded-xl transition shadow-2xs"
                   title="Cetak Lembar Catatan Material (A4 Landscape 1/1)">
                    <x-lucide-printer class="w-3.5 h-3.5 text-amber-400" /> Cetak (1/1)
                </a>
                <a href="{{ route('project-notes.ledgers.export', $ledger) }}"
                   class="btn btn-sm bg-emerald-600 hover:bg-emerald-700 text-white inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1.5 rounded-xl transition shadow-2xs"
                   title="Export Excel Lembar Catatan Material (.xlsx 1/1)">
                    <x-lucide-file-spreadsheet class="w-3.5 h-3.5" /> Export Excel (1/1)
                </a>
                {{-- Satu-satunya tombol aksi tambah Surat Jalan (Primary CTA tunggal di atas, tanpa tombol ganda) --}}
                <button type="button" @click="showAddEntryModal = true"
                    class="btn btn-sm bg-orange-600 hover:bg-orange-700 text-white inline-flex items-center gap-1.5 text-xs font-bold px-3.5 py-1.5 rounded-xl shadow-xs transition">
                    <x-lucide-plus class="w-4 h-4" /> + Catat Surat Jalan
                </button>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success p-3.5 rounded-xl bg-emerald-50 border border-emerald-300 text-emerald-800 text-xs font-semibold flex items-center justify-between">
                <span class="flex items-center gap-2"><x-lucide-circle-check class="w-4 h-4 text-emerald-600" /> {{ session('success') }}</span>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger p-3.5 rounded-xl bg-rose-50 border border-rose-300 text-rose-800 text-xs font-semibold flex items-center justify-between">
                <span class="flex items-center gap-2"><x-lucide-circle-alert class="w-4 h-4 text-rose-600" /> {{ session('error') }}</span>
            </div>
        @endif

        {{-- 2. Hero Header Card & Summary Metrics --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/90 dark:border-slate-800 p-6 shadow-xs space-y-5">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 dark:border-slate-800 pb-4">
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="px-2.5 py-1 rounded-md text-[10px] font-extrabold bg-blue-600 text-white uppercase tracking-wider">
                            {{ $ledger->default_unit == 'm3' ? 'READYMIX' : ($ledger->default_unit == 'btg' ? 'BESI & BAJA' : 'MATERIAL') }}
                        </span>
                        <span class="text-xs text-slate-400 font-mono">#{{ $ledger->id }}</span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white mt-1.5 tracking-tight" data-animate="header">
                        {{ $ledger->title }}
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 flex items-center gap-1.5 flex-wrap">
                        <span>Suplier: <strong class="text-slate-800 dark:text-slate-200">{{ $ledger->supplier_name }}</strong></span>
                        <span class="text-slate-300 dark:text-slate-700">&bull;</span>
                        <span>Penerima : <strong class="text-slate-800 dark:text-slate-200 font-mono">{{ $ledger->account_behalf ?: '-' }}</strong></span>
                        <span class="text-slate-300 dark:text-slate-700">&bull;</span>
                        <span>Periode: <strong>{{ $ledger->start_date ? $ledger->start_date->translatedFormat('d M Y') : '-' }} @if($ledger->end_date) s/d {{ $ledger->end_date->translatedFormat('d M Y') }} @endif</strong></span>
                    </p>
                </div>
                <div class="flex items-center gap-2 self-start md:self-center">
                    <div class="px-4 py-2 rounded-2xl text-xs font-extrabold flex items-center gap-2 border {{ $ledger->remaining_deposit_vol <= 0 ? 'bg-emerald-50 text-emerald-800 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800' : 'bg-amber-50 text-amber-800 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800' }}">
                        <span class="w-2 h-2 rounded-full {{ $ledger->remaining_deposit_vol <= 0 ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                        <span>Sisa Kuota: {{ number_format($ledger->remaining_deposit_vol, 2, ',', '.') }} {{ $ledger->default_unit }}</span>
                    </div>
                </div>
            </div>

            {{-- 5 Metric Cards (Responsif 5 Kolom Seimbang) --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-5 gap-3.5 text-xs">
                <div class="p-4 rounded-2xl bg-slate-50/80 dark:bg-slate-800/50 border border-slate-200/80 dark:border-slate-700 transition hover:bg-slate-100/80">
                    <span class="text-[11px] text-slate-500 dark:text-slate-400 block font-semibold uppercase tracking-wider">Total Vol. Deposit</span>
                    <span class="text-lg font-black text-slate-900 dark:text-white mt-1 block tracking-tight">
                        {{ number_format($ledger->total_deposit_vol, 2, ',', '.') }} <span class="text-xs font-semibold text-slate-400">{{ $ledger->default_unit }}</span>
                    </span>
                </div>
                <div class="p-4 rounded-2xl bg-slate-50/80 dark:bg-slate-800/50 border border-slate-200/80 dark:border-slate-700 transition hover:bg-slate-100/80">
                    <span class="text-[11px] text-slate-500 dark:text-slate-400 block font-semibold uppercase tracking-wider">Total Vol. Terkirim</span>
                    <span class="text-lg font-black text-slate-900 dark:text-white mt-1 block tracking-tight">
                        {{ number_format($ledger->total_delivered_vol, 2, ',', '.') }} <span class="text-xs font-semibold text-slate-400">{{ $ledger->default_unit }}</span>
                    </span>
                </div>
                <div class="p-4 rounded-2xl bg-slate-50/80 dark:bg-slate-800/50 border border-slate-200/80 dark:border-slate-700 transition hover:bg-slate-100/80">
                    <span class="text-[11px] text-slate-500 dark:text-slate-400 block font-semibold uppercase tracking-wider">Harga Satuan Default</span>
                    <span class="text-lg font-black text-slate-900 dark:text-white mt-1 block tracking-tight">
                        @if((float)$ledger->default_unit_price > 0)
                            Rp {{ number_format($ledger->default_unit_price, 0, ',', '.') }}
                        @else
                            <span class="text-slate-400 italic font-normal text-xs">(Belum Ditentukan)</span>
                        @endif
                    </span>
                </div>
                <div class="p-4 rounded-2xl bg-blue-50/70 dark:bg-blue-950/30 border border-blue-200/70 dark:border-blue-900 transition hover:bg-blue-50">
                    <span class="text-[11px] text-blue-700 dark:text-blue-300 block font-bold uppercase tracking-wider">Total Nilai Pemakaian</span>
                    <span class="text-lg font-black text-blue-800 dark:text-blue-200 mt-1 block tracking-tight">
                        Rp {{ number_format($ledger->total_used_amount, 0, ',', '.') }}
                    </span>
                </div>
                <div class="p-4 rounded-2xl bg-emerald-50/70 dark:bg-emerald-950/30 border border-emerald-200/70 dark:border-emerald-900 transition hover:bg-emerald-50">
                    <span class="text-[11px] text-emerald-700 dark:text-emerald-300 block font-bold uppercase tracking-wider">Jumlah Surat Jalan</span>
                    <span class="text-lg font-black text-emerald-800 dark:text-emerald-200 mt-1 block tracking-tight">
                        {{ $ledger->entries->count() }} Pengiriman
                    </span>
                </div>
            </div>
        </div>

        {{-- 3. TABEL LENGKAP 13 KOLOM PERSIS material.jpeg & REKAP SUPRI.xlsx --}}
        <section class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/90 dark:border-slate-800 shadow-xs overflow-hidden">
            
            {{-- Header Rincian Surat Jalan (Rapi & Tanpa Tombol Ganda) --}}
            <div class="bg-slate-100/90 dark:bg-slate-800/90 px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-1.5 h-6 bg-orange-600 rounded-full"></div>
                    <div>
                        <h3 class="text-sm sm:text-base font-extrabold text-slate-900 dark:text-white tracking-tight uppercase">
                            Rincian Surat Jalan Pengiriman Truk
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            {{ $ledger->supplier_name }} &bull; Penerima : <span class="font-mono font-semibold text-slate-700 dark:text-slate-200">{{ $ledger->account_behalf ?: '-' }}</span>
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2 self-start sm:self-center">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-700 text-slate-700 dark:text-slate-200 shadow-2xs">
                        <x-lucide-truck class="w-3.5 h-3.5 text-orange-600" /> Total {{ $ledger->entries->count() }} Pengiriman
                    </span>
                </div>
            </div>

            {{-- 13 Kolom Presisi --}}
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[1250px] text-xs">
                    <thead>
                        <tr class="bg-slate-200/80 dark:bg-slate-800 text-slate-800 dark:text-slate-200 font-extrabold text-[11px] uppercase border-b-2 border-slate-300 dark:border-slate-700 text-center tracking-wide">
                            <th class="py-3 px-3 border-r border-slate-400/60">Tanggal</th>
                            <th class="py-3 px-3 border-r border-slate-400/60">Suplier</th>
                            <th class="py-3 px-2 border-r border-slate-400/60">Penerima</th>
                            <th class="py-3 px-3 border-r border-slate-400/60">No. Surat</th>
                            <th class="py-3 px-3 border-r border-slate-400/60">Nama Material</th>
                            <th class="py-3 px-2.5 border-r border-slate-400/60">Vol. Deposit</th>
                            <th class="py-3 px-2.5 border-r border-slate-400/60">Vol Pengiriman</th>
                            <th class="py-3 px-2 border-r border-slate-400/60">Sat</th>
                            <th class="py-3 px-3 border-r border-slate-400/60">Harga</th>
                            <th class="py-3 px-3 border-r border-slate-400/60">Jumlah Dipakai</th>
                            <th class="py-3 px-3 border-r border-slate-400/60">Total Deposit</th>
                            <th class="py-3 px-2.5 border-r border-slate-400/60">Total Volume</th>
                            <th class="py-3 px-3 border-r border-slate-400/60">Keterangan</th>
                            <th class="py-3 px-2.5 border-r border-slate-400/60">Sisa Vol</th>
                            <th class="py-3 px-2.5 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-300 dark:divide-slate-700 text-slate-900 dark:text-slate-100 font-medium">
                        @forelse($ledger->calculated_entries as $entry)
                            @php
                                $bgClass = match($entry->batch_color) {
                                    'green'  => 'bg-[#C6E0B4] dark:bg-emerald-950/40',
                                    'yellow' => 'bg-[#FFE699] dark:bg-amber-950/40',
                                    'peach', 'orange' => 'bg-[#F8CBAD] dark:bg-orange-950/40',
                                    'blue'   => 'bg-[#B4C6E7] dark:bg-blue-950/40',
                                    default  => 'bg-white dark:bg-slate-900',
                                };
                            @endphp
                            <tr class="{{ $bgClass }} transition hover:brightness-95">
                                <td class="py-2.5 px-3 border-r border-slate-300 text-center whitespace-nowrap">
                                    {{ $entry->delivery_date ? $entry->delivery_date->format('d/m/Y') : '-' }}
                                </td>
                                <td class="py-2.5 px-3 border-r border-slate-300">
                                    {{ $entry->supplier_name ?: $ledger->supplier_name }}
                                </td>
                                <td class="py-2.5 px-2 border-r border-slate-300 text-center font-bold">
                                    {{ $entry->account_behalf ?: ($ledger->account_behalf ?: '-') }}
                                </td>
                                <td class="py-2.5 px-3 border-r border-slate-300 text-center font-mono font-bold">
                                    {{ $entry->delivery_note_number }}
                                </td>
                                <td class="py-2.5 px-3 border-r border-slate-300">
                                    {{ $entry->material_specification }}
                                </td>
                                <td class="py-2.5 px-2.5 border-r border-slate-300 text-center font-extrabold">
                                    {{ $entry->deposit_volume ? number_format($entry->deposit_volume, 0, ',', '.') : '' }}
                                </td>
                                <td class="py-2.5 px-2.5 border-r border-slate-300 text-center font-black">
                                    {{ number_format($entry->delivery_volume, 0, ',', '.') }}
                                </td>
                                <td class="py-2.5 px-2 border-r border-slate-300 text-center">
                                    {{ $entry->unit }}
                                </td>
                                <td class="py-2.5 px-3 border-r border-slate-300 text-right tabular-nums">
                                    @if((float)$entry->unit_price > 0)
                                        Rp {{ number_format($entry->unit_price, 0, ',', '.') }}
                                    @else
                                        <span class="text-slate-400 italic">-</span>
                                    @endif
                                </td>
                                <td class="py-2.5 px-3 border-r border-slate-300 text-right tabular-nums font-bold">
                                    @if((float)$entry->calculated_amount_used > 0)
                                        Rp {{ number_format($entry->calculated_amount_used, 0, ',', '.') }}
                                    @else
                                        <span class="text-slate-400 italic">-</span>
                                    @endif
                                </td>
                                <td class="py-2.5 px-3 border-r border-slate-300 text-right tabular-nums font-bold">
                                    @if((float)$entry->running_total_amount > 0)
                                        Rp {{ number_format($entry->running_total_amount, 0, ',', '.') }}
                                    @else
                                        <span class="text-slate-400 italic">-</span>
                                    @endif
                                </td>
                                <td class="py-2.5 px-2.5 border-r border-slate-300 text-right tabular-nums font-black">
                                    {{ number_format($entry->running_total_volume, 2, ',', '.') }}
                                </td>
                                <td class="py-2.5 px-3 border-r border-slate-300 text-slate-600">
                                    {{ $entry->notes }}
                                </td>
                                <td class="py-2.5 px-2.5 border-r border-slate-300 text-center font-semibold text-slate-400 dark:text-slate-500">
                                    -
                                </td>
                                <td class="py-2.5 px-2.5 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button type="button" @click="openEditEntry({{ json_encode($entry) }})"
                                            class="inline-flex items-center justify-center w-7 h-7 rounded-lg text-slate-600 dark:text-slate-300 hover:text-blue-600 hover:bg-white dark:hover:bg-slate-800 transition border border-transparent hover:border-slate-300 shadow-2xs"
                                            title="Edit Surat Jalan">
                                            <x-lucide-pencil class="w-3.5 h-3.5" />
                                        </button>
                                        <form method="POST" action="{{ route('project-notes.entries.destroy', $entry) }}" onsubmit="return confirm('Hapus baris surat jalan {{ $entry->delivery_note_number }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="inline-flex items-center justify-center w-7 h-7 rounded-lg text-slate-500 hover:text-rose-600 hover:bg-white dark:hover:bg-slate-800 transition border border-transparent hover:border-rose-200 shadow-2xs"
                                                title="Hapus Baris">
                                                <x-lucide-trash-2 class="w-3.5 h-3.5" />
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="15" class="py-10 text-center text-slate-400">
                                    <x-lucide-truck class="w-8 h-8 mx-auto mb-2 text-slate-300 dark:text-slate-600" />
                                    <div class="font-bold text-slate-700 dark:text-slate-300 text-sm">Belum ada surat jalan pengiriman yang tercatat.</div>
                                    <div class="text-xs text-slate-400 mt-0.5">Gunakan tombol <strong>+ Catat Surat Jalan</strong> di atas untuk menambahkan pengiriman baru.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                    {{-- Baris Grand Total Rekapitulasi --}}
                    @if($ledger->calculated_entries->isNotEmpty())
                        <tfoot>
                            <tr class="bg-amber-200/90 dark:bg-amber-500/20 text-slate-950 dark:text-amber-100 font-black border-t-2 border-slate-400 dark:border-slate-700 text-xs">
                                <td colspan="5" class="py-3 px-3 border-r border-slate-400/80 text-right uppercase tracking-wider">
                                    TOTAL REKAPITULASI PENGIRIMAN:
                                </td>
                                <td class="py-3 px-2.5 border-r border-slate-400/80 text-center tabular-nums font-black">
                                    {{ number_format($ledger->total_deposit_vol, 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-2.5 border-r border-slate-400/80 text-center tabular-nums font-black">
                                    {{ number_format($ledger->total_delivered_vol, 0, ',', '.') }}
                                </td>
                                <td colspan="2" class="py-3 px-3 border-r border-slate-400/80"></td>
                                <td class="py-3 px-3 border-r border-slate-400/80 text-right tabular-nums font-black">
                                    Rp {{ number_format($ledger->total_used_amount, 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-3 border-r border-slate-400/80 text-right tabular-nums font-black">
                                    Rp {{ number_format($ledger->total_used_amount, 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-2.5 border-r border-slate-400/80 text-right tabular-nums font-black">
                                    {{ number_format($ledger->total_delivered_vol, 2, ',', '.') }}
                                </td>
                                <td class="py-3 px-3 border-r border-slate-400/80 text-center font-bold">TOTAL SISA VOL</td>
                                <td class="py-3 px-2.5 border-r border-slate-400/80 text-center font-bold text-slate-500 dark:text-slate-400">
                                    -
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </section>

        {{-- ========================================================================= --}}
        {{-- MODAL: CATAT SURAT JALAN BARU                                              --}}
        {{-- ========================================================================= --}}
        <div x-show="showAddEntryModal" x-cloak class="fixed inset-0 flex items-center justify-center bg-slate-950/65 backdrop-blur-xs p-4 overflow-y-auto" style="z-index: 9999;">
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-2xl w-full p-5 space-y-3.5 my-auto" style="max-width: 520px;" @click.away="showAddEntryModal = false">
                <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-3">
                    <h3 class="text-sm font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                        <x-lucide-truck class="w-4 h-4 text-orange-600" /> Catat Surat Jalan Pengiriman
                    </h3>
                    <button type="button" @click="showAddEntryModal = false" class="text-slate-400 hover:text-slate-600 font-bold">&times;</button>
                </div>

                <form method="POST" action="{{ route('project-notes.entries.store', $ledger) }}" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Tanggal Surat Jalan *</label>
                            <input type="date" name="delivery_date" value="{{ now()->format('Y-m-d') }}" class="form-control text-xs w-full" required>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">No. Surat Jalan *</label>
                            <input type="text" name="delivery_note_number" class="form-control text-xs w-full font-mono font-bold" placeholder="Contoh: 2290102" required>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Spesifikasi Material & Armada Truk *</label>
                        <input type="text" name="material_specification" class="form-control text-xs w-full" placeholder="Contoh: K-300/FC25, 18+/-2, G25, T1" required>
                    </div>

                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Vol. Pengiriman *</label>
                            <input type="number" step="any" name="delivery_volume" class="form-control text-xs w-full font-black" placeholder="5" required>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Vol. Deposit (Top-up)</label>
                            <input type="number" step="any" name="deposit_volume" class="form-control text-xs w-full" placeholder="Kosongkan jika batch berjalan">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Satuan *</label>
                            <input type="text" name="unit" value="{{ $ledger->default_unit ?: 'm3' }}" class="form-control text-xs w-full" required>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                Harga Satuan (Rp) <span class="text-slate-400 font-normal">(Bisa Kosongkan)</span>
                            </label>
                            <input type="number" step="any" name="unit_price" value="{{ (float)$ledger->default_unit_price > 0 ? $ledger->default_unit_price : '' }}" class="form-control text-xs w-full" placeholder="Bisa kosongkan dulu">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Warna Pengelompokan Batch *</label>
                            <select name="batch_color" class="form-control text-xs w-full" required>
                                <option value="green">🟢 Batch Hijau (#C6E0B4)</option>
                                <option value="yellow">🟡 Batch Kuning (#FFE699)</option>
                                <option value="peach">🟠 Batch Peach / Oranye (#F8CBAD)</option>
                                <option value="blue">🔵 Batch Biru Muda (#B4C6E7)</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Keterangan Tambahan</label>
                        <input type="text" name="notes" class="form-control text-xs w-full" placeholder="Catatan pengiriman, kondisi cor, dll.">
                    </div>

                    <div class="flex justify-end gap-2 pt-3 border-t border-slate-200 dark:border-slate-800">
                        <button type="button" @click="showAddEntryModal = false" class="btn btn-light btn-sm">Batal</button>
                        <button type="submit" class="btn btn-primary btn-sm bg-orange-600 hover:bg-orange-700 border-orange-600 text-white font-bold">
                            Simpan Surat Jalan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- MODAL: EDIT SURAT JALAN                                                    --}}
        {{-- ========================================================================= --}}
        <div x-show="showEditEntryModal" x-cloak class="fixed inset-0 flex items-center justify-center bg-slate-950/65 backdrop-blur-xs p-4 overflow-y-auto" style="z-index: 9999;">
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-2xl w-full p-5 space-y-3.5 my-auto" style="max-width: 520px;" @click.away="showEditEntryModal = false">
                <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-3">
                    <h3 class="text-sm font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                        <x-lucide-pencil class="w-4 h-4 text-blue-600" /> Edit Surat Jalan Pengiriman
                    </h3>
                    <button type="button" @click="showEditEntryModal = false" class="text-slate-400 hover:text-slate-600 font-bold">&times;</button>
                </div>

                <form method="POST" :action="'/project-notes/entries/' + editEntry.id" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Tanggal Surat Jalan *</label>
                            <input type="date" name="delivery_date" x-model="editEntry.delivery_date" class="form-control text-xs w-full" required>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">No. Surat Jalan *</label>
                            <input type="text" name="delivery_note_number" x-model="editEntry.delivery_note_number" class="form-control text-xs w-full font-mono font-bold" required>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Spesifikasi Material & Armada Truk *</label>
                        <input type="text" name="material_specification" x-model="editEntry.material_specification" class="form-control text-xs w-full" required>
                    </div>

                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Vol. Pengiriman *</label>
                            <input type="number" step="any" name="delivery_volume" x-model="editEntry.delivery_volume" class="form-control text-xs w-full font-black" required>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Vol. Deposit (Top-up)</label>
                            <input type="number" step="any" name="deposit_volume" x-model="editEntry.deposit_volume" class="form-control text-xs w-full">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Satuan *</label>
                            <input type="text" name="unit" x-model="editEntry.unit" class="form-control text-xs w-full" required>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                Harga Satuan (Rp) <span class="text-slate-400 font-normal">(Bisa Kosongkan)</span>
                            </label>
                            <input type="number" step="any" name="unit_price" x-model="editEntry.unit_price" class="form-control text-xs w-full">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Warna Batch *</label>
                            <select name="batch_color" x-model="editEntry.batch_color" class="form-control text-xs w-full" required>
                                <option value="green">🟢 Batch Hijau (#C6E0B4)</option>
                                <option value="yellow">🟡 Batch Kuning (#FFE699)</option>
                                <option value="peach">🟠 Batch Peach / Oranye (#F8CBAD)</option>
                                <option value="blue">🔵 Batch Biru Muda (#B4C6E7)</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Keterangan</label>
                        <input type="text" name="notes" x-model="editEntry.notes" class="form-control text-xs w-full">
                    </div>

                    <div class="flex justify-end gap-2 pt-3 border-t border-slate-200 dark:border-slate-800">
                        <button type="button" @click="showEditEntryModal = false" class="btn btn-light btn-sm">Batal</button>
                        <button type="submit" class="btn btn-primary btn-sm bg-blue-600 hover:bg-blue-700 border-blue-600 text-white font-bold">
                            Perbarui Surat Jalan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- MODAL: EDIT BUKU CATATAN MATERIAL                                          --}}
        {{-- ========================================================================= --}}
        <div x-show="showEditLedgerModal" x-cloak class="fixed inset-0 flex items-center justify-center bg-slate-950/65 backdrop-blur-xs p-4 overflow-y-auto" style="z-index: 9999;">
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-2xl w-full p-5 space-y-3.5 my-auto" style="max-width: 520px;" @click.away="showEditLedgerModal = false">
                <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-3">
                    <h3 class="text-sm font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                        <x-lucide-pencil class="w-4 h-4 text-orange-600" /> Edit Header Buku Catatan
                    </h3>
                    <button type="button" @click="showEditLedgerModal = false" class="text-slate-400 hover:text-slate-600 font-bold">&times;</button>
                </div>

                <form method="POST" action="{{ route('project-notes.ledgers.update', $ledger) }}" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Judul Buku Catatan *</label>
                        <input type="text" name="title" value="{{ $ledger->title }}" class="form-control text-xs w-full" required>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Nama Suplier *</label>
                            <input type="text" name="supplier_name" value="{{ $ledger->supplier_name }}" class="form-control text-xs w-full" required>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Penerima</label>
                            <input type="text" name="account_behalf" value="{{ $ledger->account_behalf }}" class="form-control text-xs w-full">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Satuan Material *</label>
                            <input type="text" name="default_unit" value="{{ $ledger->default_unit }}" class="form-control text-xs w-full" required>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Harga Satuan Default (Rp)</label>
                            <input type="number" step="any" name="default_unit_price" value="{{ (float)$ledger->default_unit_price > 0 ? $ledger->default_unit_price : '' }}" class="form-control text-xs w-full" placeholder="Bisa kosongkan dulu">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Tanggal Mulai Periode *</label>
                            <input type="date" name="start_date" value="{{ $ledger->start_date?->format('Y-m-d') }}" class="form-control text-xs w-full" required>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Tanggal Akhir Periode</label>
                            <input type="date" name="end_date" value="{{ $ledger->end_date?->format('Y-m-d') }}" class="form-control text-xs w-full">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Catatan Tambahan</label>
                        <input type="text" name="notes" value="{{ $ledger->notes }}" class="form-control text-xs w-full">
                    </div>

                    <div class="flex justify-end gap-2 pt-3 border-t border-slate-200 dark:border-slate-800">
                        <button type="button" @click="showEditLedgerModal = false" class="btn btn-light btn-sm">Batal</button>
                        <button type="submit" class="btn btn-primary btn-sm bg-orange-600 hover:bg-orange-700 border-orange-600 text-white font-bold">
                            Perbarui Buku Catatan
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>

