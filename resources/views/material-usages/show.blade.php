<x-app-layout>
    <x-slot name="title">Bukti Pemakaian Material #{{ $materialUsage->usage_number }}</x-slot>

    @push('styles')
    <style>
        .voucher-container {
            max-width: 1100px;
            margin: 0 auto;
        }

        .meta-cell {
            padding: 14px 18px;
            border-right: 1px solid #f1f5f9;
        }

        .meta-cell:last-child {
            border-right: none;
        }

        @media (max-width: 900px) {
            .meta-grid-4 {
                grid-template-columns: repeat(2, 1fr) !important;
            }
            .meta-cell:nth-child(2) {
                border-right: none;
            }
            .meta-cell:nth-child(1), .meta-cell:nth-child(2) {
                border-bottom: 1px solid #f1f5f9;
            }
        }

        @media (max-width: 540px) {
            .meta-grid-4 {
                grid-template-columns: 1fr !important;
            }
            .meta-cell {
                border-right: none !important;
                border-bottom: 1px solid #f1f5f9;
            }
            .meta-cell:last-child {
                border-bottom: none;
            }
        }

        .sign-card {
            background: #fafaf9;
            border: 1px solid #e7e5e4;
            border-radius: 12px;
            padding: 16px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 100%;
        }

        /* Timeline Horizontal / Compact */
        .audit-track {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
            position: relative;
        }

        .audit-step {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 12px 14px;
            position: relative;
        }
    </style>
    @endpush

    <div class="voucher-container space-y-5" x-data="{ showCancelModal: false, showAuditDetails: false }">
        {{-- ==================== 1. Breadcrumbs & Top Action Bar ==================== --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-1">
            <div class="flex items-center gap-1.5 text-xs text-slate-500 font-medium">
                <a href="{{ route('material-usages.index') }}" class="hover:text-orange-600 transition-colors">Pemakaian Material</a>
                <x-lucide-chevron-right class="w-3.5 h-3.5 text-slate-400" />
                <span class="text-slate-800 font-semibold">#{{ $materialUsage->usage_number }}</span>
            </div>

            <div class="flex items-center gap-2 self-start sm:self-auto flex-wrap">
                <a href="{{ route('material-usages.index') }}" class="btn btn-light border btn-sm text-slate-600 hover:text-slate-900 font-semibold px-3 py-1.5 rounded-lg text-xs inline-flex items-center gap-1.5 transition-all shadow-2xs">
                    <x-lucide-arrow-left class="w-3.5 h-3.5" />
                    <span>Kembali</span>
                </a>

                @if($materialUsage->status !== 'cancelled')
                <a href="{{ route('material-usages.print', $materialUsage) }}" target="_blank" class="btn btn-primary btn-sm bg-orange-600 hover:bg-orange-700 border-orange-600 font-semibold px-3.5 py-1.5 rounded-lg text-xs inline-flex items-center gap-1.5 shadow-xs text-white transition-all">
                    <x-lucide-printer class="w-3.5 h-3.5" />
                    <span>Cetak Bon (PDF)</span>
                </a>
                @endif

                @if($materialUsage->status === 'completed')
                    @can('cancel material usages')
                    <button type="button" @click="showCancelModal = true" class="btn btn-outline-danger btn-sm text-red-600 hover:bg-red-50 border-red-200 font-semibold px-3 py-1.5 rounded-lg text-xs inline-flex items-center gap-1.5 transition-all">
                        <x-lucide-ban class="w-3.5 h-3.5" />
                        <span>Batalkan</span>
                    </button>
                    @endcan
                @endif
            </div>
        </div>

        {{-- Alerts --}}
        @if (session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl text-xs font-semibold flex items-center gap-2 shadow-xs">
            <x-lucide-circle-check class="w-4 h-4 text-emerald-600 flex-shrink-0" />
            <span>{{ session('success') }}</span>
        </div>
        @endif

        @if (isset($errors) && $errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl text-xs font-semibold flex items-start gap-2 shadow-xs">
            <x-lucide-circle-alert class="w-4 h-4 text-red-600 flex-shrink-0 mt-0.5" />
            <div class="space-y-1">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Banner Jika Status Dibatalkan --}}
        @if($materialUsage->status === 'cancelled')
        <div class="bg-red-50 border border-red-200 rounded-2xl p-4 flex items-start gap-3.5 shadow-xs">
            <div class="w-9 h-9 rounded-xl bg-red-100 text-red-600 flex items-center justify-center flex-shrink-0 mt-0.5">
                <x-lucide-ban class="w-5 h-5" />
            </div>
            <div class="flex-1">
                <div class="flex items-center gap-2 flex-wrap">
                    <h3 class="text-xs sm:text-sm font-bold text-red-900 m-0">Pengeluaran Material Telah Dibatalkan</h3>
                    <span class="text-[11px] text-red-600 font-medium">({{ $materialUsage->cancelled_at?->format('d/m/Y H:i') ?? '-' }})</span>
                </div>
                <p class="text-xs text-red-700 mt-1 mb-0 leading-relaxed">
                    Dibatalkan oleh <strong>{{ $materialUsage->cancelledBy?->name ?? 'Petugas' }}</strong>. Kuantitas stok telah secara otomatis dikembalikan ke gudang <strong>{{ $materialUsage->warehouse?->name }}</strong>.
                </p>
                @if($materialUsage->cancellation_reason)
                <div class="mt-2 text-xs text-red-800 bg-red-100/70 border border-red-200/80 rounded-lg px-3 py-1.5 font-medium">
                    <span class="font-bold">Alasan:</span> {{ $materialUsage->cancellation_reason }}
                </div>
                @endif
            </div>
        </div>
        @endif

        {{-- ==================== 2. MASTER VOUCHER SHEET (Single Unified Canvas) ==================== --}}
        <div class="bg-white border border-slate-200/90 rounded-2xl shadow-sm overflow-hidden {{ $materialUsage->status === 'cancelled' ? 'opacity-90' : '' }}">
            {{-- Voucher Top Ribbon --}}
            <div class="px-6 py-5 bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-xl bg-orange-600/90 text-white flex items-center justify-center shadow-inner flex-shrink-0 border border-orange-400/30">
                        <x-lucide-boxes class="w-6 h-6" />
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-[10.5px] font-bold uppercase tracking-widest text-orange-400">
                                Bukti Pengeluaran Barang (BPB)
                            </span>
                            @if($materialUsage->status === 'cancelled')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10.5px] font-semibold bg-red-500/20 text-red-300 border border-red-500/30">
                                    Dibatalkan
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10.5px] font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                    Selesai / Terverifikasi
                                </span>
                            @endif
                        </div>
                        <h1 class="text-xl sm:text-2xl font-black tracking-tight text-white m-0 mt-0.5 font-mono">
                            #{{ $materialUsage->usage_number }}
                        </h1>
                    </div>
                </div>

                <div class="sm:text-right flex sm:flex-col items-center sm:items-end justify-between gap-1 border-t sm:border-t-0 pt-3 sm:pt-0 border-slate-700/60">
                    <div class="text-xs text-slate-300">
                        PT ARSIKON CIPTA KARYA
                    </div>
                    <div class="text-[11px] text-slate-400 font-mono">
                        {{ \Carbon\Carbon::parse($materialUsage->usage_date)->translatedFormat('d F Y') }}
                    </div>
                </div>
            </div>

            {{-- Voucher Metadata Grid (4-Column Balanced Bar) --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 meta-grid-4 bg-slate-50/70 border-b border-slate-200 text-xs">
                {{-- Cell 1: No. Pemakaian & Status --}}
                <div class="meta-cell">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">
                        No. Pemakaian
                    </span>
                    <div class="font-bold text-slate-900 font-mono text-xs">
                        {{ $materialUsage->usage_number }}
                    </div>
                    <div class="text-[11px] text-slate-500 mt-0.5 flex items-center gap-1">
                        <x-lucide-calendar class="w-3 h-3 text-slate-400" />
                        {{ \Carbon\Carbon::parse($materialUsage->usage_date)->format('d/m/Y') }}
                    </div>
                </div>

                {{-- Cell 2: Pemakai (Mandor/Tukang) --}}
                <div class="meta-cell">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">
                        Pemakai (Mandor/Tukang)
                    </span>
                    <div class="font-bold text-slate-900 text-xs">
                        {{ $materialUsage->recipient_name }}
                    </div>
                    <div class="text-[11px] text-slate-500 mt-0.5 flex items-center gap-1">
                        <x-lucide-map-pin class="w-3 h-3 text-slate-400" />
                        Pekerjaan / Zona: {{ $materialUsage->job_section ?: 'Pekerjaan Umum' }}
                    </div>
                </div>

                {{-- Cell 3: Gudang & Proyek Asal --}}
                <div class="meta-cell">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">
                        Gudang Asal Barang
                    </span>
                    <div class="font-bold text-slate-900 text-xs">
                        {{ $materialUsage->warehouse?->name ?? 'Gudang Pusat' }}
                    </div>
                    <div class="text-[11px] text-slate-500 mt-0.5 flex items-center gap-1">
                        <x-lucide-building-2 class="w-3 h-3 text-slate-400" />
                        {{ $materialUsage->project?->name ? 'Proyek: ' . $materialUsage->project->name : 'Gudang Operasional' }}
                    </div>
                </div>

                {{-- Cell 4: Petugas & Referensi MR --}}
                <div class="meta-cell">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">
                        Petugas &amp; Referensi
                    </span>
                    <div class="font-bold text-slate-900 text-xs">
                        {{ $materialUsage->issuedBy?->name ?? 'Admin Gudang' }}
                    </div>
                    <div class="text-[11px] text-slate-500 mt-0.5 flex items-center gap-1">
                        @if($materialUsage->materialRequest)
                            <a href="{{ route('material-requests.show', $materialUsage->materialRequest) }}" class="text-blue-600 font-semibold hover:underline flex items-center gap-0.5">
                                <x-lucide-file-text class="w-3 h-3" /> MR #{{ $materialUsage->materialRequest->request_number }}
                            </a>
                        @else
                            <span class="text-slate-400">Pengeluaran Langsung</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Catatan Keperluan jika ada --}}
            @if($materialUsage->notes)
            <div class="px-6 py-3 bg-amber-50/50 border-b border-amber-100 flex items-start gap-2.5 text-xs text-amber-900">
                <x-lucide-info class="w-4 h-4 text-amber-600 flex-shrink-0 mt-0.5" />
                <div>
                    <span class="font-bold">Catatan Keperluan:</span> {{ $materialUsage->notes }}
                </div>
            </div>
            @endif

            {{-- ==================== 3. Tabel Rincian Material yang Dikeluarkan ==================== --}}
            <div class="p-6">
                <div class="flex items-center justify-between mb-3 flex-wrap gap-2">
                    <div class="flex items-center gap-2">
                        <x-lucide-layers class="w-4 h-4 text-orange-600" />
                        <h2 class="text-xs sm:text-sm font-bold text-slate-900 uppercase tracking-wider m-0">
                            Rincian Material yang Dikeluarkan
                        </h2>
                    </div>
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                        {{ $materialUsage->items->count() }} Jenis Item
                    </span>
                </div>

                <div class="border border-slate-200 rounded-xl overflow-hidden shadow-2xs">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                                    <th class="py-3 px-3 text-center w-12">#</th>
                                    <th class="py-3 px-4">Nama Material &amp; Spesifikasi</th>
                                    <th class="py-3 px-3 text-center w-36">Kategori</th>
                                    <th class="py-3 px-4 text-right w-40">Jumlah Keluar</th>
                                    <th class="py-3 px-4">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-xs">
                                @forelse($materialUsage->items as $index => $item)
                                @php
                                    $isCustom = $item->isCustom();
                                    $categoryName = $isCustom ? 'Custom' : ($item->material?->category?->name ?? 'Umum');
                                    $catUpper = strtoupper($categoryName);

                                    $catBadgeClass = str_contains($catUpper, 'STRUKTUR')
                                        ? 'bg-blue-50 text-blue-700 border-blue-200'
                                        : (str_contains($catUpper, 'ARSITEK')
                                            ? 'bg-amber-50 text-amber-700 border-amber-200'
                                            : (str_contains($catUpper, 'MEP')
                                                ? 'bg-indigo-50 text-indigo-700 border-indigo-200'
                                                : ($isCustom
                                                    ? 'bg-purple-50 text-purple-700 border-purple-200'
                                                    : 'bg-slate-100 text-slate-700 border-slate-200')));
                                @endphp
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    <td class="py-3 px-3 text-center font-medium text-slate-400">
                                        {{ $index + 1 }}
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="font-bold text-slate-900 text-xs sm:text-sm">
                                            {{ $item->displayName() }}
                                            @if($isCustom)
                                                <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-semibold bg-purple-50 text-purple-700 border border-purple-200 ml-1">
                                                    Item Custom
                                                </span>
                                            @endif
                                        </div>

                                        @if(!$isCustom && $item->material)
                                            <div class="flex items-center gap-2 text-[11px] text-slate-500 mt-0.5 flex-wrap">
                                                @if($item->material->type)
                                                    <span class="font-medium text-slate-600">{{ $item->material->type }}</span>
                                                @endif
                                                @if($item->material->brand)
                                                    <span class="text-slate-400">•</span>
                                                    <span>Merek: {{ $item->material->brand }}</span>
                                                @endif
                                                @if($item->material->size)
                                                    <span class="text-slate-400">•</span>
                                                    <span>{{ $item->material->size }}</span>
                                                @endif
                                            </div>
                                        @elseif($isCustom)
                                            <div class="text-[11px] text-slate-400 mt-0.5">Item pengeluaran khusus non-master</div>
                                        @endif
                                    </td>
                                    <td class="py-3 px-3 text-center">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold border {{ $catBadgeClass }}">
                                            {{ $categoryName }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        <span class="font-bold text-slate-900 text-sm tracking-tight" style="font-variant-numeric: tabular-nums;">
                                            {{ format_quantity($item->quantity) }}
                                        </span>
                                        <span class="text-slate-500 font-semibold text-[11px] ml-1">{{ $item->displayUnit() }}</span>
                                    </td>
                                    <td class="py-3 px-4 text-slate-600 text-xs">
                                        {{ $item->notes ?: '-' }}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-slate-400 text-xs">
                                        Tidak ada item material tercatat.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="bg-slate-50 border-t border-slate-200 font-semibold text-xs">
                                <tr>
                                    <td colspan="3" class="py-3 px-4 text-slate-700">
                                        Total Akumulasi Material Dikeluarkan
                                    </td>
                                    <td class="py-3 px-4 text-right text-slate-900 font-bold" style="font-variant-numeric: tabular-nums;">
                                        {{ format_quantity($materialUsage->items->sum('quantity')) }}
                                        <span class="text-[11px] text-slate-500 font-medium">unit</span>
                                    </td>
                                    <td class="py-3 px-4 text-slate-500 text-[11px]">
                                        {{ $materialUsage->items->count() }} Jenis Item
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    {{-- Stock Verification Footer Bar --}}
                    <div class="px-4 py-3 bg-slate-50/80 border-t border-slate-200 flex items-center justify-between flex-wrap gap-2 text-xs text-slate-600">
                        <div class="flex items-center gap-2">
                            <x-lucide-shield-check class="w-4 h-4 text-emerald-600 flex-shrink-0" />
                            <span>Stok fisik telah dipotong secara otomatis dari gudang <strong>{{ $materialUsage->warehouse?->name }}</strong>.</span>
                        </div>
                        <span class="text-[11px] text-slate-400 font-mono">
                            Status: {{ $materialUsage->status === 'cancelled' ? 'Dibatalkan' : 'Valid & Selesai' }}
                        </span>
                    </div>
                </div>

                {{-- ==================== 4. Serah Terima & Verifikasi (Digital Sign-off) ==================== --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-6">
                    {{-- Petugas Gudang --}}
                    <div class="sign-card">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">
                                Yang Menyerahkan (Petugas Gudang)
                            </span>
                            <div class="text-sm font-bold text-slate-900">
                                {{ $materialUsage->issuedBy?->name ?? 'Admin Gudang' }}
                            </div>
                            <div class="text-xs text-slate-500 mt-0.5">
                                Logistik &amp; Pergudangan PT Arsikon
                            </div>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-200/80 flex items-center justify-between text-[11px]">
                            <span class="text-slate-400">Verifikasi Sistem:</span>
                            <span class="text-emerald-700 font-semibold flex items-center gap-1">
                                <x-lucide-check class="w-3.5 h-3.5" /> {{ $materialUsage->created_at?->format('d/m/Y H:i') ?? '-' }}
                            </span>
                        </div>
                    </div>

                    {{-- Pemakai --}}
                    <div class="sign-card">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">
                                Yang Menerima / Pemakai
                            </span>
                            <div class="text-sm font-bold text-slate-900">
                                {{ $materialUsage->recipient_name }}
                            </div>
                            <div class="text-xs text-slate-500 mt-0.5">
                                Mandor / Tukang &bull; Zona: {{ $materialUsage->job_section ?: 'Pekerjaan Lapangan' }}
                            </div>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-200/80 flex items-center justify-between text-[11px]">
                            <span class="text-slate-400">Tujuan Pemakaian:</span>
                            <span class="text-slate-700 font-semibold truncate max-w-[200px]">
                                {{ $materialUsage->job_section ?: 'Operasional Proyek' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ==================== 5. Riwayat & Jejak Audit (Full-width Activity Deck) ==================== --}}
        <div class="bg-white border border-slate-200 rounded-2xl shadow-xs overflow-hidden">
            <div class="px-5 py-3.5 bg-slate-50/80 border-b border-slate-200 flex items-center justify-between flex-wrap gap-2">
                <div class="flex items-center gap-2">
                    <x-lucide-history class="w-4 h-4 text-orange-600" />
                    <h2 class="text-xs sm:text-sm font-bold text-slate-800 uppercase tracking-wider m-0">
                        Riwayat &amp; Jejak Audit
                    </h2>
                </div>
                <span class="text-xs text-slate-500 font-medium">
                    {{ $materialUsage->status === 'cancelled' ? '3 Langkah Tercatat' : '2 Langkah Tercatat' }}
                </span>
            </div>

            <div class="p-5">
                <div class="audit-track">
                    {{-- Step 1 --}}
                    <div class="audit-step">
                        <div class="flex items-center gap-2 mb-1.5">
                            <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                            <span class="text-xs font-bold text-slate-900">Pengeluaran Dicatat</span>
                        </div>
                        <div class="text-[11px] text-slate-600 leading-relaxed">
                            Oleh <strong>{{ $materialUsage->issuedBy?->name ?? 'Petugas' }}</strong> pada {{ $materialUsage->created_at ? $materialUsage->created_at->format('d/m/Y H:i') : '-' }}.
                        </div>
                        <div class="text-[10.5px] text-slate-500 mt-2 pt-2 border-t border-slate-200/60">
                            Pemakai: <strong>{{ $materialUsage->recipient_name }}</strong>
                        </div>
                    </div>

                    {{-- Step 2 --}}
                    <div class="audit-step">
                        <div class="flex items-center gap-2 mb-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                            <span class="text-xs font-bold text-slate-900">Stok Gudang Dipotong</span>
                        </div>
                        <div class="text-[11px] text-slate-600 leading-relaxed">
                            Sebanyak <strong>{{ $materialUsage->items->count() }} jenis material</strong> resmi dikeluarkan dari {{ $materialUsage->warehouse?->name ?? 'Gudang' }}.
                        </div>
                        <div class="text-[10.5px] text-emerald-700 font-medium mt-2 pt-2 border-t border-slate-200/60">
                            Terpotong Otomatis
                        </div>
                    </div>

                    {{-- Step 3: MR if linked --}}
                    @if($materialUsage->materialRequest)
                    <div class="audit-step">
                        <div class="flex items-center gap-2 mb-1.5">
                            <span class="w-2 h-2 rounded-full bg-orange-600"></span>
                            <span class="text-xs font-bold text-slate-900">Referensi PO Lapangan</span>
                        </div>
                        <div class="text-[11px] text-slate-600 leading-relaxed">
                            Terhubung dengan MR <a href="{{ route('material-requests.show', $materialUsage->materialRequest) }}" class="text-blue-600 font-bold hover:underline">#{{ $materialUsage->materialRequest->request_number }}</a>.
                        </div>
                        <div class="text-[10.5px] text-slate-500 mt-2 pt-2 border-t border-slate-200/60">
                            Pemohon: {{ $materialUsage->materialRequest->requestedBy?->name ?? 'Staf' }}
                        </div>
                    </div>
                    @endif

                    {{-- Step 4: Cancelled if cancelled --}}
                    @if($materialUsage->status === 'cancelled')
                    <div class="audit-step bg-red-50/70 border-red-200">
                        <div class="flex items-center gap-2 mb-1.5">
                            <span class="w-2 h-2 rounded-full bg-red-600"></span>
                            <span class="text-xs font-bold text-red-900">Transaksi Dibatalkan</span>
                        </div>
                        <div class="text-[11px] text-red-700 leading-relaxed">
                            Oleh <strong>{{ $materialUsage->cancelledBy?->name ?? '-' }}</strong> pada {{ $materialUsage->cancelled_at ? $materialUsage->cancelled_at->format('d/m/Y H:i') : '-' }}.
                        </div>
                        @if($materialUsage->cancellation_reason)
                        <div class="text-[10.5px] text-red-800 mt-2 pt-2 border-t border-red-200/60 truncate">
                            {{ $materialUsage->cancellation_reason }}
                        </div>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ==================== 6. Modal Pembatalan (Clean & Accessible) ==================== --}}
        @if($materialUsage->status === 'completed')
            @can('cancel material usages')
            <div x-show="showCancelModal" style="display: none;" 
                 class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0">
                <div class="bg-white rounded-2xl max-w-md w-full p-5 shadow-xl border border-slate-200 space-y-4"
                     @click.away="showCancelModal = false">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-red-100 text-red-600 flex items-center justify-center">
                                <x-lucide-ban class="w-4 h-4" />
                            </div>
                            <h3 class="text-sm font-bold text-slate-900 m-0">Batalkan Pengeluaran Material</h3>
                        </div>
                        <button type="button" @click="showCancelModal = false" class="text-slate-400 hover:text-slate-600">
                            <x-lucide-x class="w-4 h-4" />
                        </button>
                    </div>

                    <p class="text-xs text-slate-600 leading-relaxed">
                        Membatalkan transaksi <strong>#{{ $materialUsage->usage_number }}</strong> akan mengembalikan seluruh kuantitas material ke stok fisik gudang <strong>{{ $materialUsage->warehouse?->name }}</strong>.
                    </p>

                    <form method="POST" action="{{ route('material-usages.cancel', $materialUsage) }}"
                          data-confirm="Apakah Anda yakin ingin membatalkan transaksi pengeluaran material ini? Seluruh stok akan dikembalikan ke gudang fisik."
                          data-confirm-title="Konfirmasi Pembatalan Transaksi"
                          data-confirm-btn="Ya, Batalkan Transaksi"
                          data-confirm-danger="true">
                        @csrf
                        <div class="mb-4">
                            <label class="form-label text-xs font-bold text-slate-700 block mb-1">
                                Alasan Pembatalan <span class="text-red-500">*</span>
                            </label>
                            <textarea name="cancellation_reason" class="form-control text-xs w-full p-2.5 rounded-lg border border-red-200 focus:border-red-400 focus:ring-1 focus:ring-red-400" rows="3" required
                                placeholder="Tuliskan alasan pembatalan transaksi pengeluaran material ini..."></textarea>
                            @error('cancellation_reason')
                                <div class="text-red-600 text-[11px] mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                            <button type="button" @click="showCancelModal = false" class="btn btn-light btn-sm text-xs px-3 py-1.5 rounded-lg border text-slate-600">
                                Batal
                            </button>
                            <button type="submit" class="btn btn-danger btn-sm text-xs px-3.5 py-1.5 rounded-lg bg-red-600 hover:bg-red-700 text-white font-semibold flex items-center gap-1.5 shadow-xs">
                                <x-lucide-ban class="w-3.5 h-3.5" />
                                <span>Konfirmasi Batalkan</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            @endcan
        @endif
    </div>
</x-app-layout>
