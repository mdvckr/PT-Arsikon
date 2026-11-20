<x-app-layout>
    <x-slot name="title">Formulir Pengajuan Kas Lapangan - Arsikon</x-slot>

    <div x-data="{
        items: [
            { item_name: '', quantity: 1, unit: 'Unit', price: 0, notes: '' }
        ],
        currentBalance: {{ (float) ($project->operational_balance ?? 0) }},
        addItem() {
            this.items.push({ item_name: '', quantity: 1, unit: 'Unit', price: 0, notes: '' });
        },
        removeItem(index) {
            if (this.items.length > 1) {
                this.items.splice(index, 1);
            }
        },
        subtotal(item) {
            return (parseFloat(item.quantity) || 0) * (parseFloat(item.price) || 0);
        },
        get grandTotal() {
            return this.items.reduce((sum, it) => sum + this.subtotal(it), 0);
        },
        formatRupiah(num) {
            return 'Rp ' + (new Intl.NumberFormat('id-ID')).format(Math.round(num || 0));
        }
    }" class="space-y-5 max-w-4xl mx-auto" data-animate="panel">

        {{-- Top Nav --}}
        <div class="flex items-center justify-between gap-3 text-xs">
            <a href="{{ route('project-balance-requests.index', ['project_id' => $project->id]) }}"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 hover:bg-slate-50 font-semibold transition">
                <x-lucide-arrow-left class="w-3.5 h-3.5 text-slate-400" />
                <span>Kembali ke Riwayat Pengajuan</span>
            </a>
            <span class="text-[11px] text-slate-400">
                Formulir Resmi PKL (Petty Cash Proyek)
            </span>
        </div>

        {{-- Main Form Card --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs overflow-hidden">
            
            {{-- Header --}}
            <div class="p-5 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                            Formulir Pengajuan Dana Lapangan
                        </div>
                        <h1 class="text-lg font-black text-slate-900 dark:text-white tracking-tight mt-0.5" data-animate="header">
                            Permohonan Kas Saldo Proyek
                        </h1>
                        <p class="text-xs text-slate-500 mt-1">
                            Diajukan oleh <strong>{{ auth()->user()->name }}</strong> kepada <strong>Admin Pengadaan Pusat (Manager / Kasir Pusat)</strong>.
                        </p>
                    </div>

                    <div class="bg-white dark:bg-slate-900 px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-end shrink-0">
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Sisa Saldo Kas Saat Ini</div>
                        <div class="text-base font-black text-emerald-700 dark:text-emerald-400 tabular-nums">
                            {{ 'Rp ' . number_format($project->operational_balance ?? 0, 0, ',', '.') }}
                        </div>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('project-balance-requests.store') }}" class="p-5 space-y-5 m-0">
                @csrf
                <input type="hidden" name="project_id" value="{{ $project->id }}">

                {{-- Row 1: Informasi Proyek & Tujuan Belanja --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                            Proyek Pemohon <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" value="{{ $project->name }}" readonly
                               class="w-full text-xs h-9 px-3 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 font-bold cursor-not-allowed">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                            Tingkat Urgensi <span class="text-rose-500">*</span>
                        </label>
                        <select name="urgency" class="w-full text-xs h-9 px-3 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-semibold focus:ring-2 focus:ring-slate-900 focus:border-slate-900" required>
                            <option value="normal" selected>Normal (Kebutuhan Rutin Mingguan)</option>
                            <option value="urgent">Mendesak (Segera / Kas Kosong)</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                        Uraian / Keperluan Belanja Lapangan <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="purpose" placeholder="Contoh: Belanja semen tambahan, paku, solar genset, dan kas darurat pekerjaan struktur"
                           class="w-full text-xs h-9 px-3 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-medium placeholder-slate-400 focus:ring-2 focus:ring-slate-900 focus:border-slate-900" required>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                        Nama Pemeriksa Lapangan (Site Engineer / Site Manager) <span class="text-[10px] text-slate-400 font-normal">(Untuk Kolom Mengetahui)</span>
                    </label>
                    <input type="text" name="signer_site_manager" placeholder="Contoh: Site Engineer FK Teknik / Pak Joko (Site Manager)"
                           class="w-full text-xs h-9 px-3 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-medium placeholder-slate-400">
                </div>

                {{-- Interactive Item Builder --}}
                <div class="pt-2">
                    <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-800">
                        <div>
                            <h3 class="text-xs font-extrabold text-slate-900 dark:text-white uppercase tracking-wider">
                                Rincian Kebutuhan Item Belanja
                            </h3>
                            <p class="text-[11px] text-slate-400">
                                Masukkan rincian barang, kuantitas, dan estimasi harga per item.
                            </p>
                        </div>
                        <button type="button" @click="addItem()"
                                class="px-2.5 py-1 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 text-slate-700 dark:text-slate-200 text-xs font-semibold inline-flex items-center gap-1 transition">
                            <x-lucide-plus class="w-3.5 h-3.5 text-slate-500" />
                            <span>+ Tambah Baris</span>
                        </button>
                    </div>

                    <div class="overflow-x-auto mt-2.5">
                        <table class="w-full text-xs border-collapse">
                            <thead>
                                <tr class="bg-slate-50/80 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-700 text-[11px] font-bold text-slate-600 dark:text-slate-400">
                                    <th class="py-2 px-2 text-center" style="width: 35px;">No</th>
                                    <th class="py-2 px-3 text-start">Nama Barang / Keperluan</th>
                                    <th class="py-2 px-2 text-center" style="width: 80px;">Qty</th>
                                    <th class="py-2 px-2 text-center" style="width: 90px;">Satuan</th>
                                    <th class="py-2 px-3 text-end" style="width: 130px;">Estimasi Harga</th>
                                    <th class="py-2 px-3 text-end" style="width: 140px;">Subtotal</th>
                                    <th class="py-2 px-2 text-center" style="width: 45px;"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                <template x-for="(item, index) in items" :key="index">
                                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40">
                                        <td class="text-center font-mono text-[11px] text-slate-400 py-2 px-2" x-text="index + 1"></td>
                                        <td class="py-2 px-2">
                                            <input type="text" :name="`items[${index}][item_name]`" x-model="item.item_name"
                                                   placeholder="Contoh: Semen Gresik 40kg"
                                                   class="w-full text-xs h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-medium" required>
                                        </td>
                                        <td class="py-2 px-2 text-center">
                                            <input type="number" step="0.01" min="0.01" :name="`items[${index}][quantity]`" x-model="item.quantity"
                                                   class="w-full text-xs h-8 px-2 text-center rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 font-bold tabular-nums" required>
                                        </td>
                                        <td class="py-2 px-2 text-center">
                                            <input type="text" :name="`items[${index}][unit]`" x-model="item.unit"
                                                   placeholder="Sak / Kg"
                                                   class="w-full text-xs h-8 px-2 text-center rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300" required>
                                        </td>
                                        <td class="py-2 px-2 text-end">
                                            <input type="number" step="0.01" min="0" :name="`items[${index}][price]`" x-model="item.price"
                                                   placeholder="0"
                                                   class="w-full text-xs h-8 px-2 text-end rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 font-medium tabular-nums" required>
                                        </td>
                                        <td class="py-2 px-3 text-end font-extrabold text-slate-900 dark:text-white tabular-nums" x-text="formatRupiah(subtotal(item))">
                                        </td>
                                        <td class="py-2 px-2 text-center">
                                            <button type="button" @click="removeItem(index)" :disabled="items.length <= 1"
                                                    class="p-1 text-slate-400 hover:text-rose-600 disabled:opacity-30 disabled:cursor-not-allowed transition">
                                                <x-lucide-trash-2 class="w-3.5 h-3.5" />
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                            <tfoot>
                                <tr class="bg-slate-50 dark:bg-slate-800/80 font-bold border-t-2 border-slate-200 dark:border-slate-700 text-xs">
                                    <td colspan="5" class="py-2.5 px-3 text-end uppercase text-[11px] text-slate-500 tracking-wider">
                                        TOTAL PENGAJUAN DANA KAS:
                                    </td>
                                    <td class="py-2.5 px-3 text-end font-black text-slate-900 dark:text-white text-sm tabular-nums" x-text="formatRupiah(grandTotal)">
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                {{-- Summary Estimation Box --}}
                <div class="bg-slate-50 dark:bg-slate-800/60 p-4 rounded-xl border border-slate-200/80 dark:border-slate-700 flex items-center justify-between flex-wrap gap-3 text-xs">
                    <div class="flex items-center gap-4 flex-wrap">
                        <div>
                            <span class="text-slate-400">Saldo Kas Saat Ini:</span>
                            <strong class="text-slate-700 dark:text-slate-200 ms-1" x-text="formatRupiah(currentBalance)"></strong>
                        </div>
                        <span class="text-slate-300 dark:text-slate-700">&rarr;</span>
                        <div>
                            <span class="text-slate-400">Estimasi Saldo Jika Disetujui PO Pusat:</span>
                            <strong class="text-emerald-700 dark:text-emerald-400 ms-1" x-text="formatRupiah(currentBalance + grandTotal)"></strong>
                        </div>
                    </div>

                    <div class="text-[11px] text-slate-500 italic">
                        * Langsung dicairkan oleh Admin Pengadaan Pusat tanpa butuh Owner.
                    </div>
                </div>

                {{-- Submit Controls --}}
                <div class="pt-2 flex items-center justify-end gap-2.5">
                    <a href="{{ route('project-balance-requests.index') }}"
                       class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 text-slate-700 dark:text-slate-300 text-xs font-semibold transition">
                        Batal
                    </a>
                    <button type="submit"
                            class="px-5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 dark:bg-white dark:hover:bg-slate-100 text-white dark:text-slate-900 text-xs font-extrabold inline-flex items-center gap-1.5 transition shadow-sm">
                        <x-lucide-send class="w-3.5 h-3.5" />
                        <span>Kirim Pengajuan ke PO Pusat</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
