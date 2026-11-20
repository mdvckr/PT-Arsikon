<x-app-layout>
    <x-slot name="title">Detail Pengajuan Kas Proyek #{{ $balanceRequest->request_number ?? ('PKL-' . $balanceRequest->id) }}</x-slot>

    @php
        $user = auth()->user();
        $isCanApprove = $user && $user->hasAnyRole(['Admin Pengadaan', 'Admin Pengadaan', 'Super Admin']);
        $backUrl = $isRoleAdminPOProyek ? route('project-balance-requests.index') : route('payments.balance-requests.index');
        $currentBalance = (float) ($balanceRequest->project?->operational_balance ?? 0);
        $projectedBalance = $currentBalance + (float) $balanceRequest->amount;
    @endphp

    {{-- Header & Breadcrumbs --}}
    <div class="mb-6">
        <div class="flex items-center gap-2 text-xs text-slate-500 mb-2">
            <a href="{{ $backUrl }}" class="text-blue-600 hover:underline">
                {{ $isRoleAdminPOProyek ? 'Kas Proyek' : 'Keuangan' }}
            </a>
            <span>/</span>
            <a href="{{ $backUrl }}" class="text-blue-600 hover:underline">Permintaan Saldo Kas</a>
            <span>/</span>
            <span class="text-slate-700 font-medium">Detail #{{ $balanceRequest->request_number ?? ('PKL-' . $balanceRequest->id) }}</span>
        </div>

        <div class="flex justify-between items-center flex-wrap gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ $backUrl }}" class="inline-flex items-center justify-center w-9 h-9 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 transition shadow-xs">
                    <x-lucide-arrow-left class="w-4 h-4" />
                </a>
                <div>
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <h1 class="text-xl font-extrabold text-slate-900 tracking-tight" data-animate="header">
                            Pengajuan Kas #{{ $balanceRequest->request_number ?? ('PKL-' . $balanceRequest->id) }}
                        </h1>

                        {{-- Urgency Badge --}}
                        @if($balanceRequest->urgency === 'urgent')
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-700 border border-rose-200 uppercase tracking-wide">
                                🚨 Urgent
                            </span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600 border border-slate-200">
                                Normal
                            </span>
                        @endif

                        {{-- Status Badge --}}
                        @if($balanceRequest->status === 'pending')
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-300">
                                <x-lucide-clock class="w-3.5 h-3.5 text-amber-600" /> Menunggu Persetujuan PO Pusat
                            </span>
                        @elseif($balanceRequest->status === 'approved')
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-300">
                                <x-lucide-circle-check class="w-3.5 h-3.5 text-emerald-600" /> Disetujui &amp; Dicairkan
                            </span>
                        @elseif($balanceRequest->status === 'rejected')
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-rose-800 border border-rose-300">
                                <x-lucide-circle-x class="w-3.5 h-3.5 text-rose-600" /> Ditolak
                            </span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-500 mt-1">
                        Diajukan pada {{ $balanceRequest->created_at->format('d F Y, H:i') }} WIB oleh {{ $balanceRequest->signer_requester ?: ($balanceRequest->requester?->name ?? 'Admin Pengadaan Proyek') }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2.5 flex-wrap">
                <a href="{{ route('payments.balance-requests.print', $balanceRequest) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold shadow-xs transition">
                    <x-lucide-printer class="w-4 h-4 text-slate-500" /> Cetak Dokumen (A4)
                </a>

                @if($balanceRequest->status === 'pending' && $isCanApprove)
                    <button type="button" onclick="promptRejectRequest({{ $balanceRequest->id }}, '{{ addslashes($balanceRequest->project?->name) }}')" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-rose-200 bg-white hover:bg-rose-50 text-rose-600 text-xs font-bold shadow-xs transition">
                        <x-lucide-x class="w-4 h-4" /> Tolak
                    </button>
                    <button type="button" onclick="promptApproveRequest({{ $balanceRequest->id }}, '{{ addslashes($balanceRequest->project?->name) }}', '{{ number_format($balanceRequest->amount, 0, ',', '.') }}')" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-sm shadow-emerald-600/20 transition">
                        <x-lucide-circle-check class="w-4 h-4" /> Setujui &amp; Cairkan Saldo
                    </button>
                @endif
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="mb-5 bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl flex items-center gap-3 text-sm">
        <x-lucide-circle-check class="w-5 h-5 text-emerald-600 shrink-0" />
        <span class="font-medium">{{ session('success') }}</span>
    </div>
    @endif

    {{-- Main Container Full Width (Rata Kanan Kiri) --}}
    <div class="w-full space-y-6" data-animate="panel">

        {{-- 4 Executive Metric Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- 1. Nominal Pengajuan --}}
            <div class="bg-emerald-50/70 rounded-2xl border border-emerald-200/90 p-4 shadow-2xs">
                <span class="text-[10.5px] font-bold text-emerald-800 uppercase tracking-wider block">NOMINAL PENGAJUAN KAS</span>
                <span class="text-xl font-extrabold text-emerald-700 mt-1 block font-mono">
                    Rp {{ number_format($balanceRequest->amount, 0, ',', '.') }}
                </span>
                <span class="text-[11px] text-emerald-600 mt-1 block">Total estimasi belanja nota</span>
            </div>

            {{-- 2. Proyek Tujuan --}}
            <div class="bg-white rounded-2xl border border-slate-200/90 p-4 shadow-2xs">
                <span class="text-[10.5px] font-bold text-slate-500 uppercase tracking-wider block">PROYEK TUJUAN</span>
                <span class="text-sm font-bold text-slate-900 mt-1 block truncate" title="{{ $balanceRequest->project?->name ?? '-' }}">
                    {{ $balanceRequest->project?->name ?? '-' }}
                </span>
                <span class="text-[11px] text-slate-400 font-mono mt-1 block">
                    Kode: {{ $balanceRequest->project?->code ?? 'PRJ' }}
                </span>
            </div>

            {{-- 3. Admin Pemohon --}}
            <div class="bg-white rounded-2xl border border-slate-200/90 p-4 shadow-2xs">
                <span class="text-[10.5px] font-bold text-slate-500 uppercase tracking-wider block">ADMIN PEMOHON (LAPANGAN)</span>
                <span class="text-sm font-bold text-slate-800 mt-1 block truncate" title="{{ $balanceRequest->signer_requester ?: ($balanceRequest->requester?->name ?? 'Admin Pengadaan Proyek') }}">
                    {{ $balanceRequest->signer_requester ?: ($balanceRequest->requester?->name ?? 'Admin Pengadaan Proyek') }}
                </span>
                <span class="text-[11px] text-slate-500 mt-1 block">
                    Site Mgr: {{ $balanceRequest->signer_site_manager ?? '-' }}
                </span>
            </div>

            {{-- 4. Saldo Kas & Simulasi --}}
            <div class="bg-white rounded-2xl border border-slate-200/90 p-4 shadow-2xs">
                <span class="text-[10.5px] font-bold text-slate-500 uppercase tracking-wider block">SALDO KAS OPERASIONAL SAAT INI</span>
                <span class="text-sm font-bold text-slate-800 mt-1 block font-mono">
                    Rp {{ number_format($currentBalance, 0, ',', '.') }}
                </span>
                @if($balanceRequest->status === 'pending')
                    <span class="text-[10.5px] font-semibold text-emerald-600 mt-1 block">
                        Simulasi Sesudah: Rp {{ number_format($projectedBalance, 0, ',', '.') }}
                    </span>
                @else
                    <span class="text-[10.5px] text-slate-400 mt-1 block">
                        Status transaksi final
                    </span>
                @endif
            </div>
        </div>

        {{-- Section 1: Keperluan & Deskripsi Pengajuan --}}
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-5">
            <div class="flex items-center gap-2 mb-2 text-slate-900 font-bold text-sm">
                <x-lucide-clipboard-list class="w-4 h-4 text-amber-600" />
                <h2>Keperluan &amp; Alasan Permohonan Kas Operasional</h2>
            </div>
            <p class="text-xs text-slate-700 leading-relaxed bg-slate-50/70 rounded-xl p-3.5 border border-slate-100">
                {{ $balanceRequest->purpose ?: 'Tidak ada keterangan tambahan.' }}
            </p>
        </div>

        {{-- Section 2: Tabel Rincian Kebutuhan Belanja / Nota --}}
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden">
            <div class="bg-slate-50/70 border-b border-slate-200/90 px-6 py-4 flex items-center justify-between flex-wrap gap-2">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-700 flex items-center justify-center">
                        <x-lucide-receipt class="w-4 h-4" />
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">Rincian Barang &amp; Kebutuhan Belanja Nota</h2>
                        <p class="text-[11px] text-slate-500">Daftar item belanja nota operasional yang diajukan oleh Admin Pengadaan Proyek</p>
                    </div>
                </div>
                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-slate-100 text-slate-700">
                    {{ $balanceRequest->items->count() }} Item Rincian
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-slate-50/90 text-slate-600 border-b border-slate-200 text-[11px] uppercase tracking-wider font-semibold">
                            <th class="py-3 px-4 text-center w-12">No</th>
                            <th class="py-3 px-4 min-w-[220px]">Nama Barang / Uraian Belanja</th>
                            <th class="py-3 px-4 text-center w-28">Kuantitas</th>
                            <th class="py-3 px-4 text-right w-36">Est. Harga Satuan</th>
                            <th class="py-3 px-4 text-right w-40">Subtotal</th>
                            <th class="py-3 px-4 min-w-[180px]">Catatan / Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($balanceRequest->items as $idx => $item)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3 px-4 text-center text-slate-400 font-mono">{{ $idx + 1 }}</td>
                            <td class="py-3 px-4 font-bold text-slate-800">
                                {{ $item->item_name }}
                            </td>
                            <td class="py-3 px-4 text-center font-medium text-slate-700">
                                {{ (float)$item->quantity }} {{ $item->unit }}
                            </td>
                            <td class="py-3 px-4 text-right font-mono text-slate-600">
                                Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right font-mono font-bold text-emerald-700">
                                Rp {{ number_format($item->total_price, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-slate-500 text-[11px]">
                                {{ $item->notes ?: '-' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-8 text-slate-400">
                                Tidak ada rincian item barang spesifik (pengajuan gelondongan).
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="bg-emerald-50/50 border-t-2 border-emerald-200/80 font-bold text-slate-900 text-xs">
                            <td colspan="4" class="py-3 px-4 text-right uppercase tracking-wider text-slate-600">
                                Total Pengajuan Kas Lapangan:
                            </td>
                            <td class="py-3 px-4 text-right font-mono text-base font-extrabold text-emerald-800">
                                Rp {{ number_format($balanceRequest->amount, 0, ',', '.') }}
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- Section 3: Status & Jejak Audit Persetujuan --}}
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-5">
            <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider mb-3.5 flex items-center gap-2">
                <x-lucide-history class="w-4 h-4 text-slate-500" /> Jejak Verifikasi &amp; Dokumen
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
                <div class="bg-slate-50/70 rounded-xl p-3.5 border border-slate-100">
                    <span class="text-[10.5px] font-bold text-slate-500 uppercase tracking-wider block">WAKTU PENGAJUAN</span>
                    <span class="font-semibold text-slate-800 mt-1 block">
                        {{ $balanceRequest->created_at->format('d F Y, H:i') }} WIB
                    </span>
                    <span class="text-[11px] text-slate-500 mt-0.5 block">
                        Diajukan oleh: {{ $balanceRequest->signer_requester ?: ($balanceRequest->requester?->name ?? 'Admin Pengadaan Proyek') }}
                    </span>
                </div>

                <div class="bg-slate-50/70 rounded-xl p-3.5 border border-slate-100">
                    <span class="text-[10.5px] font-bold text-slate-500 uppercase tracking-wider block">STATUS OTORISASI PO PUSAT</span>
                    @if($balanceRequest->status === 'pending')
                        <span class="font-bold text-amber-700 mt-1 block">
                            ⏳ Menunggu Tinjauan PO Pusat
                        </span>
                        <span class="text-[11px] text-slate-500 mt-0.5 block">Belum diproses</span>
                    @elseif($balanceRequest->status === 'approved')
                        <span class="font-bold text-emerald-700 mt-1 block">
                            ✅ Disetujui &amp; Dicairkan
                        </span>
                        <span class="text-[11px] text-slate-500 mt-0.5 block">
                            Oleh: {{ $balanceRequest->approver?->name ?? 'Admin Pengadaan Pusat' }}
                        </span>
                    @elseif($balanceRequest->status === 'rejected')
                        <span class="font-bold text-rose-700 mt-1 block">
                            ❌ Pengajuan Ditolak
                        </span>
                        <span class="text-[11px] text-slate-500 mt-0.5 block">
                            Oleh: {{ $balanceRequest->approver?->name ?? 'Admin Pengadaan Pusat' }}
                        </span>
                    @endif
                </div>

                <div class="bg-slate-50/70 rounded-xl p-3.5 border border-slate-100">
                    <span class="text-[10.5px] font-bold text-slate-500 uppercase tracking-wider block">REFERENSI / CATATAN</span>
                    @if($balanceRequest->status === 'approved')
                        <span class="font-mono font-semibold text-slate-800 mt-1 block">
                            Ref: {{ $balanceRequest->reference_number ?? '-' }}
                        </span>
                        <span class="text-[11px] text-emerald-600 mt-0.5 block">Saldo berhasil dikreditkan ke kas proyek</span>
                    @elseif($balanceRequest->status === 'rejected')
                        <span class="font-semibold text-rose-700 mt-1 block">
                            Alasan Penolakan:
                        </span>
                        <span class="text-[11px] text-rose-600 mt-0.5 block">{{ $balanceRequest->rejection_reason ?? '-' }}</span>
                    @else
                        <span class="text-slate-400 mt-1 block font-mono">-</span>
                        <span class="text-[11px] text-slate-400 mt-0.5 block">Menunggu nomor referensi transfer</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Bottom Action Bar --}}
        <div class="flex items-center justify-between flex-wrap gap-3 pt-3 pb-6">
            <a href="{{ $backUrl }}" class="inline-flex items-center px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-xs transition">
                &larr; Kembali ke Daftar
            </a>

            <div class="flex items-center gap-2.5">
                <a href="{{ route('payments.balance-requests.print', $balanceRequest) }}" target="_blank" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold shadow-xs transition">
                    <x-lucide-printer class="w-4 h-4 text-slate-500" /> Cetak Formulir (A4)
                </a>

                @if($balanceRequest->status === 'pending' && $isCanApprove)
                    <button type="button" onclick="promptRejectRequest({{ $balanceRequest->id }}, '{{ addslashes($balanceRequest->project?->name) }}')" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl border border-rose-200 bg-white hover:bg-rose-50 text-rose-600 text-xs font-bold shadow-xs transition">
                        <x-lucide-x class="w-4 h-4" /> Tolak Pengajuan
                    </button>
                    <button type="button" onclick="promptApproveRequest({{ $balanceRequest->id }}, '{{ addslashes($balanceRequest->project?->name) }}', '{{ number_format($balanceRequest->amount, 0, ',', '.') }}')" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-md shadow-emerald-600/20 transition">
                        <x-lucide-circle-check class="w-4 h-4" /> Setujui &amp; Cairkan Saldo
                    </button>
                @endif
            </div>
        </div>

    </div>

    {{-- Form Hidden untuk Persetujuan / Penolakan --}}
    <form id="approveRequestForm" method="POST" style="display:none;">
        @csrf
        <input type="hidden" name="reference_number" id="approveRefNumberInput">
        <input type="hidden" name="notes" id="approveNotesInput">
    </form>

    <form id="rejectRequestForm" method="POST" style="display:none;">
        @csrf
        <input type="hidden" name="rejection_reason" id="rejectReasonInput">
    </form>

    @push('scripts')
    <script>
        function promptApproveRequest(requestId, projectName, formattedAmount) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Setujui Pengajuan Kas Proyek?',
                    html: `
                        <div class="text-left text-xs text-slate-600 space-y-2 mt-2">
                            <p>Anda akan menyetujui pengajuan kas untuk <b>${projectName}</b>:</p>
                            <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-2.5 text-center">
                                <span class="text-xs text-emerald-700 font-bold uppercase tracking-wider block">Nominal Pencairan</span>
                                <span class="text-lg font-black text-emerald-800 font-mono">Rp ${formattedAmount}</span>
                            </div>
                            <p class="text-[11px] text-slate-500">Saldo kas proyek akan otomatis bertambah saat disetujui.</p>
                            <label class="block font-semibold text-slate-700 pt-1">No. Referensi / Bukti Transfer (Opsional):</label>
                        </div>
                    `,
                    icon: 'question',
                    input: 'text',
                    inputPlaceholder: 'Contoh: TRF-BCA-981290 / Kas Tunai',
                    showCancelButton: true,
                    confirmButtonColor: '#059669',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Ya, Setujui & Cairkan',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const form = document.getElementById('approveRequestForm');
                        form.action = '/payments/balance-requests/' + requestId + '/approve';
                        document.getElementById('approveRefNumberInput').value = result.value ? result.value.trim() : '';
                        form.submit();
                    }
                });
            } else {
                if (confirm(`Setujui pengajuan kas untuk ${projectName} sebesar Rp ${formattedAmount}?`)) {
                    const ref = prompt('Masukkan No. Referensi Transfer / Bukti (Opsional):', '');
                    const form = document.getElementById('approveRequestForm');
                    form.action = '/payments/balance-requests/' + requestId + '/approve';
                    document.getElementById('approveRefNumberInput').value = ref ? ref.trim() : '';
                    form.submit();
                }
            }
        }

        function promptRejectRequest(requestId, projectName) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Tolak Pengajuan Kas Proyek?',
                    html: `Anda akan menolak pengajuan kas untuk proyek <b>${projectName}</b>.<br><span style="font-size:12px;color:#64748b;">Harap tuliskan alasan penolakan secara jelas:</span>`,
                    icon: 'warning',
                    input: 'textarea',
                    inputPlaceholder: 'Contoh: Rincian belanja belum lengkap / nota fisik sebelumnya belum diserahkan...',
                    inputAttributes: {
                        'aria-label': 'Alasan Penolakan',
                        'rows': 3
                    },
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Ya, Tolak Pengajuan',
                    cancelButtonText: 'Batal',
                    inputValidator: (value) => {
                        if (!value || !value.trim()) {
                            return 'Alasan penolakan wajib diisi!';
                        }
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        const form = document.getElementById('rejectRequestForm');
                        form.action = '/payments/balance-requests/' + requestId + '/reject';
                        document.getElementById('rejectReasonInput').value = result.value.trim();
                        form.submit();
                    }
                });
            } else {
                const reason = prompt('Masukkan alasan penolakan permintaan saldo kas untuk proyek ' + projectName + ':');
                if (reason !== null && reason.trim() !== '') {
                    const form = document.getElementById('rejectRequestForm');
                    form.action = '/payments/balance-requests/' + requestId + '/reject';
                    document.getElementById('rejectReasonInput').value = reason.trim();
                    form.submit();
                }
            }
        }
    </script>
    @endpush
</x-app-layout>
