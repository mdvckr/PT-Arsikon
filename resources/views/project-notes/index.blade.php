<x-app-layout>
    <x-slot name="title">Buku Catatan Proyek - Arsikon</x-slot>

    <div x-data="{
        activeTab: 'all',

        showLedgerModal: false,
        showEditLedgerModal: false,
        editLedger: {
            id: null,
            title: '',
            supplier_name: '',
            supplier_id: '',
            account_behalf: '',
            default_unit: 'm3',
            default_unit_price: '',
            start_date: '',
            end_date: '',
            notes: ''
        },
        openEditLedger(ledger) {
            this.editLedger = {
                id: ledger.id,
                title: ledger.title,
                supplier_name: ledger.supplier_name,
                supplier_id: ledger.supplier_id || '',
                account_behalf: ledger.account_behalf || '',
                default_unit: ledger.default_unit || 'm3',
                default_unit_price: ledger.default_unit_price > 0 ? ledger.default_unit_price : '',
                start_date: ledger.start_date ? ledger.start_date.substring(0, 10) : '',
                end_date: ledger.end_date ? ledger.end_date.substring(0, 10) : '',
                notes: ledger.notes || ''
            };
            this.showEditLedgerModal = true;
        },

        showRentalModal: false,
        showEditRentalModal: false,
        editRental: {
            id: null,
            tool_name: '',
            vendor_name: '',
            docket_number: '',
            start_date: '',
            end_date: '',
            rental_duration: 1,
            duration_unit: 'Hari',
            rate_per_unit: '',
            deposit_amount: '',
            status: 'active',
            notes: ''
        },
        openEditRental(rental) {
            this.editRental = {
                id: rental.id,
                tool_name: rental.tool_name,
                vendor_name: rental.vendor_name,
                docket_number: rental.docket_number || '',
                start_date: rental.start_date ? rental.start_date.substring(0, 10) : '',
                end_date: rental.end_date ? rental.end_date.substring(0, 10) : '',
                rental_duration: rental.rental_duration,
                duration_unit: rental.duration_unit || 'Hari',
                rate_per_unit: rental.rate_per_unit > 0 ? rental.rate_per_unit : '',
                deposit_amount: rental.deposit_amount > 0 ? rental.deposit_amount : '',
                status: rental.status,
                notes: rental.notes || ''
            };
            this.showEditRentalModal = true;
        },

        showTransferModal: false,
        showEditTransferModal: false,
        editTransfer: {
            id: null,
            title: '',
            origin_location: '',
            destination_location: '',
            transfer_date: '',
            docket_number: '',
            driver_or_transporter: '',
            status: 'in_transit',
            notes: ''
        },
        openEditTransfer(transfer) {
            this.editTransfer = {
                id: transfer.id,
                title: transfer.title,
                origin_location: transfer.origin_location || '',
                destination_location: transfer.destination_location || '',
                transfer_date: transfer.transfer_date ? transfer.transfer_date.substring(0, 10) : '',
                docket_number: transfer.docket_number || '',
                driver_or_transporter: transfer.driver_or_transporter || '',
                status: transfer.status || 'in_transit',
                notes: transfer.notes || ''
            };
            this.showEditTransferModal = true;
        },

        showBulkModal: false,
        showEditBulkModal: false,
        editBulk: {
            id: null,
            title: '',
            supplier_name: '',
            supplier_id: '',
            start_date: '',
            end_date: '',
            status: 'active',
            notes: ''
        },
        openEditBulk(bulk) {
            this.editBulk = {
                id: bulk.id,
                title: bulk.title,
                supplier_name: bulk.supplier_name,
                supplier_id: bulk.supplier_id || '',
                start_date: bulk.start_date ? bulk.start_date.substring(0, 10) : '',
                end_date: bulk.end_date ? bulk.end_date.substring(0, 10) : '',
                status: bulk.status || 'active',
                notes: bulk.notes || ''
            };
            this.showEditBulkModal = true;
        }
    }">

        {{-- Top Bar Header --}}
        <div class="flex justify-between items-center mb-5 flex-wrap gap-3">
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h2 class="text-xl font-extrabold text-slate-900 dark:text-white tracking-tight">Buku Catatan Proyek</h2>
                    @if(isset($selectedProject) && $selectedProject)
                        <span class="inline-flex items-center gap-1 text-xs font-bold bg-blue-50 text-blue-800 dark:bg-blue-950/40 dark:text-blue-300 px-2.5 py-1 rounded-lg border border-blue-200/70 dark:border-blue-900">
                            <x-lucide-building-2 class="w-3.5 h-3.5 text-blue-600" /> {{ $selectedProject->name }}
                        </span>
                    @endif
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    Rekapitulasi penggunaan material berkala dan pemantauan sewa alat proyek berkala.
                </p>
            </div>
        </div>

        {{-- Alerts --}}
        @if(session('success'))
            <div class="alert alert-success mb-4 p-3.5 rounded-2xl bg-emerald-50 border border-emerald-300 text-emerald-800 text-xs font-semibold flex items-center justify-between">
                <span class="flex items-center gap-2"><x-lucide-circle-check class="w-4 h-4 text-emerald-600" /> {{ session('success') }}</span>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger mb-4 p-3.5 rounded-2xl bg-rose-50 border border-rose-300 text-rose-800 text-xs font-semibold flex items-center justify-between">
                <span class="flex items-center gap-2"><x-lucide-circle-alert class="w-4 h-4 text-rose-600" /> {{ session('error') }}</span>
            </div>
        @endif

        

        {{-- Navigasi Tab 4 Fitur Buku Catatan Proyek --}}
        <div class="flex items-center gap-2 mb-4 overflow-x-auto pb-1">
            <button type="button"
                    @click="activeTab = 'all'"
                    :class="activeTab === 'all' ? 'bg-slate-900 text-white dark:bg-slate-100 dark:text-slate-900 shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200 dark:bg-slate-900 dark:text-slate-400 dark:border-slate-800'"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition inline-flex items-center gap-1.5 whitespace-nowrap">
                <x-lucide-layout-grid class="w-3.5 h-3.5" />
                <span>Semua Panel (Split View)</span>
            </button>
            <button type="button"
                    @click="activeTab = 'material'"
                    :class="activeTab === 'material' ? 'bg-orange-600 text-white shadow-sm shadow-orange-600/30' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200 dark:bg-slate-900 dark:text-slate-400 dark:border-slate-800'"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition inline-flex items-center gap-1.5 whitespace-nowrap">
                <x-lucide-clipboard-list class="w-3.5 h-3.5" />
                <span>1. Truk Molen (Readymix)</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px]" :class="activeTab === 'material' ? 'bg-white/20 text-white' : 'bg-orange-50 text-orange-700 dark:bg-orange-950/40 dark:text-orange-300'">
                    {{ $ledgers->count() }}
                </span>
            </button>
            <button type="button"
                    @click="activeTab = 'curah'"
                    :class="activeTab === 'curah' ? 'bg-amber-600 text-white shadow-sm shadow-amber-600/30' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200 dark:bg-slate-900 dark:text-slate-400 dark:border-slate-800'"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition inline-flex items-center gap-1.5 whitespace-nowrap">
                <x-lucide-layers class="w-3.5 h-3.5" />
                <span>2. Material Curah (Pasir / Batu)</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px]" :class="activeTab === 'curah' ? 'bg-white/20 text-white' : 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300'">
                    {{ $bulkMaterials->count() }}
                </span>
            </button>
            <button type="button"
                    @click="activeTab = 'rental'"
                    :class="activeTab === 'rental' ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-600/30' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200 dark:bg-slate-900 dark:text-slate-400 dark:border-slate-800'"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition inline-flex items-center gap-1.5 whitespace-nowrap">
                <x-lucide-truck class="w-3.5 h-3.5" />
                <span>3. Sewa Alat & Timesheet</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px]" :class="activeTab === 'rental' ? 'bg-white/20 text-white' : 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-300'">
                    {{ $rentals->count() }}
                </span>
            </button>
            <button type="button"
                    @click="activeTab = 'transfer'"
                    :class="activeTab === 'transfer' ? 'bg-teal-600 text-white shadow-sm shadow-teal-600/30' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200 dark:bg-slate-900 dark:text-slate-400 dark:border-slate-800'"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition inline-flex items-center gap-1.5 whitespace-nowrap">
                <x-lucide-arrow-right-left class="w-3.5 h-3.5" />
                <span>4. Mobilisasi Antar Proyek</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px]" :class="activeTab === 'transfer' ? 'bg-white/20 text-white' : 'bg-teal-50 text-teal-700 dark:bg-teal-950/40 dark:text-teal-300'">
                    {{ $transfers->count() }}
                </span>
            </button>
        </div>

        {{-- ========================================================================= --}}
        {{-- WORKSPACE 3 PANEL (MATERIAL, SEWA ALAT & TIMESHEET, PEMINDAHAN BARANG)    --}}
        {{-- ========================================================================= --}}
        <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">

            {{-- ===================================================================== --}}
            {{-- PANEL KIRI: REKAP CATAT MATERIAL (7 Kolom pada layar XL)              --}}
            {{-- ===================================================================== --}}
            <div :class="activeTab === 'material' ? 'xl:col-span-12' : 'xl:col-span-7'" x-show="activeTab === 'all' || activeTab === 'material'">
                <div class="card shadow-sm h-full flex flex-col" style="border:1.5px solid #cbd5e1;">
                    
                    {{-- Header Panel Kiri dengan Tombol Cetak 1/1, Export Excel 1/1, dan Tambah --}}
                    <div style="background:#f8fafc;padding:12px 18px;border-bottom:1.5px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 style="font-size:15px;font-weight:800;color:#0f172a;margin:0;letter-spacing:-0.2px;">
                                    REKAP CATAT MATERIAL COR (TRUK MOLEN)
                                </h3>
                                <span class="badge" style="background:#dbeafe;color:#1e40af;border:1px solid #93c5fd;font-size:11px;padding:2px 8px;font-weight:700;">
                                    {{ $ledgers->count() }} Buku Readymix
                                </span>
                            </div>
                            <p style="font-size:11.5px;color:#64748b;margin:2px 0 0 0;">
                                Pemantauan deposit kuota cor readymix m³, surat jalan pengiriman, & sisa volume
                            </p>
                        </div>

                        <div class="flex items-center gap-2 flex-wrap">
                            {{-- Cetak 1/1 Khusus Catatan Material --}}
                            <a href="{{ route('project-notes.print-material', request()->all()) }}"
                               target="_blank"
                               class="btn btn-xs"
                               style="background:#0f172a;border:1px solid #0f172a;color:#ffffff;font-size:11.5px;font-weight:600;padding:6px 11px;border-radius:6px;display:inline-flex;align-items:center;gap:4px;"
                               title="Cetak Catatan Material (A4 Landscape 1/1)">
                                <x-lucide-printer style="width:13px;height:13px;" /> Cetak
                            </a>

                            {{-- Export Excel 1/1 Khusus Catatan Material --}}
                            <a href="{{ route('project-notes.export-material', request()->all()) }}"
                               class="btn btn-xs"
                               style="background:#10b981;border:1px solid #059669;color:#ffffff;font-size:11.5px;font-weight:600;padding:6px 11px;border-radius:6px;display:inline-flex;align-items:center;gap:4px;"
                               title="Export Excel Catatan Material (.xlsx 1/1)">
                                <x-lucide-file-spreadsheet style="width:13px;height:13px;" /> Export Excel
                            </a>

                            {{-- Tambah Buku Catatan Material --}}
                            <button type="button" @click="showLedgerModal = true"
                                    class="btn btn-xs btn-primary"
                                    style="font-size:11.5px;font-weight:700;padding:6px 12px;border-radius:6px;display:inline-flex;align-items:center;gap:4px;">
                                <x-lucide-circle-plus style="width:13px;height:13px;" /> Catat Material
                            </button>
                        </div>
                    </div>

                    {{-- Tabel PO Nota Harian Design untuk Material Ledger --}}
                    <div class="table-wrap flex-1 overflow-x-auto" style="margin:0;">
                        <table class="data-table w-full" style="font-size:12px;margin:0;">
                            <thead>
                                <tr class="bg-slate-50/90 dark:bg-slate-800/80 border-b-2 border-slate-200 dark:border-slate-700 text-[11px] font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider">
                                    <th class="py-2.5 px-2 text-center" style="width:35px;">No</th>
                                    <th class="py-2.5 px-3">Judul Catatan & Proyek</th>
                                    <th class="py-2.5 px-3">Suplier</th>
                                    <th class="py-2.5 px-2 text-center" style="width:90px;">Vol. Deposit</th>
                                    <th class="py-2.5 px-2 text-center" style="width:90px;">Vol. Kirim</th>
                                    <th class="py-2.5 px-2 text-center" style="width:90px;">Sisa Kuota</th>
                                    <th class="py-2.5 px-2 text-end" style="width:95px;">Harga</th>
                                    <th class="py-2.5 px-3 text-end" style="width:115px;">Total Pemakaian</th>
                                    <th class="py-2.5 px-2 text-center" style="width:105px;">Surat Jalan</th>
                                    <th class="py-2.5 px-2 text-center" style="width:145px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                @forelse($ledgers as $lIdx => $ledger)
                                <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/50 transition">
                                    <td class="text-slate-400 dark:text-slate-500 text-center font-bold text-xs">{{ $lIdx + 1 }}</td>
                                    <td>
                                        <div>
                                            <a href="{{ route('project-notes.ledgers.show', $ledger) }}" 
                                               class="font-bold text-slate-900 dark:text-white hover:text-orange-600 dark:hover:text-orange-400 transition block leading-snug" 
                                               style="font-size:12.5px;" 
                                               title="Buka detail rincian surat jalan T1-T7">
                                                {{ $ledger->title }}
                                            </a>
                                        </div>
                                        <div class="flex items-center flex-wrap gap-x-2 gap-y-0.5 text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                                            <span class="inline-flex items-center gap-1 font-medium">
                                                <x-lucide-calendar class="w-3 h-3 text-slate-400 shrink-0" />
                                                <span>{{ $ledger->start_date ? $ledger->start_date->format('d/m/Y') : '-' }}</span>
                                                @if($ledger->end_date)
                                                    <span class="text-slate-300 dark:text-slate-600">–</span>
                                                    <span>{{ $ledger->end_date->format('d/m/Y') }}</span>
                                                @endif
                                            </span>
                                            @if(!$selectedProjectId && $ledger->project)
                                                <span class="text-slate-300 dark:text-slate-600">•</span>
                                                <span class="inline-flex items-center gap-1 text-slate-600 dark:text-slate-300 font-semibold">
                                                    <x-lucide-building-2 class="w-3 h-3 text-slate-400 shrink-0" />
                                                    <span>{{ $ledger->project->name }}</span>
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="font-bold text-slate-900 dark:text-slate-100 leading-tight">
                                            {{ $ledger->supplier_name }}
                                        </div>
                                    </td>
                                    <td class="text-center font-bold text-slate-800 dark:text-slate-100 whitespace-nowrap">
                                        {{ number_format($ledger->total_deposit_vol, 2, ',', '.') }}
                                        <span class="text-[10px] text-slate-400 font-normal ml-0.5">{{ $ledger->default_unit }}</span>
                                    </td>
                                    <td class="text-center font-bold text-blue-700 dark:text-blue-400 whitespace-nowrap">
                                        {{ number_format($ledger->total_delivered_vol, 2, ',', '.') }}
                                        <span class="text-[10px] text-slate-400 font-normal ml-0.5">{{ $ledger->default_unit }}</span>
                                    </td>
                                    <td class="text-center whitespace-nowrap">
                                        @if($ledger->remaining_deposit_vol > 0)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/80">
                                                {{ number_format($ledger->remaining_deposit_vol, 2, ',', '.') }}
                                                <span class="text-[9.5px] ml-0.5 font-normal opacity-80">{{ $ledger->default_unit }}</span>
                                            </span>
                                        @elseif($ledger->remaining_deposit_vol == 0)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                                                0,00
                                                <span class="text-[9.5px] ml-0.5 font-normal opacity-80">{{ $ledger->default_unit }}</span>
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-400 border border-rose-200 dark:border-rose-800/80">
                                                {{ number_format($ledger->remaining_deposit_vol, 2, ',', '.') }}
                                                <span class="text-[9.5px] ml-0.5 font-normal opacity-80">{{ $ledger->default_unit }}</span>
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-end whitespace-nowrap">
                                        @if((float)$ledger->default_unit_price > 0)
                                            <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">
                                                Rp {{ number_format($ledger->default_unit_price, 0, ',', '.') }}
                                            </span>
                                        @else
                                            <span class="text-slate-400 text-xs italic">-</span>
                                        @endif
                                    </td>
                                    <td class="text-end font-extrabold text-slate-900 dark:text-white whitespace-nowrap">
                                        @if((float)$ledger->total_used_amount > 0)
                                            Rp {{ number_format($ledger->total_used_amount, 0, ',', '.') }}
                                        @else
                                            <span class="text-slate-400 text-xs font-normal italic">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center whitespace-nowrap">
                                        <a href="{{ route('project-notes.ledgers.show', $ledger) }}"
                                           class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11.5px] font-bold bg-blue-50 dark:bg-blue-950/40 hover:bg-blue-100 dark:hover:bg-blue-900/40 text-blue-700 dark:text-blue-400 border border-blue-200/80 dark:border-blue-800/80 transition shadow-2xs"
                                           title="Buka Lembar Rincian {{ $ledger->entries->count() }} Surat Jalan">
                                            <x-lucide-truck class="w-3.5 h-3.5 text-blue-600 dark:text-blue-400" />
                                            <span>{{ $ledger->entries->count() }} Surat Jalan</span>
                                        </a>
                                    </td>
                                    <td class="text-center whitespace-nowrap">
                                        <div class="inline-flex items-center rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-2xs divide-x divide-slate-200 dark:divide-slate-700 overflow-hidden">
                                            {{-- Buka Detail Lembar Kerja --}}
                                            <a href="{{ route('project-notes.ledgers.show', $ledger) }}"
                                               class="p-1.5 text-slate-600 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-blue-50/60 dark:hover:bg-blue-950/40 transition"
                                               title="Buka Lembar Rincian T1-T7">
                                                <x-lucide-eye class="w-3.5 h-3.5" />
                                            </a>

                                            {{-- Edit Header Buku Catatan --}}
                                            <button type="button" 
                                                    @click="openEditLedger({{ json_encode($ledger) }})"
                                                    class="p-1.5 text-slate-600 dark:text-slate-400 hover:text-amber-600 dark:hover:text-amber-400 hover:bg-amber-50/60 dark:hover:bg-amber-950/40 transition"
                                                    title="Edit Header Buku Catatan">
                                                <x-lucide-pencil class="w-3.5 h-3.5" />
                                            </button>

                                            {{-- Cetak A4 Landscape --}}
                                            <a href="{{ route('project-notes.ledgers.print', $ledger) }}"
                                               target="_blank"
                                               class="p-1.5 text-slate-600 dark:text-slate-400 hover:text-orange-600 dark:hover:text-orange-400 hover:bg-orange-50/60 dark:hover:bg-orange-950/40 transition"
                                               title="Cetak Format A4 Landscape (1/1)">
                                                <x-lucide-printer class="w-3.5 h-3.5" />
                                            </a>

                                            {{-- Export Excel --}}
                                            <a href="{{ route('project-notes.ledgers.export', $ledger) }}"
                                               class="p-1.5 text-slate-600 dark:text-slate-400 hover:text-emerald-600 dark:hover:text-emerald-400 hover:bg-emerald-50/60 dark:hover:bg-emerald-950/40 transition"
                                               title="Export Excel Buku Ini (.xlsx 1/1)">
                                                <x-lucide-file-spreadsheet class="w-3.5 h-3.5" />
                                            </a>

                                            {{-- Hapus --}}
                                            <form method="POST" 
                                                  action="{{ route('project-notes.ledgers.destroy', $ledger) }}" 
                                                  onsubmit="return confirm('Hapus Buku Catatan {{ $ledger->title }}?')"
                                                  class="m-0 p-0 inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="p-1.5 text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-rose-50/60 dark:hover:bg-rose-950/40 transition" 
                                                        title="Hapus Buku Catatan">
                                                    <x-lucide-trash-2 class="w-3.5 h-3.5" />
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="10" class="text-center py-8 text-muted">
                                        <x-lucide-clipboard-list class="mx-auto mb-1 text-slate-400" style="width:28px;height:28px;" />
                                        <div class="font-semibold text-slate-600 dark:text-slate-300">Belum ada Buku Catatan Material yang tercatat.</div>
                                        <div class="text-xs text-slate-400 mt-0.5">Klik tombol <strong>Catat Material</strong> di atas untuk membuat buku baru.</div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                            @if($ledgers->isNotEmpty())
                            <tfoot>
                                <tr class="bg-slate-50 dark:bg-slate-800/80 font-bold border-t-2 border-slate-200 dark:border-slate-700 text-xs">
                                    <td colspan="3" class="text-end py-2.5 px-3 text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[11px] font-extrabold">
                                        TOTAL REKAPITULASI:
                                    </td>
                                    <td class="text-center py-2.5 px-2 font-bold text-slate-800 dark:text-slate-100 whitespace-nowrap">
                                        {{ number_format($totalDepositVol, 2, ',', '.') }}
                                    </td>
                                    <td class="text-center py-2.5 px-2 font-bold text-blue-700 dark:text-blue-400 whitespace-nowrap">
                                        {{ number_format($totalDeliveredVol, 2, ',', '.') }}
                                    </td>
                                    <td class="text-center py-2.5 px-2 font-bold text-slate-800 dark:text-slate-100 whitespace-nowrap">
                                        {{ number_format($totalRemainingVol, 2, ',', '.') }}
                                    </td>
                                    <td class="text-end py-2.5 px-2 text-slate-400 font-normal">-</td>
                                    <td class="text-end py-2.5 px-3 font-extrabold text-slate-900 dark:text-white whitespace-nowrap">
                                        Rp {{ number_format($totalMaterialCost, 0, ',', '.') }}
                                    </td>
                                    <td colspan="2" class="py-2.5"></td>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>

                </div>
            </div>

            {{-- ===================================================================== --}}
            {{-- PANEL KANAN: SEWA ALAT (5 Kolom pada layar XL)                        --}}
            {{-- ===================================================================== --}}
            <div :class="activeTab === 'rental' ? 'xl:col-span-12' : 'xl:col-span-5'" x-show="activeTab === 'all' || activeTab === 'rental'">
                <div class="card shadow-sm h-full flex flex-col" style="border:1.5px solid #cbd5e1;">
                    
                    {{-- Header Panel Kanan dengan Tombol Cetak 1/1, Export Excel 1/1, dan Tambah --}}
                    <div style="background:#f8fafc;padding:12px 18px;border-bottom:1.5px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 style="font-size:15px;font-weight:800;color:#0f172a;margin:0;letter-spacing:-0.2px;">
                                    SEWA ALAT & TIMESHEET
                                </h3>
                                <span class="badge" style="background:#ede9fe;color:#6d28d9;border:1px solid #ddd6fe;font-size:11px;padding:2px 8px;font-weight:700;">
                                    {{ $rentals->count() }} Alat
                                </span>
                            </div>
                            <p style="font-size:11.5px;color:#64748b;margin:2px 0 0 0;">
                                Rekap sewa alat kerja, armada, tarif, & timesheet harian operasional
                            </p>
                        </div>

                        <div class="flex items-center gap-2 flex-wrap">
                            {{-- Cetak 1/1 Khusus Sewa Alat --}}
                            <a href="{{ route('project-notes.print-rentals', request()->all()) }}"
                               target="_blank"
                               class="btn btn-xs"
                               style="background:#0f172a;border:1px solid #0f172a;color:#ffffff;font-size:11.5px;font-weight:600;padding:6px 11px;border-radius:6px;display:inline-flex;align-items:center;gap:4px;"
                               title="Cetak Rekap Sewa Alat (A4 Landscape 1/1)">
                                <x-lucide-printer style="width:13px;height:13px;" /> Cetak
                            </a>

                            {{-- Export Excel 1/1 Khusus Sewa Alat --}}
                            <a href="{{ route('project-notes.export-rentals', request()->all()) }}"
                               class="btn btn-xs"
                               style="background:#10b981;border:1px solid #059669;color:#ffffff;font-size:11.5px;font-weight:600;padding:6px 11px;border-radius:6px;display:inline-flex;align-items:center;gap:4px;"
                               title="Export Excel Rekap Sewa Alat (.xlsx 1/1)">
                                <x-lucide-file-spreadsheet style="width:13px;height:13px;" /> Export Excel
                            </a>

                            {{-- Catat Sewa Alat --}}
                            <button type="button" @click="showRentalModal = true"
                                    class="btn btn-xs"
                                    style="background:#4f46e5;border:1px solid #4338ca;color:#ffffff;font-size:11.5px;font-weight:700;padding:6px 12px;border-radius:6px;display:inline-flex;align-items:center;gap:4px;">
                                <x-lucide-truck style="width:13px;height:13px;" /> Catat Sewa
                            </button>
                        </div>
                    </div>

                    {{-- Tabel PO Nota Harian Design untuk Sewa Alat --}}
                    <div class="table-wrap flex-1 overflow-x-auto" style="margin:0;">
                        <table class="data-table w-full" style="font-size:12px;margin:0;">
                            <thead>
                                <tr class="bg-slate-50/90 dark:bg-slate-800/80 border-b-2 border-slate-200 dark:border-slate-700 text-[11px] font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider">
                                    <th class="py-2.5 px-2 text-center" style="width:30px;">No</th>
                                    <th class="py-2.5 px-3">Alat Kerja & Vendor</th>
                                    <th class="py-2.5 px-2 text-center" style="width:105px;">Tgl / Durasi</th>
                                    <th class="py-2.5 px-2 text-end" style="width:95px;">Tarif</th>
                                    <th class="py-2.5 px-3 text-end" style="width:110px;">Total Biaya</th>
                                    <th class="py-2.5 px-2 text-center" style="width:80px;">Status</th>
                                    <th class="py-2.5 px-2 text-center" style="width:100px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                @forelse($rentals as $rIdx => $rental)
                                <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/50 transition">
                                    <td class="text-slate-400 dark:text-slate-500 text-center font-bold text-xs">{{ $rIdx + 1 }}</td>
                                    <td>
                                        <div>
                                            <a href="{{ route('project-notes.rentals.show', $rental) }}"
                                               class="font-bold text-slate-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400 transition" 
                                               style="font-size:12.5px;"
                                               title="Buka Timesheet & Log Harian">
                                                {{ $rental->tool_name }}
                                            </a>
                                        </div>
                                        <div class="flex items-center flex-wrap gap-x-2 gap-y-0.5 text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                                            <span class="inline-flex items-center gap-1 font-medium text-slate-600 dark:text-slate-300">
                                                <x-lucide-building-2 class="w-3 h-3 text-slate-400 shrink-0" />
                                                <span>{{ $rental->vendor_name }}</span>
                                            </span>
                                            @if($rental->docket_number)
                                                <span class="text-slate-300 dark:text-slate-600">•</span>
                                                <span class="font-mono text-[10px] px-1.5 py-0.2 rounded bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                                    {{ $rental->docket_number }}
                                                </span>
                                            @endif
                                            @if($rental->logs->count() > 0)
                                                <span class="text-slate-300 dark:text-slate-600">•</span>
                                                <a href="{{ route('project-notes.rentals.show', $rental) }}"
                                                   class="inline-flex items-center gap-1 font-bold text-[10px] px-1.5 py-0.2 rounded bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 border border-indigo-200/80 dark:border-indigo-800 hover:bg-indigo-100 transition"
                                                   title="Lihat {{ $rental->logs->count() }} catatan timesheet">
                                                    <x-lucide-clock class="w-2.5 h-2.5 text-indigo-600 dark:text-indigo-400" />
                                                    <span>{{ $rental->logs->count() }} Log</span>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-center whitespace-nowrap">
                                        <div class="font-bold text-slate-800 dark:text-slate-100 text-xs">
                                            {{ (float)$rental->rental_duration }} {{ $rental->duration_unit }}
                                        </div>
                                        <div class="text-[10.5px] text-slate-500 dark:text-slate-400 mt-0.5">
                                            {{ $rental->start_date ? $rental->start_date->format('d/m/Y') : '-' }}
                                            @if($rental->end_date)
                                                – {{ $rental->end_date->format('d/m/Y') }}
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-end whitespace-nowrap">
                                        @if((float)$rental->rate_per_unit > 0)
                                            <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">
                                                Rp {{ number_format($rental->rate_per_unit, 0, ',', '.') }}
                                            </span>
                                        @else
                                            <span class="text-slate-400 text-xs italic">-</span>
                                        @endif
                                    </td>
                                    <td class="text-end font-extrabold text-indigo-700 dark:text-indigo-400 whitespace-nowrap">
                                        @if((float)$rental->total_rental_cost > 0)
                                            Rp {{ number_format($rental->total_rental_cost, 0, ',', '.') }}
                                        @else
                                            <span class="text-slate-400 text-xs font-normal italic">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center whitespace-nowrap">
                                        @if($rental->status === 'active')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800/80">
                                                AKTIF
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/80">
                                                SELESAI
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center whitespace-nowrap">
                                        <div class="inline-flex items-center rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-2xs divide-x divide-slate-200 dark:divide-slate-700 overflow-hidden">
                                            {{-- Buka Timesheet / Detail --}}
                                            <a href="{{ route('project-notes.rentals.show', $rental) }}"
                                               class="p-1.5 text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-indigo-50/60 dark:hover:bg-indigo-950/40 transition" 
                                               title="Buka Timesheet & Log Harian">
                                                <x-lucide-eye class="w-3.5 h-3.5" />
                                            </a>

                                            {{-- Tombol Edit Sewa Alat --}}
                                            <button type="button" 
                                                    @click="openEditRental({{ json_encode($rental) }})"
                                                    class="p-1.5 text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-indigo-50/60 dark:hover:bg-indigo-950/40 transition" 
                                                    title="Edit Data Sewa">
                                                <x-lucide-pencil class="w-3.5 h-3.5" />
                                            </button>

                                            {{-- Tombol Hapus Sewa Alat --}}
                                            <form method="POST" 
                                                  action="{{ route('project-notes.rentals.destroy', $rental) }}" 
                                                  onsubmit="return confirm('Hapus catatan sewa {{ $rental->tool_name }}?')"
                                                  class="m-0 p-0 inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="p-1.5 text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-rose-50/60 dark:hover:bg-rose-950/40 transition" 
                                                        title="Hapus Catatan Sewa">
                                                    <x-lucide-trash-2 class="w-3.5 h-3.5" />
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-8 text-muted">
                                        <x-lucide-truck class="mx-auto mb-1 text-slate-400" style="width:28px;height:28px;" />
                                        <div class="font-semibold text-slate-600 dark:text-slate-300">Belum ada catatan sewa alat kerja.</div>
                                        <div class="text-xs text-slate-400 mt-0.5">Klik tombol <strong> Catat Sewa</strong> di atas untuk menambahkan alat.</div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                            @if($rentals->isNotEmpty())
                            <tfoot>
                                <tr class="bg-slate-50 dark:bg-slate-800/80 font-bold border-t-2 border-slate-200 dark:border-slate-700 text-xs">
                                    <td colspan="4" class="text-end py-2.5 px-3 text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[11px] font-extrabold">
                                        TOTAL SEWA ALAT:
                                    </td>
                                    <td class="text-end py-2.5 px-3 font-extrabold text-indigo-700 dark:text-indigo-400 whitespace-nowrap">
                                        Rp {{ number_format($totalRentalCost, 0, ',', '.') }}
                                    </td>
                                    <td colspan="2" class="py-2.5"></td>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>

                </div>
            </div>

            {{-- ===================================================================== --}}
            {{-- PANEL: CATATAN MATERIAL CURAH LAPANGAN (PASIR, BATU, RITASE)          --}}
            {{-- ===================================================================== --}}
            <div class="xl:col-span-12" x-show="activeTab === 'all' || activeTab === 'curah'" x-cloak>
                <div class="card shadow-sm h-full flex flex-col" style="border:1.5px solid #cbd5e1;">
                    
                    {{-- Header Panel Curah --}}
                    <div style="background:#f8fafc;padding:12px 18px;border-bottom:1.5px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 style="font-size:15px;font-weight:800;color:#0f172a;margin:0;letter-spacing:-0.2px;">
                                    CATATAN MATERIAL CURAH
                                </h3>
                                <span class="badge" style="background:#fef3c7;color:#92400e;border:1px solid #fde68a;font-size:11px;padding:2px 8px;font-weight:700;">
                                    {{ $bulkMaterials->count() }} Rekap Suplier
                                </span>
                                @if($totalBulkRit > 0)
                                <span class="badge" style="background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0;font-size:11px;padding:2px 8px;font-weight:700;">
                                    Total {{ number_format($totalBulkRit, 0) }} Rit Terkirim
                                </span>
                                @endif
                            </div>
                            <p style="font-size:11.5px;color:#64748b;margin:2px 0 0 0;">
                                Pencatatan ritase material urug, pasir pasang/cor, batu belah/split harian dari suplier lokal
                            </p>
                        </div>

                        <div class="flex items-center gap-2 flex-wrap">
                            {{-- Tambah Catatan Curah --}}
                            <button type="button" @click="showBulkModal = true"
                                    class="btn btn-xs"
                                    style="background:#d97706;border:1px solid #b45309;color:#ffffff;font-size:11.5px;font-weight:700;padding:6px 12px;border-radius:6px;display:inline-flex;align-items:center;gap:4px;">
                                <x-lucide-circle-plus style="width:13px;height:13px;" /> Catat Material Curah
                            </button>
                        </div>
                    </div>

                    {{-- Tabel Material Curah --}}
                    <div class="table-wrap flex-1 overflow-x-auto" style="margin:0;">
                        <table class="data-table w-full" style="font-size:12px;margin:0;">
                            <thead>
                                <tr class="bg-slate-50/90 dark:bg-slate-800/80 border-b-2 border-slate-200 dark:border-slate-700 text-[11px] font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider">
                                    <th class="py-2.5 px-2 text-center" style="width:35px;">No</th>
                                    <th class="py-2.5 px-3">Judul Catatan & Proyek</th>
                                    <th class="py-2.5 px-3">Suplier</th>
                                    <th class="py-2.5 px-3 text-center" style="width:170px;">Periode Pengiriman</th>
                                    <th class="py-2.5 px-3">Ringkasan Ritase Per Jenis</th>
                                    <th class="py-2.5 px-2 text-center" style="width:90px;">Total Rit</th>
                                    <th class="py-2.5 px-2 text-center" style="width:145px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                @forelse($bulkMaterials as $bIdx => $bulk)
                                <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/50 transition">
                                    <td class="text-slate-400 dark:text-slate-500 text-center font-bold text-xs">{{ $bIdx + 1 }}</td>
                                    <td>
                                        <div>
                                            <a href="{{ route('project-notes.bulk.show', $bulk) }}" 
                                               class="font-bold text-slate-900 dark:text-white hover:text-amber-600 dark:hover:text-amber-400 transition block leading-snug" 
                                               style="font-size:12.5px;"
                                               title="Buka Lembar Rincian Ritase & Nota Harian">
                                                {{ $bulk->title }}
                                            </a>
                                            <div class="flex items-center gap-2 mt-0.5 text-[11px] text-slate-500">
                                                <span>{{ $bulk->project?->name ?? $bulk->project_name ?? 'Semua Proyek' }}</span>
                                                @if($bulk->notes)
                                                    <span class="text-slate-300">•</span>
                                                    <span class="truncate max-w-[200px]" title="{{ $bulk->notes }}">{{ $bulk->notes }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="font-bold text-slate-800 dark:text-slate-100 text-xs">
                                            {{ $bulk->supplier_name }}
                                        </div>
                                    </td>
                                    <td class="text-center text-xs text-slate-600 dark:text-slate-300 whitespace-nowrap">
                                        @if($bulk->start_date)
                                            <div class="inline-flex items-center gap-1 font-medium">
                                                <x-lucide-calendar class="w-3.5 h-3.5 text-slate-400" />
                                                <span>{{ $bulk->start_date->format('d/m/Y') }}</span>
                                                @if($bulk->end_date && $bulk->end_date != $bulk->start_date)
                                                    <span>–</span>
                                                    <span>{{ $bulk->end_date->format('d/m/Y') }}</span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-slate-400">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="flex flex-wrap items-center gap-1.5">
                                            @php
                                                $summary = $bulk->summary_by_type;
                                            @endphp
                                            @forelse($summary as $typeName => $info)
                                                <span class="inline-flex items-center gap-1 text-[11px] font-semibold px-2 py-0.5 rounded-md border
                                                    @if(str_contains(strtolower($typeName), 'pasir'))
                                                        bg-amber-50 text-amber-800 border-amber-200/80
                                                    @elseif(str_contains(strtolower($typeName), 'batu'))
                                                        bg-slate-100 text-slate-800 border-slate-300/80
                                                    @else
                                                        bg-blue-50 text-blue-800 border-blue-200/80
                                                    @endif">
                                                    {{ $info['total_qty'] }} {{ $info['unit'] }} {{ $typeName }}
                                                </span>
                                            @empty
                                                <span class="text-slate-400 text-xs italic">Belum ada item ritase</span>
                                            @endforelse
                                        </div>
                                    </td>
                                    <td class="text-center font-bold text-slate-900 dark:text-white text-xs whitespace-nowrap">
                                        <span class="inline-block px-2 py-0.5 rounded bg-amber-100/70 text-amber-900 font-extrabold text-xs">
                                            {{ number_format($bulk->total_rit, 0) }} Rit
                                        </span>
                                    </td>
                                    <td class="py-2.5 px-2 text-center">
                                        <div class="inline-flex items-center gap-0.5 bg-slate-100/80 dark:bg-slate-800/80 p-0.5 rounded-lg border border-slate-200/80 dark:border-slate-700/80">
                                            {{-- Buka Detail --}}
                                            <a href="{{ route('project-notes.bulk.show', $bulk) }}"
                                               class="p-1.5 text-slate-600 dark:text-slate-400 hover:text-amber-600 dark:hover:text-amber-400 hover:bg-white dark:hover:bg-slate-700 rounded-md transition"
                                               title="Buka Rincian Ritase & Nota Harian">
                                                <x-lucide-folder-open class="w-3.5 h-3.5" />
                                            </a>

                                            {{-- Edit Header --}}
                                            <button type="button"
                                                    @click="openEditBulk({{ json_encode($bulk) }})"
                                                    class="p-1.5 text-slate-600 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-white dark:hover:bg-slate-700 rounded-md transition"
                                                    title="Edit Data Catatan Curah">
                                                <x-lucide-pencil class="w-3.5 h-3.5" />
                                            </button>

                                            {{-- Cetak Format A4 --}}
                                            <a href="{{ route('project-notes.bulk.print', $bulk) }}"
                                               target="_blank"
                                               class="p-1.5 text-slate-600 dark:text-slate-400 hover:text-orange-600 dark:hover:text-orange-400 hover:bg-white dark:hover:bg-slate-700 rounded-md transition"
                                               title="Cetak Catatan Curah (A4 Landscape)">
                                                <x-lucide-printer class="w-3.5 h-3.5" />
                                            </a>

                                            {{-- Export Excel --}}
                                            <a href="{{ route('project-notes.bulk.export', $bulk) }}"
                                               class="p-1.5 text-slate-600 dark:text-slate-400 hover:text-emerald-600 dark:hover:text-emerald-400 hover:bg-white dark:hover:bg-slate-700 rounded-md transition"
                                               title="Export Excel Rekap (.xlsx)">
                                                <x-lucide-file-spreadsheet class="w-3.5 h-3.5" />
                                            </a>

                                            {{-- Hapus --}}
                                            <form method="POST" 
                                                  action="{{ route('project-notes.bulk.destroy', $bulk) }}" 
                                                  onsubmit="return confirm('Hapus Catatan Material Curah {{ $bulk->title }}?')"
                                                  class="m-0 p-0 inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="p-1.5 text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-white dark:hover:bg-slate-700 rounded-md transition" 
                                                        title="Hapus Catatan">
                                                    <x-lucide-trash-2 class="w-3.5 h-3.5" />
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-8 text-muted">
                                        <x-lucide-layers class="mx-auto mb-1 text-slate-400" style="width:28px;height:28px;" />
                                        <div class="font-semibold text-slate-600 dark:text-slate-300">Belum ada Catatan Material Curah (Pasir/Batu).</div>
                                        <div class="text-xs text-slate-400 mt-0.5">Klik tombol <strong>Catat Material Curah</strong> di atas untuk membuat rekap baru.</div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                            @if($bulkMaterials->isNotEmpty())
                            <tfoot>
                                <tr class="bg-slate-50 dark:bg-slate-800/80 font-bold border-t-2 border-slate-200 dark:border-slate-700 text-xs">
                                    <td colspan="5" class="text-end py-2.5 px-3 text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[11px] font-extrabold">
                                        TOTAL RITASE KESELURUHAN:
                                    </td>
                                    <td class="text-center py-2.5 px-2 font-black text-amber-700 dark:text-amber-400 whitespace-nowrap text-sm">
                                        {{ number_format($totalBulkRit, 0) }} Rit
                                    </td>
                                    <td class="py-2.5"></td>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>

                </div>
            </div>

            {{-- ===================================================================== --}}
            {{-- PANEL 4: REKAP PEMINDAHAN & MOBILISASI BARANG (SURAT JALAN)           --}}
            {{-- ===================================================================== --}}
            <div class="xl:col-span-12" x-show="activeTab === 'all' || activeTab === 'transfer'" x-cloak>
                <div class="card shadow-sm h-full flex flex-col" style="border:1.5px solid #cbd5e1;">
                    
                    {{-- Header Panel 3 --}}
                    <div style="background:#f8fafc;padding:12px 18px;border-bottom:1.5px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 style="font-size:15px;font-weight:800;color:#0f172a;margin:0;letter-spacing:-0.2px;">
                                    REKAP PEMINDAHAN & MOBILISASI BARANG
                                </h3>
                                <span class="badge" style="background:#ccfbf1;color:#0f766e;border:1px solid #99f6e4;font-size:11px;padding:2px 8px;font-weight:700;">
                                    {{ $transfers->count() }} Surat Jalan
                                </span>
                            </div>
                            <p style="font-size:11.5px;color:#64748b;margin:2px 0 0 0;">
                                Pencatatan mobilisasi peralatan proyek, balok beton, baja WF, & logistik antar lokasi proyek
                            </p>
                        </div>

                        <div class="flex items-center gap-2 flex-wrap">
                            {{-- Tambah Surat Jalan Pemindahan --}}
                            <button type="button" @click="showTransferModal = true"
                                    class="btn btn-xs"
                                    style="background:#0d9488;border:1px solid #0f766e;color:#ffffff;font-size:11.5px;font-weight:700;padding:6px 12px;border-radius:6px;display:inline-flex;align-items:center;gap:4px;">
                                <x-lucide-plus-circle style="width:13px;height:13px;" /> Surat Jalan Pemindahan
                            </button>
                        </div>
                    </div>

                    {{-- Tabel Mobilisasi / Pemindahan Barang --}}
                    <div class="table-wrap flex-1 overflow-x-auto" style="margin:0;">
                        <table class="data-table w-full" style="font-size:12px;margin:0;">
                            <thead>
                                <tr class="bg-slate-50/90 dark:bg-slate-800/80 border-b-2 border-slate-200 dark:border-slate-700 text-[11px] font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider">
                                    <th class="py-2.5 px-2 text-center" style="width:35px;">No</th>
                                    <th class="py-2.5 px-3">Surat Jalan & Judul Mobilisasi</th>
                                    <th class="py-2.5 px-3">Rute / Lokasi Asal & Tujuan</th>
                                    <th class="py-2.5 px-3">Driver / Pengangkut</th>
                                    <th class="py-2.5 px-2 text-center" style="width:130px;">Jumlah Barang</th>
                                    <th class="py-2.5 px-2 text-center" style="width:95px;">Status</th>
                                    <th class="py-2.5 px-2 text-center" style="width:110px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                @forelse($transfers as $tIdx => $transfer)
                                <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/50 transition">
                                    <td class="text-slate-400 dark:text-slate-500 text-center font-bold text-xs">{{ $tIdx + 1 }}</td>
                                    <td>
                                        <div>
                                            <a href="{{ route('project-notes.transfers.show', $transfer) }}" 
                                               class="font-bold text-slate-900 dark:text-white hover:text-teal-600 dark:hover:text-teal-400 transition block leading-snug" 
                                               style="font-size:12.5px;"
                                               title="Buka Lembar Detail Rincian Barang">
                                                {{ $transfer->title }}
                                            </a>
                                        </div>
                                        <div class="flex items-center flex-wrap gap-x-2 gap-y-0.5 text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                                            <span class="inline-flex items-center gap-1 font-medium">
                                                <x-lucide-calendar class="w-3 h-3 text-slate-400 shrink-0" />
                                                <span>{{ $transfer->transfer_date ? $transfer->transfer_date->format('d/m/Y') : '-' }}</span>
                                            </span>
                                            @if($transfer->docket_number)
                                                <span class="text-slate-300 dark:text-slate-600">•</span>
                                                <span class="font-mono text-[10px] px-1.5 py-0.2 rounded bg-teal-50 dark:bg-teal-950/40 text-teal-800 dark:text-teal-300 border border-teal-200/80 dark:border-teal-800 font-bold">
                                                    {{ $transfer->docket_number }}
                                                </span>
                                            @endif
                                            @if($transfer->project)
                                                <span class="text-slate-300 dark:text-slate-600">•</span>
                                                <span class="inline-flex items-center gap-1 text-slate-600 dark:text-slate-300 font-semibold">
                                                    <x-lucide-building-2 class="w-3 h-3 text-slate-400 shrink-0" />
                                                    <span>{{ $transfer->project->name }}</span>
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="flex items-center gap-1.5 text-xs text-slate-800 dark:text-slate-200 font-medium">
                                            <span class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-semibold text-[11px]">
                                                {{ $transfer->origin_location ?: 'Gudang / Workshop' }}
                                            </span>
                                            <x-lucide-arrow-right class="w-3.5 h-3.5 text-teal-600 shrink-0" />
                                            <span class="px-1.5 py-0.5 rounded bg-teal-50 dark:bg-teal-950/40 text-teal-800 dark:text-teal-300 font-bold text-[11px] border border-teal-200/60 dark:border-teal-900">
                                                {{ $transfer->destination_location ?: 'Lokasi Proyek' }}
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="font-semibold text-slate-900 dark:text-slate-100 text-xs">
                                            {{ $transfer->driver_or_transporter ?: '-' }}
                                        </div>
                                    </td>
                                    <td class="text-center whitespace-nowrap">
                                        <a href="{{ route('project-notes.transfers.show', $transfer) }}"
                                           class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11.5px] font-bold bg-teal-50 dark:bg-teal-950/40 hover:bg-teal-100 dark:hover:bg-teal-900/40 text-teal-700 dark:text-teal-300 border border-teal-200/80 dark:border-teal-800/80 transition shadow-2xs">
                                            <x-lucide-package-check class="w-3.5 h-3.5 text-teal-600 dark:text-teal-400" />
                                            <span>{{ $transfer->items->count() }} Item / Barang</span>
                                        </a>
                                    </td>
                                    <td class="text-center whitespace-nowrap">
                                        @if($transfer->status === 'completed' || $transfer->status === 'received')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/80">
                                                DITERIMA
                                            </span>
                                        @elseif($transfer->status === 'in_transit')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800/80">
                                                DI PERJALANAN
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                                                DRAFT
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center whitespace-nowrap">
                                        <div class="inline-flex items-center rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-2xs divide-x divide-slate-200 dark:divide-slate-700 overflow-hidden">
                                            {{-- Buka Detail Surat Jalan Pemindahan --}}
                                            <a href="{{ route('project-notes.transfers.show', $transfer) }}"
                                               class="p-1.5 text-slate-600 dark:text-slate-400 hover:text-teal-600 dark:hover:text-teal-400 hover:bg-teal-50/60 dark:hover:bg-teal-950/40 transition" 
                                               title="Buka Lembar Detail Rincian Barang">
                                                <x-lucide-eye class="w-3.5 h-3.5" />
                                            </a>

                                            {{-- Edit Surat Jalan --}}
                                            <button type="button" 
                                                    @click="openEditTransfer({{ json_encode($transfer) }})"
                                                    class="p-1.5 text-slate-600 dark:text-slate-400 hover:text-amber-600 dark:hover:text-amber-400 hover:bg-amber-50/60 dark:hover:bg-amber-950/40 transition" 
                                                    title="Edit Data Surat Jalan">
                                                <x-lucide-pencil class="w-3.5 h-3.5" />
                                            </button>

                                            {{-- Hapus Surat Jalan --}}
                                            <form method="POST" 
                                                  action="{{ route('project-notes.transfers.destroy', $transfer) }}" 
                                                  onsubmit="return confirm('Hapus surat jalan pemindahan {{ $transfer->title }}?')"
                                                  class="m-0 p-0 inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="p-1.5 text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-rose-50/60 dark:hover:bg-rose-950/40 transition" 
                                                        title="Hapus Surat Jalan">
                                                    <x-lucide-trash-2 class="w-3.5 h-3.5" />
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-8 text-muted">
                                        <x-lucide-arrow-right-left class="mx-auto mb-1 text-slate-400" style="width:28px;height:28px;" />
                                        <div class="font-semibold text-slate-600 dark:text-slate-300">Belum ada surat jalan pemindahan / mobilisasi barang.</div>
                                        <div class="text-xs text-slate-400 mt-0.5">Klik tombol <strong>Surat Jalan Pemindahan</strong> di atas untuk mencatat perpindahan logistik.</div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>

        </div>

        {{-- ========================================================================= --}}
        {{-- MODAL: TAMBAH BUKU CATATAN MATERIAL (HARGA BISA KOSONG)                   --}}
        {{-- ========================================================================= --}}
        <div x-show="showLedgerModal" x-cloak class="fixed inset-0 flex items-center justify-center bg-slate-950/65 backdrop-blur-xs p-3 sm:p-4 overflow-y-auto" style="z-index: 9999;">
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-2xl w-full overflow-hidden my-auto flex flex-col" style="max-width: 520px;" @click.away="showLedgerModal = false">
                
                {{-- 1. Header Bar (Compact) --}}
                <div class="px-4 py-2.5 bg-slate-50/90 dark:bg-slate-800/80 border-b border-slate-200/80 dark:border-slate-800 flex items-center justify-between shrink-0">
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-lg bg-orange-500/10 text-orange-600 dark:text-orange-400 flex items-center justify-center border border-orange-200/70 dark:border-orange-800/70 shadow-2xs shrink-0">
                            <x-lucide-folder-plus class="w-3.5 h-3.5" />
                        </div>
                        <div>
                            <h3 class="text-xs font-extrabold text-slate-900 dark:text-white tracking-tight">
                                Buat Buku Catatan Material Baru
                            </h3>
                            <p class="text-[10px] text-slate-500 dark:text-slate-400">
                                Rekapitulasi volume & catatan material suplier proyek
                            </p>
                        </div>
                    </div>
                    <button type="button" @click="showLedgerModal = false" class="w-6 h-6 rounded-md bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-400 hover:text-slate-600 dark:text-slate-400 dark:hover:text-slate-200 flex items-center justify-center transition shadow-2xs" title="Tutup">
                        <x-lucide-x class="w-3.5 h-3.5" />
                    </button>
                </div>

                {{-- 2. Form with Scrollable Body & Dedicated Footer (Compact) --}}
                <form method="POST" action="{{ route('project-notes.ledgers.store') }}" class="flex flex-col m-0">
                    @csrf
                    
                    {{-- Body --}}
                    <div class="p-4 space-y-2.5 max-h-[75vh] overflow-y-auto">
                        
                        {{-- Bagian 1: Proyek & Judul --}}
                        <div class="space-y-2">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                    Proyek Target <span class="text-rose-500 font-bold">*</span>
                                </label>
                                <select name="project_id" class="w-full text-xs h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 hover:bg-white focus:bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-medium transition focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 shadow-2xs" required>
                                    @foreach($projects as $p)
                                        <option value="{{ $p->id }}" {{ $selectedProjectId == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                    Judul Buku Catatan <span class="text-rose-500 font-bold">*</span>
                                </label>
                                <input type="text" name="title" class="w-full text-xs h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 hover:bg-white focus:bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-medium placeholder-slate-400 transition focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 shadow-2xs" placeholder="Contoh: CATATAN MATERIAL COR READYMIX FAK.TEKNIK UGM" required>
                            </div>
                        </div>

                        {{-- Divider: Suplier & Satuan --}}
                        <div class="relative py-0.5">
                            <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-slate-200/70 dark:border-slate-800"></div></div>
                            <div class="relative flex justify-start"><span class="bg-white dark:bg-slate-900 pr-2 text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Suplier & Satuan</span></div>
                        </div>

                        {{-- Bagian 2: Suplier, Satuan & Harga --}}
                        <div class="space-y-2">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                        Nama Suplier <span class="text-rose-500 font-bold">*</span>
                                    </label>
                                    <input type="text" name="supplier_name" class="w-full text-xs h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 hover:bg-white focus:bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-medium placeholder-slate-400 transition focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 shadow-2xs" placeholder="Contoh: PT. Solusi Bangun Beton (Dynamix)" required>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                        Penerima <span class="text-[10px] font-normal text-slate-400">(Opsional)</span>
                                    </label>
                                    <input type="text" name="account_behalf" class="w-full text-xs h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 hover:bg-white focus:bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-medium placeholder-slate-400 transition focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 shadow-2xs" placeholder="Contoh: PBAG">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                        Satuan Default <span class="text-rose-500 font-bold">*</span>
                                    </label>
                                    <input type="text" name="default_unit" value="m3" class="w-full text-xs h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 hover:bg-white focus:bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-medium placeholder-slate-400 transition focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 shadow-2xs" placeholder="m3, kg, zak..." required>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                        Harga Satuan Standar <span class="text-[10px] font-normal text-slate-400">(Opsional)</span>
                                    </label>
                                    <div class="flex rounded-lg overflow-hidden border border-slate-200 dark:border-slate-700 bg-slate-50/60 hover:bg-white focus-within:bg-white dark:bg-slate-800 shadow-2xs transition focus-within:ring-2 focus-within:ring-orange-500/20 focus-within:border-orange-500">
                                        <span class="inline-flex items-center px-2.5 bg-slate-100/90 dark:bg-slate-700/60 text-slate-500 dark:text-slate-400 text-[11px] font-bold border-r border-slate-200 dark:border-slate-700 select-none">
                                            Rp
                                        </span>
                                        <input type="number" step="any" min="0" name="default_unit_price" class="w-full text-xs h-8 px-2.5 bg-transparent border-0 focus:ring-0 focus:outline-none text-slate-800 dark:text-slate-100 font-medium placeholder-slate-400" placeholder="0">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Divider: Jadwal & Catatan --}}
                        <div class="relative py-0.5">
                            <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-slate-200/70 dark:border-slate-800"></div></div>
                            <div class="relative flex justify-start"><span class="bg-white dark:bg-slate-900 pr-2 text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Jadwal & Catatan</span></div>
                        </div>

                        {{-- Bagian 3: Jadwal & Catatan --}}
                        <div class="space-y-2">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                        Tanggal Mulai <span class="text-rose-500 font-bold">*</span>
                                    </label>
                                    <input type="date" name="start_date" value="{{ date('Y-m-d') }}" class="w-full text-xs h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 hover:bg-white focus:bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-medium transition focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 shadow-2xs" required>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                        Tanggal Selesai <span class="text-[10px] font-normal text-slate-400">(Opsional)</span>
                                    </label>
                                    <input type="date" name="end_date" class="w-full text-xs h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 hover:bg-white focus:bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-medium transition focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 shadow-2xs">
                                </div>
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                    Catatan Tambahan <span class="text-[10px] font-normal text-slate-400">(Opsional)</span>
                                </label>
                                <input type="text" name="notes" class="w-full text-xs h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 hover:bg-white focus:bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-medium placeholder-slate-400 transition focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 shadow-2xs" placeholder="Keterangan pekerjaan atau area pengecoran...">
                            </div>
                        </div>

                    </div>

                    {{-- 3. Dedicated Footer Bar (Compact) --}}
                    <div class="px-4 py-2 bg-slate-50 dark:bg-slate-800/80 border-t border-slate-200/80 dark:border-slate-800 flex items-center justify-end gap-2 shrink-0">
                        <button type="button" @click="showLedgerModal = false" class="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold transition shadow-2xs">
                            Batal
                        </button>
                        <button type="submit" class="px-3.5 py-1.5 rounded-lg bg-orange-600 hover:bg-orange-500 active:bg-orange-700 text-white text-xs font-bold transition shadow-sm shadow-orange-600/30 inline-flex items-center gap-1.5">
                            <x-lucide-check class="w-3.5 h-3.5" />
                            <span>Simpan Buku Catatan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- MODAL: EDIT BUKU CATATAN MATERIAL (HARGA BISA KOSONG)                     --}}
        {{-- ========================================================================= --}}
        <div x-show="showEditLedgerModal" x-cloak class="fixed inset-0 flex items-center justify-center bg-slate-950/65 backdrop-blur-xs p-3 sm:p-4 overflow-y-auto" style="z-index: 9999;">
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-2xl w-full overflow-hidden my-auto flex flex-col" style="max-width: 520px;" @click.away="showEditLedgerModal = false">
                
                {{-- 1. Header Bar (Compact) --}}
                <div class="px-4 py-2.5 bg-slate-50/90 dark:bg-slate-800/80 border-b border-slate-200/80 dark:border-slate-800 flex items-center justify-between shrink-0">
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-lg bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center border border-amber-200/70 dark:border-amber-800/70 shadow-2xs shrink-0">
                            <x-lucide-pencil class="w-3.5 h-3.5" />
                        </div>
                        <div>
                            <h3 class="text-xs font-extrabold text-slate-900 dark:text-white tracking-tight">
                                Edit Buku Catatan Material
                            </h3>
                            <p class="text-[10px] text-slate-500 dark:text-slate-400">
                                Perbarui data buku catatan rekapitulasi material proyek
                            </p>
                        </div>
                    </div>
                    <button type="button" @click="showEditLedgerModal = false" class="w-6 h-6 rounded-md bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-400 hover:text-slate-600 dark:text-slate-400 dark:hover:text-slate-200 flex items-center justify-center transition shadow-2xs" title="Tutup">
                        <x-lucide-x class="w-3.5 h-3.5" />
                    </button>
                </div>

                {{-- 2. Form with Scrollable Body & Dedicated Footer (Compact) --}}
                <form method="POST" :action="`{{ url('/project-notes/ledgers') }}/${editLedger.id}`" class="flex flex-col m-0">
                    @csrf
                    @method('PUT')

                    {{-- Body --}}
                    <div class="p-4 space-y-2.5 max-h-[75vh] overflow-y-auto">
                        
                        {{-- Bagian 1: Judul --}}
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                Judul Buku Catatan <span class="text-rose-500 font-bold">*</span>
                            </label>
                            <input type="text" name="title" x-model="editLedger.title" class="w-full text-xs h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 hover:bg-white focus:bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-medium placeholder-slate-400 transition focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 shadow-2xs" required>
                        </div>

                        {{-- Divider --}}
                        <div class="relative py-0.5">
                            <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-slate-200/70 dark:border-slate-800"></div></div>
                            <div class="relative flex justify-start"><span class="bg-white dark:bg-slate-900 pr-2 text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Suplier & Satuan</span></div>
                        </div>

                        {{-- Bagian 2: Suplier, Satuan & Harga --}}
                        <div class="space-y-2">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                        Nama Suplier <span class="text-rose-500 font-bold">*</span>
                                    </label>
                                    <input type="text" name="supplier_name" x-model="editLedger.supplier_name" class="w-full text-xs h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 hover:bg-white focus:bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-medium placeholder-slate-400 transition focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 shadow-2xs" required>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                        Penerima <span class="text-[10px] font-normal text-slate-400">(Opsional)</span>
                                    </label>
                                    <input type="text" name="account_behalf" x-model="editLedger.account_behalf" class="w-full text-xs h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 hover:bg-white focus:bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-medium placeholder-slate-400 transition focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 shadow-2xs">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                        Satuan Default <span class="text-rose-500 font-bold">*</span>
                                    </label>
                                    <input type="text" name="default_unit" x-model="editLedger.default_unit" class="w-full text-xs h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 hover:bg-white focus:bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-medium placeholder-slate-400 transition focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 shadow-2xs" required>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                        Harga Satuan Standar <span class="text-[10px] font-normal text-slate-400">(Opsional)</span>
                                    </label>
                                    <div class="flex rounded-lg overflow-hidden border border-slate-200 dark:border-slate-700 bg-slate-50/60 hover:bg-white focus-within:bg-white dark:bg-slate-800 shadow-2xs transition focus-within:ring-2 focus-within:ring-orange-500/20 focus-within:border-orange-500">
                                        <span class="inline-flex items-center px-2.5 bg-slate-100/90 dark:bg-slate-700/60 text-slate-500 dark:text-slate-400 text-[11px] font-bold border-r border-slate-200 dark:border-slate-700 select-none">
                                            Rp
                                        </span>
                                        <input type="number" step="any" min="0" name="default_unit_price" x-model="editLedger.default_unit_price" class="w-full text-xs h-8 px-2.5 bg-transparent border-0 focus:ring-0 focus:outline-none text-slate-800 dark:text-slate-100 font-medium placeholder-slate-400" placeholder="0">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Divider --}}
                        <div class="relative py-0.5">
                            <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-slate-200/70 dark:border-slate-800"></div></div>
                            <div class="relative flex justify-start"><span class="bg-white dark:bg-slate-900 pr-2 text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Jadwal & Catatan</span></div>
                        </div>

                        {{-- Bagian 3: Jadwal & Catatan --}}
                        <div class="space-y-2">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                        Tanggal Mulai <span class="text-rose-500 font-bold">*</span>
                                    </label>
                                    <input type="date" name="start_date" x-model="editLedger.start_date" class="w-full text-xs h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 hover:bg-white focus:bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-medium transition focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 shadow-2xs" required>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                        Tanggal Selesai <span class="text-[10px] font-normal text-slate-400">(Opsional)</span>
                                    </label>
                                    <input type="date" name="end_date" x-model="editLedger.end_date" class="w-full text-xs h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 hover:bg-white focus:bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-medium transition focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 shadow-2xs">
                                </div>
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                    Catatan Tambahan <span class="text-[10px] font-normal text-slate-400">(Opsional)</span>
                                </label>
                                <input type="text" name="notes" x-model="editLedger.notes" class="w-full text-xs h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 hover:bg-white focus:bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-medium placeholder-slate-400 transition focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 shadow-2xs" placeholder="Keterangan pekerjaan atau area pengecoran...">
                            </div>
                        </div>

                    </div>

                    {{-- 3. Dedicated Footer Bar (Compact) --}}
                    <div class="px-4 py-2 bg-slate-50 dark:bg-slate-800/80 border-t border-slate-200/80 dark:border-slate-800 flex items-center justify-end gap-2 shrink-0">
                        <button type="button" @click="showEditLedgerModal = false" class="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold transition shadow-2xs">
                            Batal
                        </button>
                        <button type="submit" class="px-3.5 py-1.5 rounded-lg bg-orange-600 hover:bg-orange-500 active:bg-orange-700 text-white text-xs font-bold transition shadow-sm shadow-orange-600/30 inline-flex items-center gap-1.5">
                            <x-lucide-check class="w-3.5 h-3.5" />
                            <span>Perbarui Buku Catatan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- MODAL: TAMBAH SEWA ALAT BARU (HARGA BISA KOSONG)                          --}}
        {{-- ========================================================================= --}}
        <div x-show="showRentalModal" x-cloak class="fixed inset-0 flex items-center justify-center bg-slate-950/65 backdrop-blur-xs p-3 sm:p-4 overflow-y-auto" style="z-index: 9999;">
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-2xl w-full overflow-hidden my-auto flex flex-col" style="max-width: 520px;" @click.away="showRentalModal = false">
                
                {{-- 1. Header Bar (Compact) --}}
                <div class="px-4 py-2.5 bg-slate-50/90 dark:bg-slate-800/80 border-b border-slate-200/80 dark:border-slate-800 flex items-center justify-between shrink-0">
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-lg bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center border border-indigo-200/70 dark:border-indigo-800/70 shadow-2xs shrink-0">
                            <x-lucide-truck class="w-3.5 h-3.5" />
                        </div>
                        <div>
                            <h3 class="text-xs font-extrabold text-slate-900 dark:text-white tracking-tight">
                                Catat Sewa Alat Baru
                            </h3>
                            <p class="text-[10px] text-slate-500 dark:text-slate-400">
                                Durasi operasional, tarif sewa, & status unit alat kerja
                            </p>
                        </div>
                    </div>
                    <button type="button" @click="showRentalModal = false" class="w-6 h-6 rounded-md bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-400 hover:text-slate-600 dark:text-slate-400 dark:hover:text-slate-200 flex items-center justify-center transition shadow-2xs" title="Tutup">
                        <x-lucide-x class="w-3.5 h-3.5" />
                    </button>
                </div>

                {{-- 2. Form with Scrollable Body & Dedicated Footer (Compact) --}}
                <form method="POST" action="{{ route('project-notes.rentals.store') }}" class="flex flex-col m-0">
                    @csrf
                    
                    {{-- Body --}}
                    <div class="p-4 space-y-2.5 max-h-[75vh] overflow-y-auto">
                        
                        {{-- Bagian 1: Proyek & Alat --}}
                        <div class="space-y-2">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                    Proyek Target <span class="text-rose-500 font-bold">*</span>
                                </label>
                                <select name="project_id" class="w-full text-xs h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 hover:bg-white focus:bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-medium transition focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-2xs" required>
                                    @foreach($projects as $p)
                                        <option value="{{ $p->id }}" {{ $selectedProjectId == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                        Nama Alat Kerja / Armada <span class="text-rose-500 font-bold">*</span>
                                    </label>
                                    <input type="text" name="tool_name" class="w-full text-xs h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 hover:bg-white focus:bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-medium placeholder-slate-400 transition focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-2xs" placeholder="Contoh: Concrete Pump Standar" required>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                        Vendor Penyedia <span class="text-rose-500 font-bold">*</span>
                                    </label>
                                    <input type="text" name="vendor_name" class="w-full text-xs h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 hover:bg-white focus:bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-medium placeholder-slate-400 transition focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-2xs" placeholder="Contoh: PT. Solusi Bangun Beton" required>
                                </div>
                            </div>
                        </div>

                        {{-- Divider --}}
                        <div class="relative py-0.5">
                            <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-slate-200/70 dark:border-slate-800"></div></div>
                            <div class="relative flex justify-start"><span class="bg-white dark:bg-slate-900 pr-2 text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Kontrak & Durasi Sewa</span></div>
                        </div>

                        {{-- Bagian 2: Kontrak & Durasi --}}
                        <div class="space-y-2">
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                        No. Bukti / Surat <span class="text-[10px] font-normal text-slate-400">(Opsional)</span>
                                    </label>
                                    <input type="text" name="docket_number" class="w-full text-xs h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 hover:bg-white focus:bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-medium placeholder-slate-400 transition focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-2xs" placeholder="CP-UGM-20260922">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                        Tanggal Mulai <span class="text-rose-500 font-bold">*</span>
                                    </label>
                                    <input type="date" name="start_date" value="{{ date('Y-m-d') }}" class="w-full text-xs h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 hover:bg-white focus:bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-medium transition focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-2xs" required>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                        Tanggal Selesai <span class="text-[10px] font-normal text-slate-400">(Opsional)</span>
                                    </label>
                                    <input type="date" name="end_date" class="w-full text-xs h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 hover:bg-white focus:bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-medium transition focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-2xs">
                                </div>
                            </div>

                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                        Durasi <span class="text-rose-500 font-bold">*</span>
                                    </label>
                                    <input type="number" step="any" min="0.5" name="rental_duration" value="1" class="w-full text-xs h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 hover:bg-white focus:bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-medium transition focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-2xs" required>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                        Satuan Durasi <span class="text-rose-500 font-bold">*</span>
                                    </label>
                                    <select name="duration_unit" class="w-full text-xs h-8 px-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 hover:bg-white focus:bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-medium transition focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-2xs" required>
                                        <option value="Shift">Shift</option>
                                        <option value="Hari" selected>Hari</option>
                                        <option value="Jam">Jam</option>
                                        <option value="Bulan">Bulan</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                        Tarif / Satuan <span class="text-[10px] font-normal text-slate-400">(Opsional)</span>
                                    </label>
                                    <div class="flex rounded-lg overflow-hidden border border-slate-200 dark:border-slate-700 bg-slate-50/60 hover:bg-white focus-within:bg-white dark:bg-slate-800 shadow-2xs transition focus-within:ring-2 focus-within:ring-indigo-500/20 focus-within:border-indigo-500">
                                        <span class="inline-flex items-center px-2.5 bg-slate-100/90 dark:bg-slate-700/60 text-slate-500 dark:text-slate-400 text-[11px] font-bold border-r border-slate-200 dark:border-slate-700 select-none">
                                            Rp
                                        </span>
                                        <input type="number" step="any" min="0" name="rate_per_unit" class="w-full text-xs h-8 px-2 bg-transparent border-0 focus:ring-0 focus:outline-none text-slate-800 dark:text-slate-100 font-medium placeholder-slate-400" placeholder="0">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                        Deposit / DP <span class="text-[10px] font-normal text-slate-400">(Opsional)</span>
                                    </label>
                                    <div class="flex rounded-lg overflow-hidden border border-slate-200 dark:border-slate-700 bg-slate-50/60 hover:bg-white focus-within:bg-white dark:bg-slate-800 shadow-2xs transition focus-within:ring-2 focus-within:ring-indigo-500/20 focus-within:border-indigo-500">
                                        <span class="inline-flex items-center px-2.5 bg-slate-100/90 dark:bg-slate-700/60 text-slate-500 dark:text-slate-400 text-[11px] font-bold border-r border-slate-200 dark:border-slate-700 select-none">
                                            Rp
                                        </span>
                                        <input type="number" step="any" min="0" name="deposit_amount" value="0" class="w-full text-xs h-8 px-2 bg-transparent border-0 focus:ring-0 focus:outline-none text-slate-800 dark:text-slate-100 font-medium placeholder-slate-400" placeholder="0">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Divider --}}
                        <div class="relative py-0.5">
                            <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-slate-200/70 dark:border-slate-800"></div></div>
                            <div class="relative flex justify-start"><span class="bg-white dark:bg-slate-900 pr-2 text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Status & Catatan</span></div>
                        </div>

                        {{-- Bagian 3: Status & Catatan --}}
                        <div class="space-y-2">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                        Status Sewa <span class="text-rose-500 font-bold">*</span>
                                    </label>
                                    <select name="status" class="w-full text-xs h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 hover:bg-white focus:bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-medium transition focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-2xs" required>
                                        <option value="active">Aktif (Sedang Beroperasi)</option>
                                        <option value="returned">Selesai (Sudah Dikembalikan)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                        Catatan Sewa <span class="text-[10px] font-normal text-slate-400">(Opsional)</span>
                                    </label>
                                    <input type="text" name="notes" class="w-full text-xs h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 hover:bg-white focus:bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-medium placeholder-slate-400 transition focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-2xs" placeholder="Area kerja, operator, dll.">
                                </div>
                            </div>
                        </div>

                    </div>

                    {{-- 3. Dedicated Footer Bar (Compact) --}}
                    <div class="px-4 py-2 bg-slate-50 dark:bg-slate-800/80 border-t border-slate-200/80 dark:border-slate-800 flex items-center justify-end gap-2 shrink-0">
                        <button type="button" @click="showRentalModal = false" class="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold transition shadow-2xs">
                            Batal
                        </button>
                        <button type="submit" class="px-3.5 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700 text-white text-xs font-bold transition shadow-sm shadow-indigo-600/30 inline-flex items-center gap-1.5">
                            <x-lucide-check class="w-3.5 h-3.5" />
                            <span>Simpan Sewa Alat</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- MODAL: EDIT SEWA ALAT (HARGA BISA KOSONG)                                 --}}
        {{-- ========================================================================= --}}
        <div x-show="showEditRentalModal" x-cloak class="fixed inset-0 flex items-center justify-center bg-slate-950/65 backdrop-blur-xs p-3 sm:p-4 overflow-y-auto" style="z-index: 9999;">
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-2xl w-full overflow-hidden my-auto flex flex-col" style="max-width: 520px;" @click.away="showEditRentalModal = false">
                
                {{-- 1. Header Bar (Compact) --}}
                <div class="px-4 py-2.5 bg-slate-50/90 dark:bg-slate-800/80 border-b border-slate-200/80 dark:border-slate-800 flex items-center justify-between shrink-0">
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-lg bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center border border-indigo-200/70 dark:border-indigo-800/70 shadow-2xs shrink-0">
                            <x-lucide-pencil class="w-3.5 h-3.5" />
                        </div>
                        <div>
                            <h3 class="text-xs font-extrabold text-slate-900 dark:text-white tracking-tight">
                                Edit Data Sewa Alat
                            </h3>
                            <p class="text-[10px] text-slate-500 dark:text-slate-400">
                                Perbarui informasi masa sewa, tarif, & status operasional unit
                            </p>
                        </div>
                    </div>
                    <button type="button" @click="showEditRentalModal = false" class="w-6 h-6 rounded-md bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-400 hover:text-slate-600 dark:text-slate-400 dark:hover:text-slate-200 flex items-center justify-center transition shadow-2xs" title="Tutup">
                        <x-lucide-x class="w-3.5 h-3.5" />
                    </button>
                </div>

                {{-- 2. Form with Scrollable Body & Dedicated Footer (Compact) --}}
                <form method="POST" :action="`{{ url('/project-notes/rentals') }}/${editRental.id}`" class="flex flex-col m-0">
                    @csrf
                    @method('PUT')

                    {{-- Body --}}
                    <div class="p-4 space-y-2.5 max-h-[75vh] overflow-y-auto">
                        
                        {{-- Bagian 1: Alat & Vendor --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                    Nama Alat Kerja / Armada <span class="text-rose-500 font-bold">*</span>
                                </label>
                                <input type="text" name="tool_name" x-model="editRental.tool_name" class="w-full text-xs h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 hover:bg-white focus:bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-medium placeholder-slate-400 transition focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-2xs" required>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                    Vendor Penyedia <span class="text-rose-500 font-bold">*</span>
                                </label>
                                <input type="text" name="vendor_name" x-model="editRental.vendor_name" class="w-full text-xs h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 hover:bg-white focus:bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-medium placeholder-slate-400 transition focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-2xs" required>
                            </div>
                        </div>

                        {{-- Divider --}}
                        <div class="relative py-0.5">
                            <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-slate-200/70 dark:border-slate-800"></div></div>
                            <div class="relative flex justify-start"><span class="bg-white dark:bg-slate-900 pr-2 text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Kontrak & Durasi Sewa</span></div>
                        </div>

                        {{-- Bagian 2: Kontrak & Durasi --}}
                        <div class="space-y-2">
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                        No. Bukti / Surat <span class="text-[10px] font-normal text-slate-400">(Opsional)</span>
                                    </label>
                                    <input type="text" name="docket_number" x-model="editRental.docket_number" class="w-full text-xs h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 hover:bg-white focus:bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-medium placeholder-slate-400 transition focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-2xs">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                        Tanggal Mulai <span class="text-rose-500 font-bold">*</span>
                                    </label>
                                    <input type="date" name="start_date" x-model="editRental.start_date" class="w-full text-xs h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 hover:bg-white focus:bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-medium transition focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-2xs" required>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                        Tanggal Selesai <span class="text-[10px] font-normal text-slate-400">(Opsional)</span>
                                    </label>
                                    <input type="date" name="end_date" x-model="editRental.end_date" class="w-full text-xs h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 hover:bg-white focus:bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-medium transition focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-2xs">
                                </div>
                            </div>

                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                        Durasi <span class="text-rose-500 font-bold">*</span>
                                    </label>
                                    <input type="number" step="any" min="0.5" name="rental_duration" x-model="editRental.rental_duration" class="w-full text-xs h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 hover:bg-white focus:bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-medium transition focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-2xs" required>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                        Satuan Durasi <span class="text-rose-500 font-bold">*</span>
                                    </label>
                                    <select name="duration_unit" x-model="editRental.duration_unit" class="w-full text-xs h-8 px-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 hover:bg-white focus:bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-medium transition focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-2xs" required>
                                        <option value="Shift">Shift</option>
                                        <option value="Hari" selected>Hari</option>
                                        <option value="Jam">Jam</option>
                                        <option value="Bulan">Bulan</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                        Tarif / Satuan <span class="text-[10px] font-normal text-slate-400">(Opsional)</span>
                                    </label>
                                    <div class="flex rounded-lg overflow-hidden border border-slate-200 dark:border-slate-700 bg-slate-50/60 hover:bg-white focus-within:bg-white dark:bg-slate-800 shadow-2xs transition focus-within:ring-2 focus-within:ring-indigo-500/20 focus-within:border-indigo-500">
                                        <span class="inline-flex items-center px-2.5 bg-slate-100/90 dark:bg-slate-700/60 text-slate-500 dark:text-slate-400 text-[11px] font-bold border-r border-slate-200 dark:border-slate-700 select-none">
                                            Rp
                                        </span>
                                        <input type="number" step="any" min="0" name="rate_per_unit" x-model="editRental.rate_per_unit" class="w-full text-xs h-8 px-2 bg-transparent border-0 focus:ring-0 focus:outline-none text-slate-800 dark:text-slate-100 font-medium placeholder-slate-400" placeholder="0">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                        Deposit / DP <span class="text-[10px] font-normal text-slate-400">(Opsional)</span>
                                    </label>
                                    <div class="flex rounded-lg overflow-hidden border border-slate-200 dark:border-slate-700 bg-slate-50/60 hover:bg-white focus-within:bg-white dark:bg-slate-800 shadow-2xs transition focus-within:ring-2 focus-within:ring-indigo-500/20 focus-within:border-indigo-500">
                                        <span class="inline-flex items-center px-2.5 bg-slate-100/90 dark:bg-slate-700/60 text-slate-500 dark:text-slate-400 text-[11px] font-bold border-r border-slate-200 dark:border-slate-700 select-none">
                                            Rp
                                        </span>
                                        <input type="number" step="any" min="0" name="deposit_amount" x-model="editRental.deposit_amount" class="w-full text-xs h-8 px-2 bg-transparent border-0 focus:ring-0 focus:outline-none text-slate-800 dark:text-slate-100 font-medium placeholder-slate-400" placeholder="0">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Divider --}}
                        <div class="relative py-0.5">
                            <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-slate-200/70 dark:border-slate-800"></div></div>
                            <div class="relative flex justify-start"><span class="bg-white dark:bg-slate-900 pr-2 text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Status & Catatan</span></div>
                        </div>

                        {{-- Bagian 3: Status & Catatan --}}
                        <div class="space-y-2">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                        Status Sewa <span class="text-rose-500 font-bold">*</span>
                                    </label>
                                    <select name="status" x-model="editRental.status" class="w-full text-xs h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 hover:bg-white focus:bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-medium transition focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-2xs" required>
                                        <option value="active">Aktif (Sedang Beroperasi)</option>
                                        <option value="returned">Selesai (Sudah Dikembalikan)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                        Catatan Sewa <span class="text-[10px] font-normal text-slate-400">(Opsional)</span>
                                    </label>
                                    <input type="text" name="notes" x-model="editRental.notes" class="w-full text-xs h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 hover:bg-white focus:bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-medium placeholder-slate-400 transition focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-2xs" placeholder="Area kerja, operator, dll.">
                                </div>
                            </div>
                        </div>

                    </div>

                    {{-- 3. Dedicated Footer Bar (Compact) --}}
                    <div class="px-4 py-2 bg-slate-50 dark:bg-slate-800/80 border-t border-slate-200/80 dark:border-slate-800 flex items-center justify-end gap-2 shrink-0">
                        <button type="button" @click="showEditRentalModal = false" class="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold transition shadow-2xs">
                            Batal
                        </button>
                        <button type="submit" class="px-3.5 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700 text-white text-xs font-bold transition shadow-sm shadow-indigo-600/30 inline-flex items-center gap-1.5">
                            <x-lucide-check class="w-3.5 h-3.5" />
                            <span>Perbarui Sewa Alat</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- MODAL: TAMBAH SURAT JALAN PEMINDAHAN / MOBILISASI BARANG                  --}}
        {{-- ========================================================================= --}}
        <div x-show="showTransferModal" x-cloak class="fixed inset-0 flex items-center justify-center bg-slate-950/65 backdrop-blur-xs p-3 sm:p-4 overflow-y-auto" style="z-index: 9999;">
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-2xl w-full overflow-hidden my-auto flex flex-col" style="max-width: 520px;" @click.away="showTransferModal = false">
                
                {{-- Header Bar --}}
                <div class="px-4 py-2.5 bg-slate-50/90 dark:bg-slate-800/80 border-b border-slate-200/80 dark:border-slate-800 flex items-center justify-between shrink-0">
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-lg bg-teal-500/10 text-teal-600 dark:text-teal-400 flex items-center justify-center border border-teal-200/70 dark:border-teal-800/70 shadow-2xs shrink-0">
                            <x-lucide-arrow-right-left class="w-3.5 h-3.5" />
                        </div>
                        <div>
                            <h3 class="text-xs font-extrabold text-slate-900 dark:text-white tracking-tight">
                                Buat Surat Jalan Pemindahan Baru
                            </h3>
                            <p class="text-[10px] text-slate-500 dark:text-slate-400">
                                Rekap mobilisasi barang, alat, dan material antar proyek
                            </p>
                        </div>
                    </div>
                    <button type="button" @click="showTransferModal = false" class="w-6 h-6 rounded-md bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-400 hover:text-slate-600 dark:text-slate-400 dark:hover:text-slate-200 flex items-center justify-center transition shadow-2xs" title="Tutup">
                        <x-lucide-x class="w-3.5 h-3.5" />
                    </button>
                </div>

                {{-- Form Body --}}
                <form method="POST" action="{{ route('project-notes.transfers.store') }}" class="flex flex-col m-0">
                    @csrf
                    
                    <div class="p-4 space-y-2.5 max-h-[75vh] overflow-y-auto">
                        <div class="space-y-2">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                    Proyek Terkait <span class="text-rose-500 font-bold">*</span>
                                </label>
                                <select name="project_id" class="w-full text-xs h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 hover:bg-white focus:bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-medium transition focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 shadow-2xs" required>
                                    @foreach($projects as $p)
                                        <option value="{{ $p->id }}" {{ $selectedProjectId == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                    Judul / Nama Mobilisasi <span class="text-rose-500 font-bold">*</span>
                                </label>
                                <input type="text" name="title" class="w-full text-xs h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 hover:bg-white focus:bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-medium placeholder-slate-400 transition focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 shadow-2xs" placeholder="Contoh: SURAT JALAN FAPERTA" required>
                            </div>
                        </div>

                        {{-- Divider --}}
                        <div class="relative py-0.5">
                            <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-slate-200/70 dark:border-slate-800"></div></div>
                            <div class="relative flex justify-start"><span class="bg-white dark:bg-slate-900 pr-2 text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Rute & Nomor Surat</span></div>
                        </div>

                        <div class="space-y-2">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                        No. Surat Jalan <span class="text-[10px] font-normal text-slate-400">(Opsional)</span>
                                    </label>
                                    <input type="text" name="docket_number" class="w-full text-xs h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 hover:bg-white focus:bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-medium placeholder-slate-400 transition focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 shadow-2xs" placeholder="Contoh: SJ-01/ARK/2026">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                        Tanggal Pemindahan <span class="text-rose-500 font-bold">*</span>
                                    </label>
                                    <input type="date" name="transfer_date" value="{{ date('Y-m-d') }}" class="w-full text-xs h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 hover:bg-white focus:bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-medium transition focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 shadow-2xs" required>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                        Lokasi Asal
                                    </label>
                                    <input type="text" name="origin_location" class="w-full text-xs h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 hover:bg-white focus:bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-medium placeholder-slate-400 transition focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 shadow-2xs" placeholder="Gudang / Workshop Utama">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                        Lokasi Tujuan
                                    </label>
                                    <input type="text" name="destination_location" class="w-full text-xs h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 hover:bg-white focus:bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-medium placeholder-slate-400 transition focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 shadow-2xs" placeholder="Site Proyek Faperta UGM">
                                </div>
                            </div>
                        </div>

                        {{-- Divider --}}
                        <div class="relative py-0.5">
                            <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-slate-200/70 dark:border-slate-800"></div></div>
                            <div class="relative flex justify-start"><span class="bg-white dark:bg-slate-900 pr-2 text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Pengangkut & Status</span></div>
                        </div>

                        <div class="space-y-2">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                        Driver / Kendaraan
                                    </label>
                                    <input type="text" name="driver_or_transporter" class="w-full text-xs h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 hover:bg-white focus:bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-medium placeholder-slate-400 transition focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 shadow-2xs" placeholder="Pak Budi / Truk AB 1234 CD">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                        Status Pemindahan <span class="text-rose-500 font-bold">*</span>
                                    </label>
                                    <select name="status" class="w-full text-xs h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 hover:bg-white focus:bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-medium transition focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 shadow-2xs" required>
                                        <option value="pending">Draft (Menunggu Muat)</option>
                                        <option value="in_transit" selected>Di Perjalanan (Mobilisasi)</option>
                                        <option value="received">Diterima di Lokasi</option>
                                        <option value="completed">Selesai</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                    Catatan Tambahan <span class="text-[10px] font-normal text-slate-400">(Opsional)</span>
                                </label>
                                <input type="text" name="notes" class="w-full text-xs h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 hover:bg-white focus:bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-medium placeholder-slate-400 transition focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 shadow-2xs" placeholder="Keterangan kondisi muatan atau instruksi penerima...">
                            </div>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="px-4 py-2 bg-slate-50 dark:bg-slate-800/80 border-t border-slate-200/80 dark:border-slate-800 flex items-center justify-end gap-2 shrink-0">
                        <button type="button" @click="showTransferModal = false" class="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold transition shadow-2xs">
                            Batal
                        </button>
                        <button type="submit" class="px-3.5 py-1.5 rounded-lg bg-teal-600 hover:bg-teal-500 active:bg-teal-700 text-white text-xs font-bold transition shadow-sm shadow-teal-600/30 inline-flex items-center gap-1.5">
                            <x-lucide-check class="w-3.5 h-3.5" />
                            <span>Simpan Surat Jalan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- MODAL: EDIT SURAT JALAN PEMINDAHAN / MOBILISASI BARANG                    --}}
        {{-- ========================================================================= --}}
        <div x-show="showEditTransferModal" x-cloak class="fixed inset-0 flex items-center justify-center bg-slate-950/65 backdrop-blur-xs p-3 sm:p-4 overflow-y-auto" style="z-index: 9999;">
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-2xl w-full overflow-hidden my-auto flex flex-col" style="max-width: 520px;" @click.away="showEditTransferModal = false">
                
                {{-- Header Bar --}}
                <div class="px-4 py-2.5 bg-slate-50/90 dark:bg-slate-800/80 border-b border-slate-200/80 dark:border-slate-800 flex items-center justify-between shrink-0">
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-lg bg-teal-500/10 text-teal-600 dark:text-teal-400 flex items-center justify-center border border-teal-200/70 dark:border-teal-800/70 shadow-2xs shrink-0">
                            <x-lucide-pencil class="w-3.5 h-3.5" />
                        </div>
                        <div>
                            <h3 class="text-xs font-extrabold text-slate-900 dark:text-white tracking-tight">
                                Edit Surat Jalan Pemindahan
                            </h3>
                            <p class="text-[10px] text-slate-500 dark:text-slate-400">
                                Perbarui data mobilisasi, rute, atau status kirim
                            </p>
                        </div>
                    </div>
                    <button type="button" @click="showEditTransferModal = false" class="w-6 h-6 rounded-md bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-400 hover:text-slate-600 dark:text-slate-400 dark:hover:text-slate-200 flex items-center justify-center transition shadow-2xs" title="Tutup">
                        <x-lucide-x class="w-3.5 h-3.5" />
                    </button>
                </div>

                {{-- Form Body --}}
                <form method="POST" :action="`{{ url('/project-notes/transfers') }}/${editTransfer.id}`" class="flex flex-col m-0">
                    @csrf
                    @method('PUT')

                    <div class="p-4 space-y-2.5 max-h-[75vh] overflow-y-auto">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                Judul / Nama Mobilisasi <span class="text-rose-500 font-bold">*</span>
                            </label>
                            <input type="text" name="title" x-model="editTransfer.title" class="w-full text-xs h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 hover:bg-white focus:bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-medium placeholder-slate-400 transition focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 shadow-2xs" required>
                        </div>

                        {{-- Divider --}}
                        <div class="relative py-0.5">
                            <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-slate-200/70 dark:border-slate-800"></div></div>
                            <div class="relative flex justify-start"><span class="bg-white dark:bg-slate-900 pr-2 text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Rute & Nomor Surat</span></div>
                        </div>

                        <div class="space-y-2">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                        No. Surat Jalan <span class="text-[10px] font-normal text-slate-400">(Opsional)</span>
                                    </label>
                                    <input type="text" name="docket_number" x-model="editTransfer.docket_number" class="w-full text-xs h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 hover:bg-white focus:bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-medium placeholder-slate-400 transition focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 shadow-2xs">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                        Tanggal Pemindahan <span class="text-rose-500 font-bold">*</span>
                                    </label>
                                    <input type="date" name="transfer_date" x-model="editTransfer.transfer_date" class="w-full text-xs h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 hover:bg-white focus:bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-medium transition focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 shadow-2xs" required>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                        Lokasi Asal
                                    </label>
                                    <input type="text" name="origin_location" x-model="editTransfer.origin_location" class="w-full text-xs h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 hover:bg-white focus:bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-medium placeholder-slate-400 transition focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 shadow-2xs">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                        Lokasi Tujuan
                                    </label>
                                    <input type="text" name="destination_location" x-model="editTransfer.destination_location" class="w-full text-xs h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 hover:bg-white focus:bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-medium placeholder-slate-400 transition focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 shadow-2xs">
                                </div>
                            </div>
                        </div>

                        {{-- Divider --}}
                        <div class="relative py-0.5">
                            <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-slate-200/70 dark:border-slate-800"></div></div>
                            <div class="relative flex justify-start"><span class="bg-white dark:bg-slate-900 pr-2 text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Pengangkut & Status</span></div>
                        </div>

                        <div class="space-y-2">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                        Driver / Kendaraan
                                    </label>
                                    <input type="text" name="driver_or_transporter" x-model="editTransfer.driver_or_transporter" class="w-full text-xs h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 hover:bg-white focus:bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-medium placeholder-slate-400 transition focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 shadow-2xs">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                        Status Pemindahan <span class="text-rose-500 font-bold">*</span>
                                    </label>
                                    <select name="status" x-model="editTransfer.status" class="w-full text-xs h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 hover:bg-white focus:bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-medium transition focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 shadow-2xs" required>
                                        <option value="pending">Draft (Menunggu Muat)</option>
                                        <option value="in_transit">Di Perjalanan (Mobilisasi)</option>
                                        <option value="received">Diterima di Lokasi</option>
                                        <option value="completed">Selesai</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                    Catatan Tambahan <span class="text-[10px] font-normal text-slate-400">(Opsional)</span>
                                </label>
                                <input type="text" name="notes" x-model="editTransfer.notes" class="w-full text-xs h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 hover:bg-white focus:bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-medium placeholder-slate-400 transition focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 shadow-2xs">
                            </div>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="px-4 py-2 bg-slate-50 dark:bg-slate-800/80 border-t border-slate-200/80 dark:border-slate-800 flex items-center justify-end gap-2 shrink-0">
                        <button type="button" @click="showEditTransferModal = false" class="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold transition shadow-2xs">
                            Batal
                        </button>
                        <button type="submit" class="px-3.5 py-1.5 rounded-lg bg-teal-600 hover:bg-teal-500 active:bg-teal-700 text-white text-xs font-bold transition shadow-sm shadow-teal-600/30 inline-flex items-center gap-1.5">
                            <x-lucide-check class="w-3.5 h-3.5" />
                            <span>Perbarui Surat Jalan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- MODAL: TAMBAH CATATAN MATERIAL CURAH (PASIR, BATU, RITASE)                --}}
        {{-- ========================================================================= --}}
        <div x-show="showBulkModal" x-cloak class="fixed inset-0 flex items-center justify-center bg-slate-950/65 backdrop-blur-xs p-3 sm:p-4 overflow-y-auto" style="z-index: 9999;">
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-2xl w-full overflow-hidden my-auto flex flex-col" style="max-width: 520px;" @click.away="showBulkModal = false">
                
                {{-- Header Bar --}}
                <div class="px-4 py-2.5 bg-slate-50/90 dark:bg-slate-800/80 border-b border-slate-200/80 dark:border-slate-800 flex items-center justify-between shrink-0">
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-lg bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center border border-amber-200/70 dark:border-amber-800/70 shadow-2xs shrink-0">
                            <x-lucide-layers class="w-3.5 h-3.5" />
                        </div>
                        <div>
                            <h3 class="text-xs font-extrabold text-slate-900 dark:text-white tracking-tight">
                                Catatan Material Curah Baru
                            </h3>
                            <p class="text-[10px] text-slate-500 dark:text-slate-400">
                                Rekap pembelian pasir, batu, sirtu, & material ritase per suplier
                            </p>
                        </div>
                    </div>
                    <button type="button" @click="showBulkModal = false" class="w-6 h-6 rounded-md bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-400 hover:text-slate-600 dark:text-slate-400 dark:hover:text-slate-200 flex items-center justify-center transition shadow-2xs" title="Tutup">
                        <x-lucide-x class="w-3.5 h-3.5" />
                    </button>
                </div>

                {{-- Form Body --}}
                <form method="POST" action="{{ route('project-notes.bulk.store') }}" class="flex flex-col m-0">
                    @csrf

                    <div class="p-4 space-y-2.5 max-h-[75vh] overflow-y-auto">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                Proyek Tujuan <span class="text-rose-500 font-bold">*</span>
                            </label>
                            <select name="project_id" class="w-full text-xs h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 hover:bg-white focus:bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-medium transition focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 shadow-2xs" required>
                                <option value="">-- Pilih Proyek --</option>
                                @foreach($projects as $p)
                                    <option value="{{ $p->id }}" {{ (isset($selectedProjectId) && $selectedProjectId == $p->id) ? 'selected' : '' }}>
                                        {{ $p->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                Judul Rekap / Catatan <span class="text-rose-500 font-bold">*</span>
                            </label>
                            <input type="text" name="title" class="w-full text-xs h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 hover:bg-white focus:bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-medium placeholder-slate-400 transition focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 shadow-2xs" placeholder="Contoh: CATATAN PEMBELIAN PASIR & BATU (SUPRI)" required>
                        </div>

                        {{-- Suplier & Status --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                    Nama Suplier <span class="text-rose-500 font-bold">*</span>
                                </label>
                                <input type="text" name="supplier_name" class="w-full text-xs h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 hover:bg-white focus:bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-medium placeholder-slate-400 transition focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 shadow-2xs" placeholder="Supri / Pak Joko / CV..." required>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                    Status Catatan <span class="text-rose-500 font-bold">*</span>
                                </label>
                                <select name="status" class="w-full text-xs h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 hover:bg-white focus:bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-medium transition focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 shadow-2xs" required>
                                    <option value="active" selected>Aktif (Berjalan)</option>
                                    <option value="completed">Selesai</option>
                                    <option value="archived">Arsip</option>
                                </select>
                            </div>
                        </div>

                        {{-- Tanggal Mulai & Selesai --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                    Tanggal Mulai <span class="text-rose-500 font-bold">*</span>
                                </label>
                                <input type="date" name="start_date" value="{{ date('Y-m-d') }}" class="w-full text-xs h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 hover:bg-white focus:bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-medium transition focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 shadow-2xs" required>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                    Tanggal Selesai <span class="text-[10px] font-normal text-slate-400">(Opsional)</span>
                                </label>
                                <input type="date" name="end_date" class="w-full text-xs h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 hover:bg-white focus:bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-medium transition focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 shadow-2xs">
                            </div>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                Catatan / Keterangan <span class="text-[10px] font-normal text-slate-400">(Opsional)</span>
                            </label>
                            <input type="text" name="notes" class="w-full text-xs h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 hover:bg-white focus:bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-medium placeholder-slate-400 transition focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 shadow-2xs" placeholder="Keterangan peruntukan atau kesepakatan harga rit...">
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="px-4 py-2 bg-slate-50 dark:bg-slate-800/80 border-t border-slate-200/80 dark:border-slate-800 flex items-center justify-end gap-2 shrink-0">
                        <button type="button" @click="showBulkModal = false" class="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold transition shadow-2xs">
                            Batal
                        </button>
                        <button type="submit" class="px-3.5 py-1.5 rounded-lg bg-amber-600 hover:bg-amber-500 active:bg-amber-700 text-white text-xs font-bold transition shadow-sm shadow-amber-600/30 inline-flex items-center gap-1.5">
                            <x-lucide-check class="w-3.5 h-3.5" />
                            <span>Simpan Catatan Curah</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- MODAL: EDIT CATATAN MATERIAL CURAH (PASIR, BATU, RITASE)                  --}}
        {{-- ========================================================================= --}}
        <div x-show="showEditBulkModal" x-cloak class="fixed inset-0 flex items-center justify-center bg-slate-950/65 backdrop-blur-xs p-3 sm:p-4 overflow-y-auto" style="z-index: 9999;">
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-2xl w-full overflow-hidden my-auto flex flex-col" style="max-width: 520px;" @click.away="showEditBulkModal = false">
                
                {{-- Header Bar --}}
                <div class="px-4 py-2.5 bg-slate-50/90 dark:bg-slate-800/80 border-b border-slate-200/80 dark:border-slate-800 flex items-center justify-between shrink-0">
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-lg bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center border border-amber-200/70 dark:border-amber-800/70 shadow-2xs shrink-0">
                            <x-lucide-pencil class="w-3.5 h-3.5" />
                        </div>
                        <div>
                            <h3 class="text-xs font-extrabold text-slate-900 dark:text-white tracking-tight">
                                Edit Catatan Material Curah
                            </h3>
                            <p class="text-[10px] text-slate-500 dark:text-slate-400">
                                Perbarui judul, suplier, periode, atau status catatan
                            </p>
                        </div>
                    </div>
                    <button type="button" @click="showEditBulkModal = false" class="w-6 h-6 rounded-md bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-400 hover:text-slate-600 dark:text-slate-400 dark:hover:text-slate-200 flex items-center justify-center transition shadow-2xs" title="Tutup">
                        <x-lucide-x class="w-3.5 h-3.5" />
                    </button>
                </div>

                {{-- Form Body --}}
                <form method="POST" :action="`{{ url('/project-notes/bulk-materials') }}/${editBulk.id}`" class="flex flex-col m-0">
                    @csrf
                    @method('PUT')

                    <div class="p-4 space-y-2.5 max-h-[75vh] overflow-y-auto">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                Judul Rekap / Catatan <span class="text-rose-500 font-bold">*</span>
                            </label>
                            <input type="text" name="title" x-model="editBulk.title" class="w-full text-xs h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 hover:bg-white focus:bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-medium placeholder-slate-400 transition focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 shadow-2xs" required>
                        </div>

                        {{-- Suplier & Status --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                    Nama Suplier <span class="text-rose-500 font-bold">*</span>
                                </label>
                                <input type="text" name="supplier_name" x-model="editBulk.supplier_name" class="w-full text-xs h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 hover:bg-white focus:bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-medium placeholder-slate-400 transition focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 shadow-2xs" required>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                    Status Catatan <span class="text-rose-500 font-bold">*</span>
                                </label>
                                <select name="status" x-model="editBulk.status" class="w-full text-xs h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 hover:bg-white focus:bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-medium transition focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 shadow-2xs" required>
                                    <option value="active">Aktif (Berjalan)</option>
                                    <option value="completed">Selesai</option>
                                    <option value="archived">Arsip</option>
                                </select>
                            </div>
                        </div>

                        {{-- Tanggal Mulai & Selesai --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                    Tanggal Mulai <span class="text-rose-500 font-bold">*</span>
                                </label>
                                <input type="date" name="start_date" x-model="editBulk.start_date" class="w-full text-xs h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 hover:bg-white focus:bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-medium transition focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 shadow-2xs" required>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                    Tanggal Selesai <span class="text-[10px] font-normal text-slate-400">(Opsional)</span>
                                </label>
                                <input type="date" name="end_date" x-model="editBulk.end_date" class="w-full text-xs h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 hover:bg-white focus:bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-medium transition focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 shadow-2xs">
                            </div>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                Catatan / Keterangan <span class="text-[10px] font-normal text-slate-400">(Opsional)</span>
                            </label>
                            <input type="text" name="notes" x-model="editBulk.notes" class="w-full text-xs h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 hover:bg-white focus:bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-medium placeholder-slate-400 transition focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 shadow-2xs">
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="px-4 py-2 bg-slate-50 dark:bg-slate-800/80 border-t border-slate-200/80 dark:border-slate-800 flex items-center justify-end gap-2 shrink-0">
                        <button type="button" @click="showEditBulkModal = false" class="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold transition shadow-2xs">
                            Batal
                        </button>
                        <button type="submit" class="px-3.5 py-1.5 rounded-lg bg-amber-600 hover:bg-amber-500 active:bg-amber-700 text-white text-xs font-bold transition shadow-sm shadow-amber-600/30 inline-flex items-center gap-1.5">
                            <x-lucide-check class="w-3.5 h-3.5" />
                            <span>Perbarui Catatan Curah</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
