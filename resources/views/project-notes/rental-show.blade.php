<x-app-layout>
    <x-slot name="title">{{ $rental->tool_name }} - Rekap Sewa Alat - Arsikon</x-slot>

    <div x-data="{
        showAddLogModal: false,
        showEditRentalModal: false,
        showEditLogModal: false,
        editLog: {
            id: null,
            usage_date: '',
            usage_duration: '',
            duration_unit: '{{ $rental->duration_unit ?: 'Jam' }}',
            activity_type: '',
            equipment_spec: '',
            notes: ''
        },
        openEditLog(log) {
            this.editLog = {
                id: log.id,
                usage_date: log.usage_date ? log.usage_date.substring(0, 10) : '',
                usage_duration: log.usage_duration,
                duration_unit: log.duration_unit,
                activity_type: log.activity_type,
                equipment_spec: log.equipment_spec || '',
                notes: log.notes || ''
            };
            this.showEditLogModal = true;
        }
    }" class="space-y-6">

        {{-- 1. Top Breadcrumb & Actions --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs">
            <div class="flex items-center gap-2 flex-wrap">
                <a href="{{ route('project-notes.index', ['project_id' => $rental->project_id]) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-200 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 transition">
                    <x-lucide-arrow-left class="w-3.5 h-3.5" /> Kembali ke Buku Catatan Proyek
                </a>
                <span class="text-slate-300 dark:text-slate-700 hidden sm:inline">|</span>
                <span class="inline-flex items-center gap-1 text-xs font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-950/40 px-2.5 py-1 rounded-lg border border-indigo-200/60 dark:border-indigo-900">
                    <x-lucide-building-2 class="w-3.5 h-3.5" /> {{ $rental->project?->name ?? ($rental->project_name ?? 'Proyek') }}
                </span>
                <span class="inline-flex items-center gap-1 text-xs font-bold bg-amber-50 text-amber-800 dark:bg-amber-950/40 dark:text-amber-300 px-2.5 py-1 rounded-lg border border-amber-200/70 dark:border-amber-900">
                    <x-lucide-wrench class="w-3.5 h-3.5 text-amber-600" /> Sewa Alat Kerja
                </span>
            </div>

            <div class="flex items-center gap-2 flex-wrap">
                <button type="button" @click="showEditRentalModal = true"
                    class="btn btn-sm btn-light border inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1.5 rounded-xl text-slate-700 hover:bg-slate-100 transition shadow-2xs">
                    <x-lucide-pencil class="w-3.5 h-3.5 text-slate-500" /> Edit Info Alat
                </button>
                <button type="button" @click="window.print()"
                    class="btn btn-sm bg-slate-900 hover:bg-slate-800 text-white inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1.5 rounded-xl transition shadow-2xs">
                    <x-lucide-printer class="w-3.5 h-3.5 text-amber-400" /> Cetak Timesheet
                </button>
                <button type="button" @click="showAddLogModal = true"
                    class="btn btn-sm bg-indigo-600 hover:bg-indigo-700 text-white inline-flex items-center gap-1.5 text-xs font-bold px-3.5 py-1.5 rounded-xl shadow-xs transition">
                    <x-lucide-plus class="w-4 h-4" /> + Catat Log Pemakaian
                </button>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success p-3.5 rounded-xl bg-emerald-50 border border-emerald-300 text-emerald-800 text-xs font-semibold flex items-center justify-between">
                <span class="flex items-center gap-2"><x-lucide-circle-check class="w-4 h-4 text-emerald-600" /> {{ session('success') }}</span>
            </div>
        @endif

        {{-- 2. Header Ringkasan & 4 Metric Cards --}}
        <div class="bg-white dark:bg-slate-900 p-5 rounded-3xl border border-slate-200/90 dark:border-slate-800 shadow-xs space-y-4">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 dark:border-slate-800 pb-4">
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="px-2.5 py-1 rounded-md text-[10px] font-extrabold bg-indigo-600 text-white uppercase tracking-wider">
                            TIMESHEET SEWA ALAT
                        </span>
                        <span class="text-xs text-slate-400 font-mono">#ALAT-{{ $rental->id }}</span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white mt-1.5 tracking-tight">
                        {{ $rental->tool_name }}
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 flex items-center gap-1.5 flex-wrap">
                        <span>Vendor: <strong class="text-slate-800 dark:text-slate-200">{{ $rental->vendor_name }}</strong></span>
                        @if($rental->docket_number)
                            <span class="text-slate-300 dark:text-slate-700">&bull;</span>
                            <span>No. Docket/SJ: <strong class="font-mono text-slate-800 dark:text-slate-200">{{ $rental->docket_number }}</strong></span>
                        @endif
                        <span class="text-slate-300 dark:text-slate-700">&bull;</span>
                        <span>Periode: <strong>{{ $rental->start_date ? $rental->start_date->translatedFormat('d M Y') : '-' }} @if($rental->end_date && $rental->end_date != $rental->start_date) s/d {{ $rental->end_date->translatedFormat('d M Y') }} @endif</strong></span>
                    </p>
                </div>
                <div class="flex items-center gap-2 self-start md:self-center">
                    <span class="px-3.5 py-1.5 rounded-xl text-xs font-extrabold border {{ $rental->status === 'active' ? 'bg-amber-50 text-amber-800 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300' : 'bg-emerald-50 text-emerald-800 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300' }}">
                        STATUS: {{ strtoupper($rental->status === 'active' ? 'AKTIF DI PROYEK' : 'SELESAI / RETURNED') }}
                    </span>
                </div>
            </div>

            {{-- 4 Metric Cards --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                <div class="p-4 rounded-2xl bg-indigo-50/70 dark:bg-indigo-950/30 border border-indigo-200/70 dark:border-indigo-900">
                    <span class="text-[11px] text-indigo-700 dark:text-indigo-300 block font-bold uppercase tracking-wider">Total Durasi Pemakaian</span>
                    <span class="text-xl font-black text-indigo-900 dark:text-indigo-200 mt-1 block tracking-tight">
                        {{ number_format($rental->rental_duration, 1, ',', '.') }} <span class="text-xs font-semibold">{{ $rental->duration_unit ?: 'Jam' }}</span>
                    </span>
                </div>
                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700">
                    <span class="text-[11px] text-slate-500 dark:text-slate-400 block font-semibold uppercase tracking-wider">Tarif per {{ $rental->duration_unit ?: 'Jam' }}</span>
                    <span class="text-lg font-black text-slate-900 dark:text-white mt-1 block tracking-tight">
                        @if((float)$rental->rate_per_unit > 0)
                            Rp {{ number_format($rental->rate_per_unit, 0, ',', '.') }}
                        @else
                            <span class="text-slate-400 font-normal italic text-xs">(Belum Ditentukan)</span>
                        @endif
                    </span>
                </div>
                <div class="p-4 rounded-2xl bg-emerald-50/70 dark:bg-emerald-950/30 border border-emerald-200/70 dark:border-emerald-900">
                    <span class="text-[11px] text-emerald-700 dark:text-emerald-300 block font-bold uppercase tracking-wider">Total Biaya Sewa</span>
                    <span class="text-lg font-black text-emerald-900 dark:text-emerald-200 mt-1 block tracking-tight">
                        @if((float)$rental->total_rental_cost > 0)
                            Rp {{ number_format($rental->total_rental_cost, 0, ',', '.') }}
                        @else
                            <span class="text-slate-400 font-normal italic text-xs">-</span>
                        @endif
                    </span>
                </div>
                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700">
                    <span class="text-[11px] text-slate-500 dark:text-slate-400 block font-semibold uppercase tracking-wider">Jumlah Catatan Harian</span>
                    <span class="text-xl font-black text-slate-900 dark:text-white mt-1 block tracking-tight">
                        {{ $rental->logs->count() }} Hari Kerja
                    </span>
                </div>
            </div>
        </div>

        {{-- 3. TABEL TIMESHEET HARIAN PEMAKAIAN ALAT (PERSIS REKAP TIBO URIP.xlsx) --}}
        <section class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/90 dark:border-slate-800 shadow-xs overflow-hidden">
            <div class="bg-slate-100/90 dark:bg-slate-800/90 px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-1.5 h-6 bg-indigo-600 rounded-full"></div>
                    <div>
                        <h3 class="text-sm sm:text-base font-extrabold text-slate-900 dark:text-white tracking-tight uppercase">
                            Logbook Harian Pemakaian Alat Kerja
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            {{ $rental->tool_name }} &bull; Vendor: <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $rental->vendor_name }}</span>
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
                            <th class="py-3 px-3 border-r border-slate-300/80 text-center" style="width: 110px;">Durasi / Qty</th>
                            <th class="py-3 px-3 border-r border-slate-300/80 text-center" style="width: 80px;">Satuan</th>
                            <th class="py-3 px-4 border-r border-slate-300/80">Jenis Pekerjaan (Aktivitas)</th>
                            <th class="py-3 px-4 border-r border-slate-300/80">Spesifikasi Armada / Keterangan</th>
                            <th class="py-3 px-3 text-center" style="width: 90px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800 font-medium">
                        @forelse($rental->logs as $idx => $log)
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition">
                                <td class="py-2.5 px-3 border-r border-slate-200 text-center font-bold text-slate-400">
                                    {{ $idx + 1 }}
                                </td>
                                <td class="py-2.5 px-3 border-r border-slate-200 text-center font-bold text-slate-800 dark:text-slate-100 whitespace-nowrap">
                                    {{ $log->usage_date ? $log->usage_date->translatedFormat('d M Y') : '-' }}
                                </td>
                                <td class="py-2.5 px-3 border-r border-slate-200 text-center font-black text-indigo-700 dark:text-indigo-400 text-sm">
                                    {{ (float)$log->usage_duration }}
                                </td>
                                <td class="py-2.5 px-3 border-r border-slate-200 text-center text-slate-600 dark:text-slate-300 font-semibold">
                                    {{ $log->duration_unit }}
                                </td>
                                <td class="py-2.5 px-4 border-r border-slate-200 font-semibold text-slate-900 dark:text-white">
                                    {{ $log->activity_type }}
                                </td>
                                <td class="py-2.5 px-4 border-r border-slate-200 text-slate-600 dark:text-slate-400">
                                    {{ $log->equipment_spec ?: ($log->notes ?: '-') }}
                                </td>
                                <td class="py-2.5 px-3 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button type="button" @click="openEditLog({{ json_encode($log) }})"
                                            class="inline-flex items-center justify-center w-7 h-7 rounded-lg text-slate-600 dark:text-slate-300 hover:text-indigo-600 hover:bg-slate-100 dark:hover:bg-slate-800 transition shadow-2xs"
                                            title="Edit Log">
                                            <x-lucide-pencil class="w-3.5 h-3.5" />
                                        </button>
                                        <form method="POST" action="{{ route('project-notes.rental-logs.destroy', $log) }}" onsubmit="return confirm('Hapus baris log tanggal {{ $log->usage_date?->format('d/m/Y') }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="inline-flex items-center justify-center w-7 h-7 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-slate-800 transition shadow-2xs"
                                                title="Hapus Log">
                                                <x-lucide-trash-2 class="w-3.5 h-3.5" />
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-10 text-center text-slate-400">
                                    <x-lucide-calendar-days class="w-8 h-8 mx-auto mb-2 text-slate-300 dark:text-slate-600" />
                                    <div class="font-bold text-slate-700 dark:text-slate-300 text-sm">Belum ada catatan logbook harian alat ini.</div>
                                    <div class="text-xs text-slate-400 mt-0.5">Gunakan tombol <strong>+ Catat Log Pemakaian</strong> di atas untuk menambahkan aktivitas.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($rental->logs->isNotEmpty())
                        <tfoot>
                            <tr class="bg-indigo-100/80 dark:bg-indigo-950/50 text-indigo-950 dark:text-indigo-100 font-black border-t-2 border-indigo-300 dark:border-indigo-800 text-xs">
                                <td colspan="2" class="py-3 px-3 text-right uppercase tracking-wider">
                                    TOTAL PEMAKAIAN:
                                </td>
                                <td class="py-3 px-3 text-center font-black text-sm text-indigo-900 dark:text-indigo-200">
                                    {{ (float)$rental->rental_duration }}
                                </td>
                                <td class="py-3 px-3 text-center uppercase font-bold">
                                    {{ $rental->duration_unit ?: 'Jam' }}
                                </td>
                                <td colspan="3" class="py-3 px-4 text-slate-700 dark:text-slate-300">
                                    @if((float)$rental->rate_per_unit > 0)
                                        Estimasi Total Biaya: <strong>Rp {{ number_format($rental->total_rental_cost, 0, ',', '.') }}</strong> ({{ (float)$rental->rental_duration }} {{ $rental->duration_unit }} &times; Rp {{ number_format($rental->rate_per_unit, 0, ',', '.') }})
                                    @endif
                                </td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </section>

        {{-- MODAL: CATAT LOG PEMAKAIAN HARIAN --}}
        <div x-show="showAddLogModal" x-cloak class="fixed inset-0 flex items-center justify-center bg-slate-950/65 backdrop-blur-xs p-4 overflow-y-auto" style="z-index: 9999;">
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-2xl w-full p-5 space-y-3.5 my-auto" style="max-width: 500px;" @click.away="showAddLogModal = false">
                <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-3">
                    <h3 class="text-sm font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                        <x-lucide-calendar-plus class="w-4 h-4 text-indigo-600" /> Catat Log Harian Pemakaian Alat
                    </h3>
                    <button type="button" @click="showAddLogModal = false" class="text-slate-400 hover:text-slate-600 font-bold">&times;</button>
                </div>

                <form method="POST" action="{{ route('project-notes.rental-logs.store', $rental) }}" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Tanggal Pemakaian *</label>
                            <input type="date" name="usage_date" value="{{ date('Y-m-d') }}" class="form-control text-xs w-full" required>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Durasi / Qty *</label>
                            <input type="number" step="any" min="0.1" name="usage_duration" value="7" class="form-control text-xs w-full font-black" required>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Satuan *</label>
                            <input type="text" name="duration_unit" value="{{ $rental->duration_unit ?: 'Jam' }}" class="form-control text-xs w-full" required>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Armada / Spesifikasi</label>
                            <input type="text" name="equipment_spec" class="form-control text-xs w-full" placeholder="Contoh: Truck bak tronton">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Jenis Pekerjaan (Aktivitas) *</label>
                        <input type="text" name="activity_type" class="form-control text-xs w-full" placeholder="Contoh: Pengecoran, Layani boredpile, Galian..." required>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Catatan Tambahan</label>
                        <input type="text" name="notes" class="form-control text-xs w-full" placeholder="Kondisi cuaca, kendala, atau operator...">
                    </div>

                    <div class="flex justify-end gap-2 pt-3 border-t border-slate-200 dark:border-slate-800">
                        <button type="button" @click="showAddLogModal = false" class="btn btn-light btn-sm">Batal</button>
                        <button type="submit" class="btn btn-primary btn-sm bg-indigo-600 hover:bg-indigo-700 border-indigo-600 text-white font-bold">
                            Simpan Log Harian
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- MODAL: EDIT LOG PEMAKAIAN HARIAN --}}
        <div x-show="showEditLogModal" x-cloak class="fixed inset-0 flex items-center justify-center bg-slate-950/65 backdrop-blur-xs p-4 overflow-y-auto" style="z-index: 9999;">
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-2xl w-full p-5 space-y-3.5 my-auto" style="max-width: 500px;" @click.away="showEditLogModal = false">
                <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-3">
                    <h3 class="text-sm font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                        <x-lucide-pencil class="w-4 h-4 text-indigo-600" /> Edit Log Pemakaian Alat
                    </h3>
                    <button type="button" @click="showEditLogModal = false" class="text-slate-400 hover:text-slate-600 font-bold">&times;</button>
                </div>

                <form method="POST" :action="`{{ url('/project-notes/rental-logs') }}/${editLog.id}`" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Tanggal Pemakaian *</label>
                            <input type="date" name="usage_date" x-model="editLog.usage_date" class="form-control text-xs w-full" required>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Durasi / Qty *</label>
                            <input type="number" step="any" min="0.1" name="usage_duration" x-model="editLog.usage_duration" class="form-control text-xs w-full font-black" required>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Satuan *</label>
                            <input type="text" name="duration_unit" x-model="editLog.duration_unit" class="form-control text-xs w-full" required>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Armada / Spesifikasi</label>
                            <input type="text" name="equipment_spec" x-model="editLog.equipment_spec" class="form-control text-xs w-full">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Jenis Pekerjaan (Aktivitas) *</label>
                        <input type="text" name="activity_type" x-model="editLog.activity_type" class="form-control text-xs w-full" required>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Catatan Tambahan</label>
                        <input type="text" name="notes" x-model="editLog.notes" class="form-control text-xs w-full">
                    </div>

                    <div class="flex justify-end gap-2 pt-3 border-t border-slate-200 dark:border-slate-800">
                        <button type="button" @click="showEditLogModal = false" class="btn btn-light btn-sm">Batal</button>
                        <button type="submit" class="btn btn-primary btn-sm bg-indigo-600 hover:bg-indigo-700 border-indigo-600 text-white font-bold">
                            Perbarui Log
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
