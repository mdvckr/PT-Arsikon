# Master Plan: Redesign Inventori Gudang — "High-Craft Enterprise & Bento UI"
**Proyek:** Sistem Manajemen Gudang & Pengadaan — PT Arsikon  
**Halaman Terkait:** Modul Logistik ➔ Inventori Gudang (`resources/views/inventory/index.blade.php`)  
**Target Visual:** Setara produk SaaS Premium (Linear, Apple Business Manager, Stripe Dashboard) — Kaya detail visual, berdimensi, interaktif, responsif, mendukung penuh *Light/Dark Mode*, dan menghilangkan kesan kaku buatan robot/AI.

---

## 1. Evaluasi Desain Lama vs Desain Baru (High-Craft Enterprise)

| Aspek Visual | Desain Lama (Terlihat Kaku / Template AI) | Desain Baru (High-Craft Enterprise & Bento UI) |
| :--- | :--- | :--- |
| **Header Halaman** | Judul teks biasa di atas latar abu-abu polos dengan *badge* standar. | **Executive Header Banner:** Panel gelap bergradasi halus dengan efek *ambient glow* (oranye & biru), indikator *live pulse* (`animate-ping`), dan tombol aksi bergaya *glassmorphism* (`backdrop-blur`). |
| **Kartu Statistik (KPI)** | 4 kotak putih kaku terpisah yang hanya menampilkan teks angka dan *badge* sudut. | **3 Bento Metric Cards Bervisual:** Dilengkapi grafik mikro (*multi-segment composition bar* untuk proporsi Struktur/MEP/Arsitek, *split pill* untuk status alat, dan *health radar* untuk stok kritis). |
| **Navigasi Material vs Alat** | 2 tabel raksasa ditumpuk atas-bawah sehingga halaman menjadi sangat panjang. | **Segmented Command Switcher:** Menggunakan tab interaktif berbasis Alpine.js dengan indikator titik aktif berwarna oranye khas PT Arsikon. |
| **Pencarian & Filter** | Berada di dalam kotak terpisah dengan 4 label *UPPERCASE* kaku yang memakan ruang vertikal. | **Inline Command Bar:** Menyatu langsung di header tabel, dilengkapi indikator *shortcut keyboard* (`/`) dan dropdown minimalis. |
| **Grup Kategori Tabel** | Menggunakan ikon folder kuning standar (`📁`) dan teks berulang *"Klik untuk membuka / menutup"*. | **Modern Accordion Group Header:** Menggunakan tombol *chevron* berbingkai yang berputar halus (`rotate-90`) saat diklik dan *badge* jumlah varian. |
| **Baris Data Material** | Deretan teks biasa tanpa hierarki visual dan angka stok polos. | **Rich Data Row:** Memiliki *Monogram Avatar* bergradasi (`CT`), *pill* kode SKU `monospace`, serta **Visual Stock Capacity Bar** (*progress bar* perbandingan stok aktual vs stok minimum). |

---

## 2. Spesifikasi Komponen Visual Utama

### A. Executive Header Banner
- **Latar Belakang:** Gradasi gelap (`bg-gradient-to-br from-slate-900 via-slate-900 to-slate-800`) yang tetap elegan baik saat *Light Mode* maupun *Dark Mode*.
- **Ambient Lighting:** Dua lingkaran cahaya blur (`bg-orange-500/15` dan `bg-blue-500/10`) di sudut kanan untuk memberikan kedalaman dimensi.
- **Live Indicator:** Titik hijau berdenyut yang menandakan koneksi pemantauan stok *real-time* di Gudang Pusat PT Arsikon.

### B. Bento Metric Cards dengan Micro-Visualizations
1. **Bento 1 (Total Material Konstruksi):** Menampilkan total unit fisik ditambah *multi-progress bar* 3 warna (Biru untuk Struktur, Oranye untuk MEP, Hijau untuk Arsitek).
2. **Bento 2 (Kesiapan Alat Kerja):** Menampilkan unit siap pakai ditambah 2 kotak sub-metrik (*Di Proyek* dan *Perawatan*).
3. **Bento 3 (Indeks Kesehatan Stok):** Menampilkan persentase kesiapan stok dan ringkasan jumlah SKU yang berada di bawah ambang batas minimum.

### C. Rich Data Table & Visual Stock Meter
- **Kolom Identitas Material:** Menampilkan kotak inisial 2 huruf (`CT`, `SM`, `BS`) dengan gradasi warna, judul material yang berubah warna saat di-*hover*, dan lencana SKU bergaya kode (`font-mono`).
- **Kolom Kapasitas Stok Fisik:** Dilengkapi bilah kapasitas (*progress bar*) di bawah angka stok:
  - **Hijau (`bg-emerald-500`):** Stok aman di atas batas minimum.
  - **Kuning/Oranye (`bg-amber-500`):** Stok mendekati batas minimum.
  - **Merah (`bg-rose-500`):** Stok kritis / di bawah batas minimum.

---

## 3. Kode Implementasi Lengkap (Blade + Tailwind CSS + Alpine.js)

Salin struktur kode berikut ke dalam file `resources/views/inventory/index.blade.php` (dan sesuaikan variabel *looping* dengan data dari `InventoryController`):

```html
<div x-data="{ 
        activeTab: 'material', 
        searchQuery: '',
        openGroups: ['ARSITEK', 'MEP', 'STRUKTUR'],
        toggleGroup(group) {
            this.openGroups.includes(group) 
                ? this.openGroups = this.openGroups.filter(g => g !== group) 
                : this.openGroups.push(group);
        },
        expandAll() { this.openGroups = ['ARSITEK', 'MEP', 'STRUKTUR']; },
        collapseAll() { this.openGroups = []; }
    }" 
    class="w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 py-7 space-y-7">

    <!-- ================= 1. EXECUTIVE HEADER BANNER ================= -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-900 via-slate-900 to-slate-800 dark:from-slate-900 dark:via-slate-950 dark:to-slate-900 p-6 sm:p-7 text-white shadow-lg border border-slate-800/80">
        <!-- Dekorasi Cahaya Halus (Ambient Glow) -->
        <div class="pointer-events-none absolute -right-20 -top-20 h-64 w-64 rounded-full bg-orange-500/15 blur-3xl"></div>
        <div class="pointer-events-none absolute right-40 -bottom-24 h-56 w-56 rounded-full bg-blue-500/10 blur-3xl"></div>

        <div class="relative flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div class="space-y-2">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur-md border border-white/10 text-xs font-medium text-orange-300">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    <span>Gudang Pusat PT Arsikon (Sentral Distribusi)</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-white">
                    Pusat Kendali Inventori & Aset
                </h1>
                <p class="text-xs sm:text-sm text-slate-300/80 max-w-2xl">
                    Pantau pergerakan stok material konstruksi, ambang batas kritis, dan distribusi alat kerja proyek secara presisi.
                </p>
            </div>

            <!-- Tombol Aksi Cepat Bergaya Glassmorphism -->
            <div class="flex flex-wrap items-center gap-3 self-start lg:self-center">
                <a href="#" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold bg-white/10 hover:bg-white/15 text-white border border-white/15 backdrop-blur-md transition">
                    <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Unduh Laporan Stok
                </a>
                <a href="#" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white shadow-md shadow-orange-500/20 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Stock Opname Baru
                </a>
            </div>
        </div>
    </div>

    <!-- ================= 2. BENTO METRIC CARDS (DENGAN VISUALISASI MIKRO) ================= -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">

        <!-- BENTO 1: Material + Visual Komposisi Bar -->
        <div class="group relative bg-white dark:bg-slate-800/90 rounded-2xl p-6 border border-slate-200/80 dark:border-slate-700/80 shadow-xs hover:shadow-md transition-all duration-200">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Material Konstruksi</span>
                    <div class="mt-2 flex items-baseline gap-2.5">
                        <span class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white tabular-nums">1.250</span>
                        <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Unit Fisik (7 Varian)</span>
                    </div>
                </div>
                <div class="w-11 h-11 rounded-xl bg-blue-50 dark:bg-blue-500/10 border border-blue-100 dark:border-blue-500/20 flex items-center justify-center text-blue-600 dark:text-blue-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
            </div>

            <!-- Micro-Chart: Komposisi Kategori Material -->
            <div class="mt-5 space-y-2">
                <div class="flex h-2 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-700 p-0.5 gap-0.5">
                    <div class="bg-blue-600 rounded-l-full" style="width: 72%" title="Struktur (72%)"></div>
                    <div class="bg-orange-500" style="width: 18%" title="MEP (18%)"></div>
                    <div class="bg-emerald-500 rounded-r-full" style="width: 10%" title="Arsitek (10%)"></div>
                </div>
                <div class="flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400">
                    <span class="inline-flex items-center gap-1"><i class="w-2 h-2 rounded-full bg-blue-600"></i> Struktur (5)</span>
                    <span class="inline-flex items-center gap-1"><i class="w-2 h-2 rounded-full bg-orange-500"></i> MEP (1)</span>
                    <span class="inline-flex items-center gap-1"><i class="w-2 h-2 rounded-full bg-emerald-500"></i> Arsitek (1)</span>
                </div>
            </div>
        </div>

        <!-- BENTO 2: Kesiapan Armada & Alat Kerja -->
        <div class="group relative bg-white dark:bg-slate-800/90 rounded-2xl p-6 border border-slate-200/80 dark:border-slate-700/80 shadow-xs hover:shadow-md transition-all duration-200">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Kesiapan Alat Kerja</span>
                    <div class="mt-2 flex items-baseline gap-2.5">
                        <span class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white tabular-nums">0</span>
                        <span class="text-xs font-medium text-emerald-600 dark:text-emerald-400">Unit Siap Pakai</span>
                    </div>
                </div>
                <div class="w-11 h-11 rounded-xl bg-amber-50 dark:bg-amber-500/10 border border-amber-100 dark:border-amber-500/20 flex items-center justify-center text-amber-600 dark:text-amber-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
            </div>

            <!-- Split Pill Info -->
            <div class="mt-5 grid grid-cols-2 gap-2.5 pt-1">
                <div class="px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-900/70 border border-slate-100 dark:border-slate-700/60 flex items-center justify-between">
                    <span class="text-[11px] text-slate-500 dark:text-slate-400">Di Proyek</span>
                    <span class="text-xs font-bold text-slate-800 dark:text-slate-200 tabular-nums">0 Unit</span>
                </div>
                <div class="px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-900/70 border border-slate-100 dark:border-slate-700/60 flex items-center justify-between">
                    <span class="text-[11px] text-slate-500 dark:text-slate-400">Perawatan</span>
                    <span class="text-xs font-bold text-slate-800 dark:text-slate-200 tabular-nums">0 Unit</span>
                </div>
            </div>
        </div>

        <!-- BENTO 3: Indikator Kesehatan Stok (Stock Health Radar) -->
        <div class="group relative bg-white dark:bg-slate-800/90 rounded-2xl p-6 border border-slate-200/80 dark:border-slate-700/80 shadow-xs hover:shadow-md transition-all duration-200">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Indeks Kesehatan Stok</span>
                    <div class="mt-2 flex items-center gap-2.5">
                        <span class="text-3xl font-extrabold tracking-tight text-emerald-600 dark:text-emerald-400">100%</span>
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 border border-emerald-200/60 dark:border-emerald-500/30">
                            Terkendali
                        </span>
                    </div>
                </div>
                <div class="w-11 h-11 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-100 dark:border-emerald-500/20 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
            </div>

            <div class="mt-5 flex items-center justify-between px-3.5 py-2.5 rounded-xl bg-emerald-50/60 dark:bg-emerald-950/30 border border-emerald-100 dark:border-emerald-900/50">
                <span class="text-xs text-emerald-800 dark:text-emerald-300 font-medium">Stok di bawah batas minimum</span>
                <span class="text-xs font-extrabold text-emerald-700 dark:text-emerald-400 tabular-nums">0 SKU Kritis</span>
            </div>
        </div>

    </div>

    <!-- ================= 3. MAIN DATA WORKSPACE (DENGAN VISUAL STOCK METER) ================= -->
    <div class="bg-white dark:bg-slate-800/95 rounded-2xl border border-slate-200/90 dark:border-slate-700/80 shadow-xs overflow-hidden">

        <!-- COMMAND BAR: Segmented Switcher + Search & Filter -->
        <div class="p-4 sm:px-6 border-b border-slate-200/80 dark:border-slate-700/80 flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4">
            
            <!-- Segmented Switcher -->
            <div class="inline-flex p-1 rounded-xl bg-slate-100 dark:bg-slate-900 border border-slate-200/60 dark:border-slate-700/60 self-start">
                <button type="button" @click="activeTab = 'material'"
                    :class="activeTab === 'material' ? 'bg-white dark:bg-slate-800 text-slate-900 dark:text-white shadow-xs ring-1 ring-slate-900/5' : 'text-slate-500 hover:text-slate-800 dark:text-slate-400'"
                    class="flex items-center gap-2.5 px-4 py-2 rounded-lg text-xs font-bold transition-all">
                    <span class="w-2 h-2 rounded-full" :class="activeTab === 'material' ? 'bg-orange-500' : 'bg-slate-300 dark:bg-slate-600'"></span>
                    <span>Material Konstruksi</span>
                    <span class="px-2 py-0.5 rounded-md text-[11px] bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 tabular-nums">7</span>
                </button>

                <button type="button" @click="activeTab = 'alat'"
                    :class="activeTab === 'alat' ? 'bg-white dark:bg-slate-800 text-slate-900 dark:text-white shadow-xs ring-1 ring-slate-900/5' : 'text-slate-500 hover:text-slate-800 dark:text-slate-400'"
                    class="flex items-center gap-2.5 px-4 py-2 rounded-lg text-xs font-bold transition-all">
                    <span class="w-2 h-2 rounded-full" :class="activeTab === 'alat' ? 'bg-orange-500' : 'bg-slate-300 dark:bg-slate-600'"></span>
                    <span>Alat Kerja & Armada</span>
                    <span class="px-2 py-0.5 rounded-md text-[11px] bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400 tabular-nums">0</span>
                </button>
            </div>

            <!-- Filter & Search -->
            <div class="flex flex-wrap sm:flex-nowrap items-center gap-2.5">
                <div class="relative w-full sm:w-72">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" x-model="searchQuery" placeholder="Filter cepat nama material, SKU..." 
                        class="w-full pl-10 pr-10 py-2 text-xs bg-slate-50 dark:bg-slate-900/90 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-800 dark:text-slate-100 placeholder:text-slate-400 focus:bg-white focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition">
                    <kbd class="hidden sm:inline-block absolute right-2.5 top-1/2 -translate-y-1/2 px-1.5 py-0.5 text-[10px] font-mono text-slate-400 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded">/</kbd>
                </div>

                <select class="text-xs bg-slate-50 dark:bg-slate-900/90 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2 text-slate-700 dark:text-slate-200 font-medium">
                    <option>Semua Gudang</option>
                    <option selected>Gudang Pusat</option>
                </select>

                <select class="text-xs bg-slate-50 dark:bg-slate-900/90 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2 text-slate-700 dark:text-slate-200 font-medium">
                    <option>Semua Kondisi Stok</option>
                    <option>Stok Aman</option>
                    <option>Stok Menipis / Kritis</option>
                </select>
            </div>
        </div>

        <!-- TABEL BERDIMENSI (DENGAN MONOGRAM AVATAR & STOCK CAPACITY BAR) -->
        <div x-show="activeTab === 'material'" class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[960px]">
                <thead>
                    <tr class="bg-slate-50/80 dark:bg-slate-900/60 border-b border-slate-200/80 dark:border-slate-700/80 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                        <th class="py-3.5 px-6">Identitas Material & SKU</th>
                        <th class="py-3.5 px-4">Spesifikasi & Kelompok</th>
                        <th class="py-3.5 px-4">Titik Gudang</th>
                        <th class="py-3.5 px-4 w-52">Kapasitas Stok Fisik</th>
                        <th class="py-3.5 px-4 text-center">Status Level</th>
                        <th class="py-3.5 px-4">Pembaruan Terakhir</th>
                        <th class="py-3.5 px-6 text-right">Tindakan</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60 text-xs">
                    
                    <!-- GRUP KATEGORI PREMIUM -->
                    <tr @click="toggleGroup('ARSITEK')" class="bg-slate-50/50 dark:bg-slate-900/40 hover:bg-slate-100/70 dark:hover:bg-slate-800/80 cursor-pointer transition select-none">
                        <td colspan="7" class="py-3 px-6">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <span class="w-6 h-6 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center shadow-2xs">
                                        <svg :class="openGroups.includes('ARSITEK') ? 'rotate-90 text-orange-600' : 'text-slate-400'" class="w-3.5 h-3.5 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                    </span>
                                    <span class="font-bold tracking-wide text-slate-800 dark:text-slate-100">DIVISI ARSITEK</span>
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-200/50 dark:border-blue-500/20">
                                        1 Varian Material
                                    </span>
                                </div>
                                <span class="text-[11px] font-medium text-slate-400 hover:text-slate-600" x-text="openGroups.includes('ARSITEK') ? 'Lipat Grup' : 'Buka Grup'"></span>
                            </div>
                        </td>
                    </tr>

                    <!-- BARIS ITEM MATERIAL (KAYA VISUAL) -->
                    <tr x-show="openGroups.includes('ARSITEK')" class="group hover:bg-orange-50/30 dark:hover:bg-slate-700/25 transition">
                        <!-- Kolom 1: Avatar Monogram + Nama + Pill SKU -->
                        <td class="py-4 px-6">
                            <div class="flex items-center gap-3.5">
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white font-bold text-xs flex items-center justify-center shadow-xs shrink-0">
                                    CT
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900 dark:text-white text-[13px] group-hover:text-orange-600 dark:group-hover:text-orange-400 transition">
                                        Cat Dinding Eksterior White 25kg
                                    </div>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-900 font-mono text-[10px] font-semibold text-slate-600 dark:text-slate-300 border border-slate-200/70 dark:border-slate-700">
                                            MAT-CAT-001
                                        </span>
                                        <span class="text-[11px] text-slate-400">Dulux Weathershield</span>
                                    </div>
                                </div>
                            </div>
                        </td>

                        <!-- Kolom 2: Spesifikasi -->
                        <td class="py-4 px-4">
                            <div class="font-semibold text-slate-700 dark:text-slate-200">Eksterior</div>
                            <div class="text-[11px] text-slate-400 mt-0.5">Kemasan: 25 kg (Pail)</div>
                        </td>

                        <!-- Kolom 3: Lokasi Gudang -->
                        <td class="py-4 px-4">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100/80 dark:bg-slate-900/70 text-slate-700 dark:text-slate-300 font-medium text-[11px]">
                                <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                Gudang Pusat
                            </span>
                        </td>

                        <!-- Kolom 4: Visual Stock Meter -->
                        <td class="py-4 px-4">
                            <div class="flex items-baseline justify-between mb-1.5">
                                <span class="text-sm font-extrabold text-slate-900 dark:text-white tabular-nums">45 <span class="text-[11px] font-normal text-slate-400">Pail</span></span>
                                <span class="text-[10px] font-medium text-slate-400">Min: 10</span>
                            </div>
                            <!-- Progress Bar Stok -->
                            <div class="w-full h-1.5 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden">
                                <div class="bg-emerald-500 h-full rounded-full" style="width: 82%"></div>
                            </div>
                        </td>

                        <!-- Kolom 5: Badge Status -->
                        <td class="py-4 px-4 text-center">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-200/70 dark:border-emerald-500/20">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Stok Aman
                            </span>
                        </td>

                        <!-- Kolom 6: Waktu Update -->
                        <td class="py-4 px-4">
                            <div class="text-slate-700 dark:text-slate-300 font-medium">24 Okt 2026</div>
                            <div class="text-[11px] text-slate-400">Pukul 23:20 WIB</div>
                        </td>

                        <!-- Kolom 7: Tombol Aksi Interaktif -->
                        <td class="py-4 px-6 text-right">
                            <div class="inline-flex items-center gap-1.5">
                                <a href="#" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:border-orange-500 hover:text-orange-600 dark:hover:text-orange-400 shadow-2xs transition">
                                    Kartu Stok
                                </a>
                            </div>
                        </td>
                    </tr>

                </tbody>
            </table>
        </div>

        <!-- TAB 2: TABEL ALAT KERJA & ARMADA -->
        <div x-show="activeTab === 'alat'" style="display: none;" class="p-14 text-center">
            <div class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-slate-700/60 text-slate-400 mx-auto flex items-center justify-center mb-3.5 border border-slate-200/60 dark:border-slate-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            </div>
            <h3 class="text-sm font-bold text-slate-800 dark:text-slate-200">Belum Ada Unit Alat Kerja Terdaftar</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 max-w-sm mx-auto mt-1">
                Data inventaris alat kerja dan posisi peminjaman proyek akan tampil di sini setelah ditambahkan melalui modul Alat.
            </p>
        </div>

    </div>
</div>
```

---

## 4. Checklist Implementasi (Backlog)
- [x] Mengganti header halaman `inventory/index.blade.php` dengan **Executive Header Banner** beraksen *ambient glow*.
- [x] Mengganti 4 kartu KPI lama dengan **3 Bento Metric Cards** yang memiliki *multi-segment bar* komposisi material.
- [x] Menyatukan tabel Material dan tabel Alat ke dalam satu kontainer menggunakan **Alpine.js Segmented Switcher** (`activeTab`).
- [x] Mengganti ikon folder kuning (`📁`) pada grup kategori dengan tombol *chevron* modern.
- [x] Menambahkan **Monogram Avatar** (`substr($material->name, 0, 2)`) dan **Visual Stock Capacity Bar** pada setiap baris material.