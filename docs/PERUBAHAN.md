# Dokumentasi Perubahan & Refaktor Alur Pengadaan dan Gudang (PT-Arsikon)

## 1. Ringkasan Per Tugas
Dokumentasi ini mencatat setiap perubahan, file terkait, hasil pengujian, dan bukti bug (test merah awal sebelum implementasi).

### Baseline
- **Pembersihan & Restorasi Fixture Pengujian**:
  - Restorasi seeding data Supplier di `MasterDataSeeder.php` yang sebelumnya hilang pada commit lama.
  - Perbaikan scoping notifikasi gudang proyek pada `NotificationHelper.php` agar tidak mengirim notifikasi approval lokal gudang proyek ke Admin Pusat jika notifikasi tersebut murni untuk admin gudang proyek.
  - Penyesuaian `setUp()` pada beberapa test lama (`ToolManagementTest`, `WarehouseIndependenceTest`, `DailyLogTest`, `InventoryAndStockTest`, `ReportTest`, `StockOpnameTest`, `MaterialUsageTest`) agar menggunakan `firstOrCreate` untuk master data material/tool uji, sehingga suite pengujian berjalan independen dan konsisten.
  - Hasil: Seluruh 150 test lama (525 assertions) berstatus HIJAU (PASS).

### T1. Penomoran Aman (Document Counters)
- **Tujuan**: Mengganti pembangkitan nomor berbasis `count() + 1` yang rawan race condition pada `pr_number`, `po_number`, serta generator MR/GR/Distribution dengan tabel `document_counters` dan pessimistic locking `lockForUpdate()` dalam transaksi DB. Format nomor tetap terjaga (`PR-YYYY-0001`, `PO-YYYY-0001`, `REQ-YYYYMMDD-0001`, dsb).
- **Perubahan File**:
  - `database/migrations/2026_10_12_000001_create_document_counters_table.php`: Migrasi tabel `document_counters` dengan field `type`, `year`, `last_number`, dan `UNIQUE(type, year)`.
  - `app/Models/DocumentCounter.php`: Model Eloquent untuk pencatat urutan dokumen.
  - `app/Services/DocumentNumberService.php`: Service pembangkit nomor dokumen berurut aman, melakukan inisialisasi counter dari data eksisting tertinggi jika record counter belum ada, dan mengunci row dengan `lockForUpdate()`.
  - `app/Models/PurchaseOrder.php`: Update model hook `creating` untuk menggunakan `DocumentNumberService::generate('PO')`.
  - `app/Models/ProcurementRequest.php`: Update model hook `creating` untuk menggunakan `DocumentNumberService::generate('PR')`.
  - `app/Models/MaterialRequest.php`: Update `generateRequestNumber()` menggunakan `DocumentNumberService::generate('MR')`.
  - `app/Models/GoodsReceipt.php`: Update `generateReceiptNumber()` menggunakan `DocumentNumberService::generate('GR')`.
  - `app/Models/Distribution.php`: Update `generateDistributionNumber()` menggunakan `DocumentNumberService::generate('DST')`.
  - `tests/Feature/DocumentCounterTest.php`: Test merah awal (sebelum service dibuat) lalu hijau membuktikan nomor sekuensial, inisialisasi dari nilai tertinggi eksisting, dan integrasi dengan model create.
- **Hasil Pengujian**: Test suite lulus 153 passed (535 assertions).

### T2. Pemisahan Peran Persetujuan (Separation of Duties)
- **Tujuan**: Menghilangkan celah self-approval / self-rejection di mana pengguna menyetujui atau menolak dokumen buatannya sendiri pada Material Request, Procurement Request, Purchase Order, dan Payment verification. Selain itu, Exception pada approval/rejection dibungkus try/catch di controller agar menghasilkan flash message error, bukan error 500.
- **Perubahan File**:
  - `app/Services/MaterialRequestService.php`: Validasi penolakan approval jika `approver == requested_by_user_id`.
  - `app/Http/Controllers/MaterialRequestController.php`: Pembungkusan `approve` dan `reject` dalam `try/catch` dengan flash error `back()->with('error', ...)`.
  - `app/Http/Controllers/ProcurementController.php`: Validasi penolakan `approve` dan `reject` jika `auth()->id() == requested_by`.
  - `app/Http/Controllers/PurchaseOrderController.php`: Penambahan method `approve` dengan otorisasi role/permission dan validasi penolakan jika `auth()->id() == created_by`.
  - `routes/web.php`: Pendaftaran route `POST /purchase-orders/{purchaseOrder}/approve`.
  - `app/Http/Controllers/PaymentController.php`: Validasi penolakan `verify` dan `reject` jika `auth()->id() == created_by`.
  - `tests/Feature/SeparationOfDutiesTest.php`: Test merah awal membuktikan 5 celah self-approval/self-verification dan ketiadaan route PO approve, lalu seluruhnya hijau setelah guard diterapkan.
- **Hasil Pengujian**: Seluruh 158 test (546 assertions) berstatus HIJAU (PASS).

---

## 2. Asumsi & Keputusan Otonom
1. **Database Test Terpisah**: Dibuat database MySQL `pt_arsikon_test` dan file `.env.testing` agar pengujian otomatis tidak menyentuh database operasional/lokal utama (`pt_pbag`).
2. **Kategori dan Master Data**: Master data awal di-seed secara idempotent menggunakan `firstOrCreate`.

---

## 3. Penyimpangan dari Dokumen Rencana & Alasan
- *Belum ada penyimpangan hingga tahap baseline.*

---

## 4. Risiko untuk Data Lama & Mitigasi
- Data lama yang ada di database harus aman: migrasi baru selalu menggunakan kolom nullable atau default aman, dan memiliki metode `down()` yang benar.

---

## 5. Langkah Deploy & Rollback
### Deploy
1. `php artisan down`
2. `php artisan migrate --force`
3. `php artisan permissions:sync-procurement` (setelah T16)
4. `php artisan optimize:clear`
5. `php artisan config:cache && php artisan route:cache && php artisan view:cache`
6. `php artisan up`

### Rollback
1. `php artisan migrate:rollback --step=...`
2. `php artisan optimize:clear`

---

## 6. Hal yang Sengaja Tidak Dikerjakan (Di Luar Lingkup)
- Modul di luar lingkup yang ditegaskan dokumen panduan: RAB/BoQ, approval berjenjang berbasis nilai nominal, RFQ/perbandingan supplier, PPN, sewa alat berbayar, retensi/opname subkontraktor, jalur urgent, master vendor/verifikasi vendor, tabel invoice baru.

---

## 7. Keputusan Otonom yang Perlu Dikonfirmasi Manusia (T18 - T24)
- (a) **T21**: Arti fitur di sebelah Alokasi diimplementasikan sebagai kolom "Stok Tersedia" (stok gudang sumber dikurangi alokasi lain + badge Cukup/Kurang, serta sisa dipesan untuk purchase).
- (b) **T22**: Site Manager / Site Engineering sebagai label metadata `requested_as` pada `material_requests`, bukan permission/role Spatie baru.
- (c) **T18**: Penamaan tampilan "PO Lapangan" untuk Material Request di UI, sementara Purchase Order ke vendor tetap Purchase Order.
- (d) **T24**: Urutan grup sidebar Teknis -> Pengadaan -> Logistik di bawah Dashboard.
- (e) **T20**: "Dibutuhkan Tgl" (`needed_at`) dan "Tanggal Order" (`created_at`) sebagai dua field terpisah di antarmuka.
