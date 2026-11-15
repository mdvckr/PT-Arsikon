<x-app-layout>
    <x-slot name="title">Pengajuan Kas Saldo Proyek - Arsikon</x-slot>

    <div class="space-y-5">
        {{-- Header Bar --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white dark:bg-slate-900 p-4 sm:p-5 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs">
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h1 class="text-lg sm:text-xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                        Pengajuan Kas Saldo Proyek
                    </h1>
                    @if(isset($selectedProject) && $selectedProject)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg text-xs font-bold bg-blue-50 text-blue-800 dark:bg-blue-950/40 dark:text-blue-300 border border-blue-200/70 dark:border-blue-900">
                            <x-lucide-building-2 class="w-3.5 h-3.5 text-blue-600" /> {{ $selectedProject->name }}
                        </span>
                    @endif
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    Permohonan dana operasional kas belanja lapangan yang diajukan langsung ke Admin PO Pusat (Manajer / Kasir Pusat).
                </p>
            </div>

            <div class="flex items-center gap-2 flex-wrap shrink-0">
                <a href="{{ route('project-balance-requests.create', request()->only(['project_id'])) }}"
                   class="px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 dark:bg-white dark:hover:bg-slate-100 text-white dark:text-slate-900 text-xs font-bold inline-flex items-center gap-1.5 transition shadow-xs">
                    <x-lucide-plus class="w-4 h-4" />
                    <span>Buat Pengajuan Kas</span>
                </a>
            </div>
        </div>

        {{-- Sub Tab Navigasi PO Proyek --}}
        <div class="flex items-center gap-2 border-b border-slate-200 pb-2">
            <a href="{{ route('project-balance-requests.index', request()->only(['project_id'])) }}"
               class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5 bg-slate-900 text-white shadow-xs">
                <x-lucide-hand-coins style="width:14px;height:14px;" /> Pengajuan Kas Saldo Proyek
            </a>
        </div>

        {{-- Alerts --}}
        @if(session('success'))
            <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center justify-between">
                <span class="flex items-center gap-2"><x-lucide-circle-check class="w-4 h-4 text-emerald-600" /> {{ session('success') }}</span>
            </div>
        @endif
        @if(session('error'))
            <div class="p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold flex items-center justify-between">
                <span class="flex items-center gap-2"><x-lucide-circle-alert class="w-4 h-4 text-rose-600" /> {{ session('error') }}</span>
            </div>
        @endif

        {{-- Metric Summary Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5 text-xs">
            <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs">
                <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider flex items-center gap-1.5">
                    <x-lucide-wallet class="w-4 h-4 text-emerald-600" /> Sisa Saldo Kas Proyek
                </div>
                <div class="text-xl sm:text-2xl font-black text-emerald-700 dark:text-emerald-400 mt-1.5 tabular-nums">
                    Rp {{ number_format($selectedProject->operational_balance ?? 0, 0, ',', '.') }}
                </div>
                <div class="text-[11px] text-slate-400 mt-1">
                    Sisa dana tunai di tangan pemegang kas proyek
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs">
                <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider flex items-center gap-1.5">
                    <x-lucide-clock class="w-4 h-4 text-amber-500" /> Menunggu Persetujuan PO Pusat
                </div>
                <div class="text-xl sm:text-2xl font-black text-amber-700 dark:text-amber-400 mt-1.5 tabular-nums">
                    {{ $pendingCount }} <span class="text-sm font-semibold">Pengajuan</span>
                </div>
                <div class="text-[11px] text-slate-400 mt-1">
                    Sedang ditinjau Admin PO Pusat (tanpa butuh Owner)
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs">
                <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider flex items-center gap-1.5">
                    <x-lucide-circle-check class="w-4 h-4 text-blue-600" /> Total Berhasil Dicairkan
                </div>
                <div class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white mt-1.5 tabular-nums">
                    Rp {{ number_format($totalApproved, 0, ',', '.') }}
                </div>
                <div class="text-[11px] text-slate-400 mt-1">
                    {{ $approvedCount }} pengajuan telah dicairkan oleh PO Pusat
                </div>
            </div>
        </div>

        {{-- Table Container --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs overflow-hidden">
            <div class="px-5 py-3.5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between flex-wrap gap-2">
                <div class="flex items-center gap-2">
                    <h2 class="text-xs font-extrabold text-slate-900 dark:text-white uppercase tracking-wider">
                        Riwayat Pengajuan Kas Lapangan (PKL)
                    </h2>
                    <span class="text-[11px] font-mono text-slate-400">({{ $balanceRequests->total() }} data)</span>
                </div>

                @if(!$isRoleAdminPOProyek && $projects->count() > 1)
                <form method="GET" class="flex items-center gap-2 m-0">
                    <select name="project_id" onchange="this.form.submit()" class="text-xs h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-medium">
                        <option value="">-- Semua Proyek --</option>
                        @foreach($projects as $p)
                            <option value="{{ $p->id }}" {{ (isset($selectedProject) && $selectedProject->id == $p->id) ? 'selected' : '' }}>
                                {{ $p->name }}
                            </option>
                        @endforeach
                    </select>
                </form>
                @endif
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs border-collapse">
                    <thead>
                        <tr class="bg-slate-50/80 dark:bg-slate-800/60 border-b border-slate-200/90 dark:border-slate-700/80 text-[11px] font-bold text-slate-600 dark:text-slate-300">
                            <th class="py-2.5 px-3 text-center" style="width: 40px;">No</th>
                            <th class="py-2.5 px-3 text-start" style="width: 150px;">No. Pengajuan</th>
                            <th class="py-2.5 px-3 text-start" style="width: 110px;">Tanggal</th>
                            <th class="py-2.5 px-4 text-start">Uraian / Keperluan Belanja</th>
                            <th class="py-2.5 px-3 text-center" style="width: 90px;">Urgensi</th>
                            <th class="py-2.5 px-3 text-end" style="width: 130px;">Jumlah Dana</th>
                            <th class="py-2.5 px-3 text-center" style="width: 150px;">Status</th>
                            <th class="py-2.5 px-3 text-center" style="width: 120px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                        @forelse($balanceRequests as $idx => $req)
                        <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition">
                            <td class="text-center font-mono text-[11px] text-slate-400 py-3 px-3">
                                {{ $balanceRequests->firstItem() + $idx }}
                            </td>
                            <td class="py-3 px-3">
                                <a href="{{ route('project-balance-requests.show', $req) }}" class="font-mono font-bold text-slate-900 dark:text-white hover:text-blue-600 hover:underline transition" title="Lihat Detail Pengajuan">
                                    {{ $req->request_number ?? ('PKL-' . $req->id) }}
                                </a>
                                <div class="text-[10px] text-slate-400 mt-0.5">
                                    Oleh: {{ $req->requester?->name ?? 'Admin Proyek' }}
                                </div>
                            </td>
                            <td class="py-3 px-3 text-slate-700 dark:text-slate-300 whitespace-nowrap">
                                {{ $req->request_date ? $req->request_date->translatedFormat('d M Y') : '-' }}
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-semibold text-slate-900 dark:text-white">
                                    {{ $req->purpose }}
                                </div>
                                @if($req->items->count() > 0)
                                    <div class="flex items-center gap-1.5 mt-1 text-[11px] text-slate-500">
                                        <span class="inline-block px-1.5 py-0.2 rounded bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[10px]">
                                            {{ $req->items->count() }} item rincian
                                        </span>
                                        <span class="truncate max-w-sm">
                                            ({{ $req->items->pluck('item_name')->take(3)->implode(', ') }}@if($req->items->count() > 3), dsb.@endif)
                                        </span>
                                    </div>
                                @endif
                                @if($req->status === 'rejected' && $req->rejection_reason)
                                    <div class="mt-1 text-[11px] text-rose-600 bg-rose-50 dark:bg-rose-950/40 p-1.5 rounded-md border border-rose-200 dark:border-rose-900">
                                        <strong>Alasan Penolakan:</strong> {{ $req->rejection_reason }}
                                    </div>
                                @endif
                            </td>
                            <td class="text-center py-3 px-3">
                                @if($req->urgency === 'urgent')
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">Mendesak</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">Normal</span>
                                @endif
                            </td>
                            <td class="text-end py-3 px-3 font-extrabold text-slate-900 dark:text-white tabular-nums text-xs whitespace-nowrap">
                                Rp {{ number_format($req->amount, 0, ',', '.') }}
                            </td>
                            <td class="text-center py-3 px-3">
                                @if($req->status === 'approved')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10.5px] font-extrabold bg-emerald-50 text-emerald-800 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300">
                                        <x-lucide-check class="w-3 h-3 text-emerald-600" /> Disetujui PO Pusat
                                    </span>
                                    @if($req->approver)
                                        <div class="text-[9.5px] text-slate-400 mt-0.5">Oleh: {{ $req->approver->name }}</div>
                                    @endif
                                @elseif($req->status === 'rejected')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10.5px] font-extrabold bg-rose-50 text-rose-800 border border-rose-200 dark:bg-rose-950/40 dark:text-rose-300">
                                        <x-lucide-x class="w-3 h-3 text-rose-600" /> Ditolak
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10.5px] font-extrabold bg-amber-50 text-amber-800 border border-amber-200 dark:bg-amber-950/40 dark:text-amber-300">
                                        <x-lucide-clock class="w-3 h-3 text-amber-600" /> Menunggu PO Pusat
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-center">
                                <div class="inline-flex items-center gap-1">
                                    {{-- Detail Pengajuan --}}
                                    <a href="{{ route('project-balance-requests.show', $req) }}"
                                       class="px-2.5 py-1 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 text-slate-700 dark:text-slate-300 text-xs font-semibold inline-flex items-center gap-1 transition shadow-2xs"
                                       title="Lihat Detail & Rincian Barang">
                                        <x-lucide-eye class="w-3.5 h-3.5 text-slate-500" />
                                        <span>Detail</span>
                                    </a>

                                    {{-- Cetak Formulir PKL A4 --}}
                                    <a href="{{ route('project-balance-requests.print', $req) }}" target="_blank"
                                       class="px-2.5 py-1 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 text-slate-700 dark:text-slate-300 text-xs font-semibold inline-flex items-center gap-1 transition"
                                       title="Cetak Formulir Resmi A4 Ber-KOP">
                                        <x-lucide-printer class="w-3.5 h-3.5 text-slate-500" />
                                        <span>Cetak</span>
                                    </a>

                                    {{-- Tombol Approval Langsung untuk Admin PO Pusat (Jika status pending) --}}
                                    @if(auth()->user()->hasAnyRole(['Admin PO', 'Admin Pengadaan', 'Admin Pusat', 'Super Admin']) && $req->status === 'pending')
                                        <form method="POST" action="{{ route('project-balance-requests.approve', $req) }}" class="m-0 p-0 inline"
                                              onsubmit="return confirm('Setujui pengajuan kas #{{ $req->request_number }} sebesar Rp {{ number_format($req->amount, 0, ',', '.') }}? Saldo kas proyek akan bertambah seketika tanpa approval Owner.')">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold inline-flex items-center gap-1 transition shadow-2xs"
                                                    title="Setujui & Cairkan Langsung">
                                                <x-lucide-check class="w-3.5 h-3.5" />
                                                <span>Setujui</span>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-10 text-slate-400">
                                <x-lucide-hand-coins class="w-8 h-8 mx-auto text-slate-300 mb-2" />
                                <div class="font-bold text-slate-600 dark:text-slate-300">Belum ada Pengajuan Kas Proyek.</div>
                                <div class="text-[11px] mt-0.5">Jika saldo operasional menipis, klik tombol <strong>+ Buat Pengajuan Kas</strong> di atas.</div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($balanceRequests->hasPages())
                <div class="p-3.5 border-t border-slate-100 dark:border-slate-800">
                    {{ $balanceRequests->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
