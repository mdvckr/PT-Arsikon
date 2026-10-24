# PT Arsikon Cipta Karya — Enterprise Warehouse & Construction Logistics Management System

<p align="center">
  <img src="public/assets/Logo-Dashboard.png" alt="PT Arsikon Cipta Karya Logo" width="120" style="border-radius: 12px; margin-bottom: 12px;">
</p>

<p align="center">
  <strong>Sistem Manajemen Gudang, Pengadaan, Distribusi Logistik, dan Inventaris Proyek Konstruksi Terintegrasi</strong>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 12">
  <img src="https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.2+">
  <img src="https://img.shields.io/badge/MySQL-8.0+-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL 8.0+">
  <img src="https://img.shields.io/badge/TailwindCSS-3.x-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white" alt="Tailwind CSS">
  <img src="https://img.shields.io/badge/Alpine.js-3.x-8BC0D0?style=for-the-badge&logo=alpine.js&logoColor=white" alt="Alpine.js">
  <img src="https://img.shields.io/badge/Security-OWASP%20ASVS%20L2-0A5C36?style=for-the-badge&logo=shield&logoColor=white" alt="Security Hardened">
  <img src="https://img.shields.io/badge/Architecture-Multi--Warehouse-EA580C?style=for-the-badge" alt="Multi-Warehouse">
</p>

---

## 📑 Daftar Isi

- [Tentang Sistem](#-tentang-sistem)
- [Arsitektur & Fitur Utama](#-arsitektur--fitur-utama)
- [Keamanan Tingkat Enterprise (OWASP Hardening)](#-keamanan-tingkat-enterprise-owasp-hardening)
- [Desain Antarmuka & User Experience (UX)](#-desain-antarmuka--user-experience-ux)
- [Tech Stack](#-tech-stack)
- [Daftar Akun Bawaan (Role-Based Access Control)](#-daftar-akun-bawaan-role-based-access-control)
- [Panduan Instalasi & Menjalankan](#-panduan-instalasi--menjalankan)
- [Pengujian Otomatis (Automated Testing)](#-pengujian-otomatis-automated-testing)
- [Panduan Deployment Produksi](#-panduan-deployment-produksi)
- [Struktur Direktori Proyek](#-struktur-direktori-proyek)
- [Metodologi Scrum & Dokumentasi Perubahan](#-metodologi-scrum--dokumentasi-perubahan)
- [Lisensi](#-lisensi)

---

## 🏢 Tentang Sistem

**PT Arsikon Cipta Karya WMS (Construction Warehouse Management System)** adalah platform terpadu berbasis web yang dirancang khusus untuk memfasilitasi tata kelola rantai pasok (*supply chain*), pengadaan material (*procurement*), distribusi logistik antar-gudang, pengelolaan peralatan proyek (*tool lifecycle*), pencatatan nota pembelian harian, serta audit stok real-time pada industri konstruksi berskala besar.

Sistem ini memecahkan kompleksitas operasional kontraktor dengan menerapkan pemisahan tugas (*Separation of Duties*), isolasi multi-gudang (Gudang Pusat vs Gudang Proyek), reservasi kuota stok *in-transit*, mitigasi kerugian pengiriman (*transit loss claim*), serta pelaporan harian yang akuntabel.

---

## ⚡ Arsitektur & Fitur Utama

```
                     ┌─────────────────────────────────────────────────────────┐
                     │          KANTOR PUSAT / ADMIN PUSAT / OWNER             │
                     │  - Manajemen Master Data (Material, Alat, Supplier)     │
                     │  - Persetujuan PO Resmi & Verifikasi Pembayaran Vendor  │
                     │  - Audit Trail & Pelaporan Eksekutif Konsolidasi        │
                     └────────────────────────────┬────────────────────────────┘
                                                  │
                      ┌───────────────────────────┴───────────────────────────┐
                      ▼                                                       ▼
  ┌───────────────────────────────────────┐               ┌───────────────────────────────────────┐
  │         GUDANG PUSAT (CENTRAL)        │               │      GUDANG PROYEK (SITE / FIELD)     │
  │  - Penerimaan PO Supplier (GR)        │               │  - Permintaan Material (PO Lapangan)  │
  │  - Pemotongan Stok Distribusi Out     │◄─[Surat Jalan]┤  - Penerimaan Distribusi In           │
  │  - Penanganan Klaim Kerugian Transit  │ (In-Transit)  │  - Pencatatan Nota Pembelian Harian   │
  │  - Stock Opname & Kalibrasi Berkala   │               │  - Pemakaian Material Lapangan (Bon)  │
  └───────────────────────────────────────┘               └───────────────────────────────────────┘
```

### 1. Isolasi & Manajemen Multi-Gudang
- **Hierarki Gudang:** Membedakan peran operasional **Gudang Pusat** (penyimpanan induk & logistik transit) dan **Gudang Proyek** (titik konsumsi material dan penempatan alat site).
- **Workspace Switcher:** Fitur pergantian gudang aktif berbasis sesi pengguna secara instan tanpa perlu *re-login*.

### 2. Tata Kelola Pengadaan & Pembelian (Procurement & Purchasing)
- **Multi-PO per Procurement Request (PR):** Mendukung pemecahan 1 PR ke beberapa Purchase Order (PO) dengan validasi akumulasi kuota otomatis (`qty_ordered <= qty_approved`).
- **Separation of Duties (Anti Self-Approval):** Pencegahan persetujuan atau penolakan dokumen pengadaan oleh pembuat dokumen itu sendiri.
- **Pencetakan Ganda (Dual PO Print):**
  1. *Cetak PO Resmi:* Format resmi lengkap dengan rincian harga satuan, PPN, dan grand total untuk supplier/vendor.
  2. *Cetak PO Lapangan (Blind PO):* Format tanpa harga (nominal Rp 0) khusus pekerja lapangan untuk mencegah kebocoran informasi komersial rahasia.

### 3. Distribusi, Surat Jalan & Mitigasi Kerugian Transit
- **Surat Jalan (Distributions):** Penerbitan pengiriman antar-gudang dengan armada/driver, mengunci kuota stok `qty_in_transit`.
- **Konfirmasi Penerimaan (Goods Receipts):** Pencocokan fisik di lokasi tujuan.
- **Pencatatan Selisih Transit (`transit_losses`):** Pelacakan kerugian/kerusakan barang selama pengangkutan dengan alur persetujuan klaim ke vendor atau tanggung jawab internal.

### 4. Pencatatan Nota Pembelian Harian (Daily Purchase Receipts)
- **Modul Nota Harian:** Khusus mencatat belanja material operasional mendesak di toko sekitar lokasi proyek.
- **Pengelompokan Mingguan & Harian:** Tampilan kartu dossier yang mengelompokkan belanja per minggu dan per tanggal nota dengan rekapitulasi total biaya otomatis.
- **Laporan Cetak & Berkas Fisik:** Ekspor cetak PDF laporan mingguan/bulanan dan penyimpanan berkas bukti nota/kuitansi digital.

### 5. Manajemen Siklus Hidup Alat Kerja (Tools Management)
- **Tool Inventory & Assignments:** Peminjaman alat kerja antar-gudang dan pekerja proyek dengan otorisasi mandor/admin.
- **Inspeksi Pengembalian:** Verifikasi kelayakan fisik alat saat dikembalikan (Baik, Rusak Ringan, Rusak Berat, Hilang).
- **Pemeliharaan (Maintenance):** Pencatatan riwayat servis berkala dan kesiapan operasional mesin/peralatan berat.

### 6. Penggunaan Material & Stock Opname
- **Pemisahan `qty_received` dan `qty_issued`:** Pencatatan bon pengeluaran material ke pekerja proyek dibatasi ketat hanya pada barang yang telah fisik tiba di lokasi proyek.
- **Stock Opname Digital:** Rekonsiliasi fisik vs catatan sistem dengan kalkulasi otomatis selisih (*discrepancy*) dan penyesuaian (*adjustment*) stok terotorisasi.

---

## 🛡 Keamanan Tingkat Enterprise (OWASP Hardening)

Aplikasi dibangun dengan standar kepatuhan **OWASP Top 10** dan **OWASP ASVS Level 2**:

1. **Content Security Policy & Security Headers:**
   - Header terpasang global: `X-Frame-Options: DENY` (anti clickjacking), `X-Content-Type-Options: nosniff`, `Referrer-Policy: strict-origin-when-cross-origin`, dan `Permissions-Policy`.
   - Content Security Policy (CSP) mode *Report-Only* terstandarisasi untuk melindungi integritas skrip frontend.
2. **Perlindungan Brute Force & Session Fixation:**
   - Throttle login berjenjang: maksimal **5 percobaan per 15 menit** dengan isolasi per IP dan kombinasi email+IP.
   - Pemasangan middleware `auth.session` (`AuthenticateSession`) untuk terminasi sesi ganda secara aman saat terjadi pergantian kata sandi (`logoutOtherDevices`).
3. **Penyimpanan Berkas Aman (Layer 5 File Upload Pipeline):**
   - Validasi ganda ekstensi dan MIME type nyata via PHP Fileinfo.
   - Penyimpanan berkas menggunakan nama acak hash unik pada disk privat non-eksekusi.
   - Re-encoding gambar menggunakan ekstensi GD untuk melucuti payload metadata jahat (*anti-steganography* & *anti-PHP2Shell*).
4. **Pencegahan Celah Stored XSS:**
   - Seluruh data dinamis input pengguna di-render menggunakan sanitasi Blade standar `{{ }}` dan `@json()`, mengeliminasi raw tag unescaped `{!! !!}`.
5. **Dual-Write Audit Trail System:**
   - Tabel `security_logs` mencatat event berisiko tinggi (`SECURITY_LOCKOUT`, `AUTH_FAILED`, `ACCESS_FORBIDDEN`, `ROLE_CHANGE`) dengan sanitasi otomatis data sensitif (password/token).
   - Seluruh aktivitas keamanan dijembatani otomatis ke tabel `audit_logs` untuk rekam jejak investigasi forensik.

---

## 🎨 Desain Antarmuka & User Experience (UX)

- **Solid Corporate Orange Brand (`#EA580C`):** Warna primer korporat PT Arsikon yang kontras, berwibawa, dan mudah dikenali.
- **Liquid Crystal Glassmorphism Sidebar:**
  - Item menu aktif menggunakan kapsul kaca semi-transparan (`rgba(255, 255, 255, 0.22)`) dengan border rim specular halus dan efek `backdrop-filter: blur(8px)`.
  - Item hover memiliki animasi kilau kaca (*glass hover shimmer*) bergradien lembut yang responsif.
  - Kartu workspace dan profil pengguna bertekstur *frosted glass* 12px blur.
- **Tata Letak Adaptif:** Responsif di semua resolusi layar (Desktop lebar, Laptop 13-14", Tablet, dan Layar Ponsel).
- **Anti-Flicker Standar:** Tampilan terang yang konsisten dan nyaman di mata untuk penggunaan jangka panjang di lapangan maupun kantor.

---

## 🛠 Tech Stack

| Komponen | Teknologi yang Digunakan |
| :--- | :--- |
| **Backend Framework** | Laravel 12.x (Laravel 12.70.1) |
| **Bahasa Pemrograman** | PHP 8.2+ (Typed Properties, Enums, Match Expressions) |
| **Database Engine** | MySQL 8.0+ / MariaDB 10.6+ (InnoDB, Transactional Locks) |
| **Otorisasi & Role** | Spatie Laravel-Permission 6.x |
| **Frontend Styling** | Tailwind CSS 3.x + Custom Enterprise Design System |
| **Interaktivitas UI** | Alpine.js 3.x |
| **Asset Bundler** | Vite 5.x |
| **Ikonografi** | Font Awesome 6.5 Pro/Free Icons |
| **Tipografi** | Inter (Google Fonts via preconnect) |
| **Format Dokumen** | Native Responsive CSS Print Engine (Standar A4 / Surat Jalan) |

---

## 👥 Daftar Akun Bawaan (Role-Based Access Control)

Setelah proses *seeding* dijalankan, sistem menyediakan akun default untuk setiap peran dengan kata sandi bawaan: **`password`** (atau nilai dari variabel `SEED_DEFAULT_PASSWORD`):

| Peran (Role) | Alamat Email | Kata Sandi | Deskripsi & Batas Wewenang |
| :--- | :--- | :--- | :--- |
| **Owner** | `owner@arsikon.co.id` | `password` | **Monitoring Eksekutif:** Akses baca penuh ke laporan, metrik bisnis, mutasi kas, dan audit trail. Tidak dapat mengubah/membuat transaksi fisik secara langsung. |
| **Admin Pusat** | `admin.pusat@arsikon.co.id` | `password` | **Super Administrator:** Mengelola seluruh data master, pengguna, gudang, proyek, persetujuan PO, dan pengaturan sistem global. |
| **Admin Gudang Pusat** | `gudang.pusat@arsikon.co.id` | `password` | **Logistik Pusat:** Mengelola stok Gudang Pusat, penerimaan barang dari supplier, pengiriman Surat Jalan antar-gudang, dan stock opname pusat. |
| **Admin PO (Pusat)** | `admin.po@arsikon.co.id` | `password` | **Pengadaan Pusat:** Membuat & mengelola Purchase Order (PO) resmi ke supplier, Procurement Request, dan verifikasi faktur pembayaran vendor. |
| **Admin Gudang Proyek** | `admin.proyek1@arsikon.co.id` | `password` | **Logistik Lapangan:** Mengelola gudang proyek site, konfirmasi penerimaan surat jalan, pencatatan pemakaian material proyek, dan retur sisa material. |
| **Admin PO Proyek** *(admin-po-nota)* | `po.proyek@arsikon.co.id` | `password` | **Nota Harian Proyek:** Akses penuh pencatatan nota pembelian harian di sekitar lokasi proyek, kuitansi belanja tunai, dan read-only PO Lapangan. |
| **Karyawan** | `karyawan@arsikon.co.id` | `password` | **Pekerja Lapangan:** Mengajukan permintaan kebutuhan material lapangan (PO Lapangan), peminjaman alat kerja proyek, dan melihat stok tersedia. |

---

## 🚀 Panduan Instalasi & Menjalankan

### 1. Prasyarat Sistem
Pastikan perangkat Anda telah terpasang:
- **PHP >= 8.2** (Ekstensi aktif: `pdo_mysql`, `mbstring`, `fileinfo`, `gd`, `xml`, `bcmath`, `curl`)
- **Composer >= 2.x**
- **Node.js >= 18.x** dan **npm**
- **MySQL >= 8.0** atau **MariaDB >= 10.6**
- **Git**

### 2. Kloning Repositori
```bash
git clone https://github.com/mdvckr/PT-Arsikon.git
cd PT-Arsikon
```

### 3. Pemasangan Dependensi
```bash
# Instal dependensi PHP
composer install

# Instal dependensi JavaScript & CSS
npm install
```

### 4. Konfigurasi Lingkungan (`.env`)
Salin berkas contoh konfigurasi:
```bash
cp .env.example .env
```
Buka berkas `.env` dan sesuaikan kredensial basis data Anda:
```env
APP_NAME="PT-Arsikon"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pt_arsikon
DB_USERNAME=root
DB_PASSWORD=
```
Hasilkan *Application Encryption Key*:
```bash
php artisan key:generate
```

### 5. Migrasi & Seeding Basis Data
Jalankan migrasi seluruh tabel beserta data awal (*roles, permissions, default users, warehouses, sample materials*):
```bash
php artisan migrate:fresh --seed
```

### 6. Tautkan Storage Simbolik
```bash
php artisan storage:link
```

### 7. Kompilasi Aset Frontend
Untuk mode pengembangan (*hot module replacement*):
```bash
npm run dev
```
Atau kompilasi bundel produksi:
```bash
npm run build
```

### 8. Menjalankan Server Lokal
```bash
php artisan serve
```
Akses aplikasi melalui peramban di: **`http://127.0.0.1:8000`**

---

## 🧪 Pengujian Otomatis (Automated Testing)

Aplikasi dilengkapi dengan rangkaian unit & feature test komprehensif berbasis **PHPUnit** untuk menjamin kestabilan alur bisnis, integritas otorisasi, dan perlindungan keamanan:

```bash
# Menjalankan seluruh rangkaian tes otomatis
php artisan test

# Menguji integritas struktur navigasi & role sidebar
php artisan test tests/Feature/SidebarNavigationTest.php

# Menguji kontrol akses RBAC Nota Harian & PO Lapangan
php artisan test tests/Feature/AdminPoNotaRbacTest.php

# Menguji lapisan keamanan OWASP & mitigasi celah keamanan
php artisan test tests/Feature/SecurityHardeningPhaseTest.php

# Menguji pemisahan wewenang persetujuan (Separation of Duties)
php artisan test tests/Feature/SeparationOfDutiesTest.php

# Menguji counter penomoran dokumen anti race-condition
php artisan test tests/Feature/DocumentCounterTest.php
```

---

## 🚢 Panduan Deployment Produksi

Saat mendistribusikan aplikasi ke server produksi (VPS / Dedicated Server dengan Nginx / Apache):

### 1. Optimasi Cache Laravel
Gunakan perintah optimasi bawaan framework untuk meminimalkan I/O disk dan mempercepat waktu muat:
```bash
# Cache seluruh konfigurasi environment
php artisan config:cache

# Cache pendaftaran seluruh rute aplikasi
php artisan route:cache

# Pre-compile seluruh template tampilan Blade
php artisan view:cache

# Optimasi event listener
php artisan event:cache

# (Atau jalankan seluruh optimasi sekaligus)
php artisan optimize
```

### 2. Kompilasi Aset Produksi
```bash
npm run build
```

### 3. Konfigurasi Virtual Host Nginx (Rekomendasi)
```nginx
server {
    listen 80;
    server_name arsikon.yourdomain.com;
    root /var/www/PT-Arsikon/public;

    add_header X-Frame-Options "DENY";
    add_header X-Content-Type-Options "nosniff";
    add_header Referrer-Policy "strict-origin-when-cross-origin";

    index index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### 4. Background Worker & Task Scheduling
Jalankan cron job scheduler Laravel setiap menit:
```bash
* * * * * cd /var/www/PT-Arsikon && php artisan schedule:run >> /dev/null 2>&1
```

---

## 📂 Struktur Direktori Proyek

```
PT-Arsikon/
├── app/
│   ├── Console/Commands/       # Artisan commands (SecurityAudit, dsb.)
│   ├── Http/
│   │   ├── Controllers/        # Web controllers (Material, PO, GR, Nota, dsb.)
│   │   ├── Middleware/         # SecurityHeaders, AuthenticateSession, Role guards
│   │   └── Requests/           # Form Requests dengan validasi terstruktur
│   ├── Models/                 # Eloquent Models (User, Inventory, TransitLoss, dsb.)
│   ├── Policies/               # Kebijakan otorisasi (PurchaseOrderPolicy, ReceiptPolicy)
│   ├── Providers/              # AppServiceProvider, AuthServiceProvider
│   └── Services/               # Business services (SecureUploadService, SecurityLogger)
├── config/                     # Konfigurasi sistem (security, filesystems, auth)
├── database/
│   ├── migrations/             # Migrasi skema database terurut kronologis
│   └── seeders/                # Database seeders (RoleAndPermissionSeeder, MasterDataSeeder)
├── docs/                       # Dokumentasi resmi proyek
│   ├── PERUBAHAN.md            # Catatan riwayat Sprint & Perubahan Arsitektur (Scrum)
│   └── master_plan_security... # Cetak biru keamanan OWASP ASVS
├── public/                     # Direktori root web publik & manifest Vite
├── resources/
│   ├── css/app.css             # Entry point CSS & Enterprise Sidebar styling
│   ├── js/app.js               # Entry point Alpine.js & Notification Center
│   └── views/                  # Template tampilan Blade
│       ├── components/         # Komponen UI Blade modular
│       ├── layouts/app.blade   # Layout induk aplikasi (Glass Sidebar & Topbar)
│       └── [modul]/            # View per modul (materials, purchase-receipts, dsb.)
├── routes/
│   ├── web.php                 # Rute web aplikasi terkelompok peran
│   └── console.php             # Rute konsol interaktif
├── storage/                    # Simpanan berkas privat, logs, dan cache aplikasi
└── tests/
    ├── Feature/                # Pengujian integrasi alur bisnis, RBAC, dan modul
    └── Unit/                   # Pengujian unit keamanan & helper terisolasi
```

---

## 📋 Metodologi Scrum & Dokumentasi Perubahan

Pengembangan sistem ini mengadopsi kerangka kerja **Agile Scrum**. Setiap iterasi, refactoring, mitigasi bug, maupun penambahan fitur dicatat secara transparan pada dokumen rekam jejak:

👉 **[Lihat Riwayat Sprint di docs/PERUBAHAN.md](docs/PERUBAHAN.md)**

Format pencatatan mencakup:
- **Sprint Goal & User Story:** Target fungsional dari sudut pandang pemangku kepentingan.
- **Definition of Done (DoD) & Acceptance Criteria:** Parameter verifikasi kualitas sebelum pekerjaan dianggap selesai.
- **Artifacts:** Daftar berkas yang diubah, ditambah, atau dihapus.
- **Quality Review:** Laporan hasil eksekusi *test suite* otomatis dan pengujian manual.

---

## 📄 Lisensi

Hak Cipta © 2026 **PT Arsikon Cipta Karya**. Seluruh hak cipta dilindungi undang-undang.  
Aplikasi ini merupakan perangkat lunak *proprietary* milik PT Arsikon Cipta Karya dan hanya diperuntukkan bagi penggunaan internal operasional perusahaan.
