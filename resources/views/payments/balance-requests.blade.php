<x-app-layout>
    <x-slot name="title">Permintaan Saldo Kas Operasional Proyek</x-slot>

    {{-- Breadcrumbs & Header --}}
    <div class="mb-6">
        <div class="flex items-center gap-2 text-xs text-slate-500 mb-2">
            <a href="{{ route('payments.index') }}" class="text-blue-600 hover:underline">Keuangan</a>
            <span>/</span>
            <span class="text-slate-700 font-medium">Permintaan Saldo Kas Operasional Proyek</span>
        </div>

        <div class="flex justify-between items-center flex-wrap gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('payments.index') }}" class="inline-flex items-center justify-center w-10 h-10 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 transition shadow-2xs">
                    <x-lucide-arrow-left class="w-4 h-4" />
                </a>
                <div>
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <h1 class="text-xl font-extrabold text-slate-900 tracking-tight" data-animate="header">
                            Permintaan Saldo Kas Operasional Proyek
                        </h1>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-800 border border-amber-200 shadow-2xs">
                            <x-lucide-calendar class="w-3.5 h-3.5 text-amber-600" />
                            Siklus Bulanan
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Daftar seluruh permohonan dana kas belanja nota harian Admin Pengadaan Proyek, tersusun rapi per bulan kalender operasional.
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2.5 flex-wrap">
                <button type="button" onclick="toggleAllMonths(true)" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-2xs transition">
                    <x-lucide-chevrons-down class="w-3.5 h-3.5 text-slate-500" /> Buka Semua
                </button>
                <button type="button" onclick="toggleAllMonths(false)" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-2xs transition">
                    <x-lucide-chevrons-up class="w-3.5 h-3.5 text-slate-500" /> Tutup Semua
                </button>
                <a href="{{ route('payments.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition">
                    Kembali ke Keuangan
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="mb-5 bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl flex items-center gap-3 text-sm shadow-2xs">
        <x-lucide-circle-check class="w-5 h-5 text-emerald-600 shrink-0" />
        <span class="font-medium">{{ session('success') }}</span>
    </div>
    @endif

    {{-- 4 Executive Metric Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        {{-- 1. Menunggu Approval --}}
        <div class="bg-white rounded-2xl border border-slate-200/90 p-4 shadow-2xs flex flex-col justify-between">
            <div>
                <span class="text-[10.5px] font-bold text-slate-500 uppercase tracking-wider block">MENUNGGU APPROVAL ADMIN PENGADAAN</span>
                <div class="flex items-center gap-2 mt-1.5">
                    <span class="text-2xl font-black font-mono {{ $metrics['pending_count'] > 0 ? 'text-amber-600' : 'text-slate-800' }}">
                        {{ $metrics['pending_count'] }}
                    </span>
                    <span class="text-xs text-slate-500 font-medium">Pengajuan</span>
                    @if($metrics['pending_count'] > 0)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200 animate-pulse">
                            Perlu Tindakan
                        </span>
                    @endif
                </div>
            </div>
            <span class="text-[11px] text-slate-400 mt-2 block">Permohonan dana kas yang belum diproses</span>
        </div>

        {{-- 2. Total Dana Diajukan --}}
        <div class="bg-white rounded-2xl border border-slate-200/90 p-4 shadow-2xs flex flex-col justify-between">
            <div>
                <span class="text-[10.5px] font-bold text-slate-500 uppercase tracking-wider block">TOTAL DANA DIAJUKAN</span>
                <span class="text-2xl font-black text-slate-900 mt-1.5 block font-mono">
                    Rp {{ number_format($metrics['total_amount'], 0, ',', '.') }}
                </span>
            </div>
            <span class="text-[11px] text-slate-400 mt-2 block">Akumulasi seluruh permohonan dana kas</span>
        </div>

        {{-- 3. Total Dana Dicairkan --}}
        <div class="bg-emerald-50/70 rounded-2xl border border-emerald-200/90 p-4 shadow-2xs flex flex-col justify-between">
            <div>
                <span class="text-[10.5px] font-bold text-emerald-800 uppercase tracking-wider block">TOTAL DANA SUDAH DICAIRKAN</span>
                <span class="text-2xl font-black text-emerald-700 mt-1.5 block font-mono">
                    Rp {{ number_format($metrics['approved_amount'], 0, ',', '.') }}
                </span>
            </div>
            <span class="text-[11px] text-emerald-600 mt-2 block">{{ $metrics['approved_count'] }} pengajuan telah disetujui</span>
        </div>

        {{-- 4. Total Transaksi --}}
        <div class="bg-white rounded-2xl border border-slate-200/90 p-4 shadow-2xs flex flex-col justify-between">
            <div>
                <span class="text-[10.5px] font-bold text-slate-500 uppercase tracking-wider block">TOTAL TRANSAKSI PENGAJUAN</span>
                <div class="flex items-center gap-2 mt-1.5">
                    <span class="text-2xl font-black text-slate-900 font-mono">
                        {{ $metrics['total_count'] }}
                    </span>
                    <span class="text-xs text-slate-500 font-medium">Pengajuan Terdaftar</span>
                </div>
            </div>
            <span class="text-[11px] text-slate-400 mt-2 block">Riwayat pengajuan kas lapangan</span>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="bg-white rounded-2xl border border-slate-200/90 p-4 mb-6 shadow-2xs">
        <form method="GET" action="{{ route('payments.balance-requests.index') }}" class="flex items-center justify-between flex-wrap gap-3">
            <div class="flex items-center gap-3 flex-wrap flex-1">
                {{-- Filter Proyek --}}
                <div class="min-w-[220px]">
                    <select name="project_id" onchange="this.form.submit()" class="w-full px-3 py-2 text-xs bg-white border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 shadow-2xs">
                        <option value="">-- Semua Proyek --</option>
                        @foreach($projects as $prj)
                            <option value="{{ $prj->id }}" {{ request('project_id') == $prj->id ? 'selected' : '' }}>
                                {{ $prj->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Filter Status --}}
                <div class="min-w-[190px]">
                    <select name="status" onchange="this.form.submit()" class="w-full px-3 py-2 text-xs bg-white border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 shadow-2xs">
                        <option value="">-- Semua Status --</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Menunggu Persetujuan</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Disetujui</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                    </select>
                </div>

                @if(request()->hasAny(['project_id', 'status']))
                    <a href="{{ route('payments.balance-requests.index') }}" class="px-3 py-2 rounded-xl text-xs font-semibold text-rose-600 hover:bg-rose-50 border border-rose-200 transition">
                        Reset Filter
                    </a>
                @endif
            </div>

            <span class="text-xs text-slate-500 font-medium bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-200/80">
                Menampilkan <strong class="text-slate-800">{{ $balanceRequests->count() }}</strong> transaksi pengajuan
            </span>
        </form>
    </div>

    {{-- Monthly Accordion List --}}
    @php
        $monthsData = $groupedBalanceRequestMonths ?? $groupedBalanceRequestWeeks ?? collect();
    @endphp

    <div class="space-y-4" data-animate="panel">
        @if($monthsData->isNotEmpty())
            @foreach($monthsData as $mKey => $month)
            @php
                $isExpanded = $month->has_pending || $month->is_current_month || $loop->first;
            @endphp
            <div class="rounded-2xl border border-slate-200/90 overflow-hidden bg-white shadow-2xs">
                {{-- Month Header Bar (Clickable) --}}
                <div onclick="toggleMonth('{{ $mKey }}')" 
                     class="cursor-pointer transition-colors px-6 py-3.5 bg-slate-50/80 hover:bg-slate-100/90 flex items-center justify-between flex-wrap gap-3 select-none border-b border-slate-200">
                    
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-orange-50 border border-orange-200/60 text-orange-600 flex items-center justify-center shrink-0">
                            <x-lucide-calendar class="w-4 h-4" />
                        </div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h2 class="text-sm sm:text-base font-bold text-slate-900 m-0 tracking-tight">
                                {{ $month->month_label }}
                            </h2>
                            @if($month->is_current_month)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-sky-100 text-sky-800 border border-sky-200 uppercase tracking-wide">
                                    Bulan Ini
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-xs font-medium text-slate-600 bg-white border border-slate-200 px-2.5 py-1 rounded-lg">
                            {{ $month->items->count() }} Pengajuan
                        </span>

                        @if($month->has_pending)
                            <span class="text-xs font-semibold text-amber-800 bg-amber-50 border border-amber-300 px-2.5 py-1 rounded-lg flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-600"></span>
                                {{ $month->pending_count }} Menunggu Approval
                            </span>
                        @endif

                        <span class="text-xs font-mono font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2.5 py-1 rounded-lg">
                            Rp {{ number_format($month->total_amount, 0, ',', '.') }}
                        </span>

                        <div class="w-7 h-7 rounded-lg bg-white border border-slate-200 flex items-center justify-center ml-1">
                            <x-lucide-chevron-down id="icon-month-{{ $mKey }}" 
                                                   class="month-toggle-icon w-4 h-4 text-slate-500 transition-transform duration-200" 
                                                   style="{{ $isExpanded ? 'transform:rotate(0deg);' : 'transform:rotate(-90deg);' }}" />
                        </div>
                    </div>
                </div>

                {{-- Month Table Container --}}
                <div id="content-month-{{ $mKey }}" 
                     class="month-toggle-content overflow-x-auto" 
                     style="{{ $isExpanded ? 'display:block;' : 'display:none;' }}">
                    <table class="w-full text-left text-xs divide-y divide-slate-100">
                        <thead class="bg-slate-50/70 font-semibold text-slate-600 text-[11px] uppercase tracking-wider">
                            <tr>
                                <th scope="col" class="py-3 px-5 w-44">No. Pengajuan</th>
                                <th scope="col" class="py-3 px-5">Proyek &amp; Pemohon</th>
                                <th scope="col" class="py-3 px-5">Keperluan</th>
                                <th scope="col" class="py-3 px-5 text-right w-40">Nominal Kas</th>
                                <th scope="col" class="py-3 px-5 text-center w-36">Status</th>
                                <th scope="col" class="py-3 px-5 text-center w-36">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach($month->items as $reqItem)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                {{-- 1. No. Pengajuan --}}
                                <td class="py-3.5 px-5 align-middle">
                                    <div class="flex items-center gap-1.5 font-mono font-bold text-xs">
                                        <a href="{{ route('payments.balance-requests.show', $reqItem) }}" class="text-blue-600 hover:underline" title="Lihat Detail">
                                            {{ $reqItem->request_number ?? ('PKL-' . $reqItem->id) }}
                                        </a>
                                        @if($reqItem->urgency === 'urgent')
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-rose-50 text-rose-700 border border-rose-200">Urgent</span>
                                        @endif
                                    </div>
                                    <div class="text-[11px] text-slate-400 mt-0.5">
                                        {{ $reqItem->created_at->format('d/m/Y H:i') }} WIB
                                    </div>
                                </td>

                                {{-- 2. Proyek & Pemohon --}}
                                <td class="py-3.5 px-5 align-middle">
                                    <div class="font-semibold text-slate-900 text-xs truncate max-w-[280px]" title="{{ $reqItem->project?->name ?? '-' }}">
                                        {{ $reqItem->project?->name ?? '-' }}
                                    </div>
                                    <div class="text-[11px] text-slate-500 mt-0.5">
                                        Pemohon: {{ $reqItem->signer_requester ?: ($reqItem->requester?->name ?? 'Admin Pengadaan Proyek') }}
                                    </div>
                                </td>

                                {{-- 3. Keperluan --}}
                                <td class="py-3.5 px-5 align-middle">
                                    <div class="text-slate-800 text-xs">
                                        {{ $reqItem->purpose }}
                                    </div>
                                    @if($reqItem->items && $reqItem->items->count() > 0)
                                        <div class="text-[11px] text-slate-400 mt-0.5">
                                            {{ $reqItem->items->count() }} rincian barang
                                        </div>
                                    @endif
                                </td>

                                {{-- 4. Nominal Kas --}}
                                <td class="py-3.5 px-5 align-middle text-right">
                                    <div class="font-mono font-bold text-emerald-700 text-sm">
                                        Rp {{ number_format($reqItem->amount, 0, ',', '.') }}
                                    </div>
                                </td>

                                {{-- 5. Status Persetujuan --}}
                                <td class="py-3.5 px-5 align-middle text-center">
                                    @php
                                        $badgeStyles = [
                                            'pending' => 'bg-amber-50 text-amber-800 border-amber-300',
                                            'approved' => 'bg-emerald-50 text-emerald-800 border-emerald-300',
                                            'rejected' => 'bg-rose-50 text-rose-800 border-rose-300',
                                        ];
                                        $style = $badgeStyles[$reqItem->status] ?? 'bg-slate-100 text-slate-700 border-slate-200';
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $style }}">
                                        {{ $reqItem->status_label }}
                                    </span>
                                    @if($reqItem->status === 'approved' && $reqItem->approver)
                                        <div class="text-[10px] text-slate-400 mt-0.5">
                                            {{ $reqItem->approver->name }}
                                        </div>
                                    @elseif($reqItem->status === 'rejected' && $reqItem->rejection_reason)
                                        <div class="text-[10px] text-rose-600 mt-0.5 truncate max-w-[140px] mx-auto" title="{{ $reqItem->rejection_reason }}">
                                            {{ $reqItem->rejection_reason }}
                                        </div>
                                    @endif
                                </td>

                                {{-- 6. Aksi --}}
                                <td class="py-3.5 px-5 align-middle text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <a href="{{ route('payments.balance-requests.show', $reqItem) }}" 
                                           class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 transition" 
                                           title="Lihat Detail">
                                            <x-lucide-eye class="w-3.5 h-3.5 text-slate-500" />
                                            <span>Detail</span>
                                        </a>

                                        <a href="{{ route('payments.balance-requests.print', $reqItem) }}" target="_blank" 
                                           class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 transition" 
                                           title="Cetak Formulir (A4)">
                                            <x-lucide-printer class="w-3.5 h-3.5 text-slate-500" />
                                            <span>Cetak</span>
                                        </a>

                                        @if($reqItem->status === 'pending' && auth()->user()->hasAnyRole(['Admin Pengadaan', 'Admin Pengadaan', 'Super Admin']))
                                            <button type="button" onclick="promptApproveRequest({{ $reqItem->id }}, '{{ addslashes($reqItem->project?->name) }}', '{{ number_format($reqItem->amount, 0, ',', '.') }}')"
                                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium text-white bg-emerald-600 hover:bg-emerald-700 transition" 
                                                    title="Setujui dan Cairkan">
                                                <x-lucide-circle-check class="w-3.5 h-3.5" />
                                                <span>Setujui</span>
                                            </button>
                                            <button type="button" onclick="promptRejectRequest({{ $reqItem->id }}, '{{ addslashes($reqItem->project?->name) }}')"
                                                    class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-xs font-medium text-rose-600 bg-white hover:bg-rose-50 border border-rose-200 transition" 
                                                    title="Tolak Pengajuan">
                                                <x-lucide-x class="w-3.5 h-3.5" />
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endforeach
        @else
            <div class="bg-white rounded-2xl border border-slate-200/90 text-center py-16 px-4 shadow-2xs">
                <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto mb-3 text-slate-400">
                    <x-lucide-inbox class="w-6 h-6" />
                </div>
                <h3 class="font-bold text-sm text-slate-800">Belum Ada Permintaan Saldo Kas</h3>
                <p class="text-xs text-slate-500 mt-1">Permohonan dana kas belanja nota harian dari Admin Pengadaan Proyek ke PO Pusat akan tercantum rapi per bulan di sini.</p>
            </div>
        @endif
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
        function toggleMonth(monthKey) {
            const content = document.getElementById('content-month-' + monthKey);
            const icon = document.getElementById('icon-month-' + monthKey);
            if (!content) return;
            const isHidden = (content.style.display === 'none');
            content.style.display = isHidden ? 'block' : 'none';
            if (icon) {
                icon.style.transform = isHidden ? 'rotate(0deg)' : 'rotate(-90deg)';
            }
        }

        function toggleAllMonths(expand) {
            document.querySelectorAll('.month-toggle-content').forEach(c => {
                c.style.display = expand ? 'block' : 'none';
            });
            document.querySelectorAll('.month-toggle-icon').forEach(icon => {
                icon.style.transform = expand ? 'rotate(0deg)' : 'rotate(-90deg)';
            });
        }

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