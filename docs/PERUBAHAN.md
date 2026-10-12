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

### T3. Guard Status PO & Pengembalian Kuota (PO Guard & Status Flow)
- **Tujuan**:
  - Menambah enum status PO `approved` dan `closed` (short close).
  - Melindungi action `send()` agar hanya dapat dikirim ke supplier bila berstatus `approved`.
  - Melindungi action `cancel()` agar ditolak jika terdapat Goods Receipt (GR) yang telah berstatus `confirmed`.
  - Mengembalikan / mengurangi `qty_ordered` pada item MR dan PR terkait saat PO dibatalkan, serta menghitung ulang status PR (kembali ke `approved` atau `partially_ordered`).
  - Membungkus action `send()` dan `cancel()` dalam transaksi DB dengan `lockForUpdate()`.
  - Menerapkan otorisasi ketat pada `send()` dan `cancel()` PO.
- **Perubahan File**:
  - `database/migrations/2026_10_12_000002_update_procurement_and_material_request_schema.php`: Migrasi perluasan enum status PO & PR, penambahan kolom `risk_bearer`, `delivery_warehouse_id`, `tool_id`, `lost_qty`, `short_closed_qty`, `procurement_request_item_id`, `qty_ordered` pada item PR dan item MR.
  - `app/Models/PurchaseOrder.php`: Menambahkan enum status label & color untuk `approved` dan `closed`, fillable baru, relasi `deliveryWarehouse()` dan `goodsReceipts()`.
  - `app/Models/PurchaseOrderItem.php`: Menambahkan fillable baru `tool_id`, `lost_qty`, `short_closed_qty`, `procurement_request_item_id` dan relasi terkait.
  - `app/Models/ProcurementRequest.php` & `ProcurementRequestItem.php`: Update fillable, cast, dan status label/color untuk `partially_ordered` dan `fully_ordered`.
  - `app/Models/MaterialRequestItem.php`: Update fillable dan casts untuk kolom logistik & pengadaan baru.
  - `app/Http/Controllers/PurchaseOrderController.php`: Guard method `send()` (hanya approved, transaksi DB + lock), guard `cancel()` (hanya draft/approved/sent, tolak jika ada GR confirmed, rollback kuota `qty_ordered` MR & PR, hitung ulang status PR, transaksi DB + lock).
  - `resources/views/purchase-orders/show.blade.php`: Update tombol UI (Setujui PO saat draft, Kirim ke Supplier hanya saat approved, Batalkan PO saat draft/approved/sent, alert error).
  - `tests/Feature/PurchaseOrderGuardTest.php`: Test merah awal (bukti bug: send dari draft lolos tanpa error, cancel dengan GR confirmed lolos, kuota PR/MR tidak berkurang) lalu hijau setelah guard diimplementasikan.
- **Hasil Pengujian**: Seluruh 164 test (561 assertions) berstatus HIJAU (PASS).

### T4. Perbaikan Jalur Surat Jalan / Pemindahan Stok (Distributions & Transit Losses)
- **Tujuan**:
  - Pengetatan syarat penerbitan Surat Jalan: MR harus berstatus `approved` atau `partially_fulfilled` (menghapus celah auto-approval shortcut saat `submitted`).
  - Menolak item custom/manual (hanya material terdaftar di master).
  - Menolak item yang dialokasikan untuk pembelian (`fulfillment == 'purchase'`).
  - Kuota kirim ketat: `quantity <= (qty_approved - qty_in_transit - qty_received)`.
  - Saat `ship()`: memotong stok gudang asal dengan mutasi `'distribution_out'` dan menaikkan `qty_in_transit` pada item MR (status MR tidak diubah menjadi fulfilled saat shipped).
  - Saat `receive()`: menambah stok gudang tujuan dengan mutasi `'distribution_in'`, mengupdate `qty_in_transit` dan `qty_received` pada item MR, mencatat selisih ke tabel baru `transit_losses`, me-zero-out sisa transit selisih, dan menghitung status MR dari `qty_received` (`fulfilled` jika semua diterima penuh, `partially_fulfilled` jika sebagian).
- **Perubahan File**:
  - `database/migrations/2026_10_12_000003_create_transit_losses_table.php`: Migrasi pembuatan tabel `transit_losses` lengkap dengan relasi ke distributions, goods_receipts, purchase_orders, materials, tools, status claim/recorded, dan tracking approval.
  - `app/Models/TransitLoss.php`: Model Eloquent untuk pencatatan kerugian/selisih transit.
  - `app/Models/Distribution.php` & `DistributionItem.php`: Penambahan relasi `transitLosses()`.
  - `app/Services/DistributionService.php`: Pengetatan validasi status MR, penolakan item custom & purchase, validasi kuota kirim, mutasi stok `'distribution_out'` dan `'distribution_in'`, update `qty_in_transit` & `qty_received` MR, pencatatan `TransitLoss`, dan kalkulasi status MR dari penerimaan riil.
  - `tests/Feature/DistributionStockTransferTest.php`: Test merah awal (bukti 6 bug/celah) lalu hijau setelah perbaikan.
  - `tests/Feature/DistributionAndReceivingTest.php`: Penyesuaian test lama agar selaras dengan pengetatan T4.
- **Hasil Pengujian**: Seluruh 170 test (547 assertions) berstatus HIJAU (PASS).

### T5. Sinkronisasi PO dan PR (Multi-PO per PR & Kuota Tersisa)
- **Tujuan**:
  - Mendukung penerbitan multi-PO dari 1 Procurement Request (PR) dengan validasi kuota ketat: `quantity PO <= (item.quantity - item.qty_ordered)`.
  - Pembuatan PO menolak input yang melebihi sisa kuota PR dan menampilkan pesan validasi informatif.
  - Menyimpan `procurement_request_item_id` pada setiap item PO.
  - Mengupdate akumulasi `item.qty_ordered += qty_po` pada item PR dan MR.
  - Menghitung ulang status PR secara dinamis: `partially_ordered` jika sebagian dipesan, dan `fully_ordered` jika seluruh kuota kuantitas telah terpesan.
  - Mengembalikan kuota saat PO dibatalkan (cancel) dan mengembalikan status PR ke `partially_ordered` atau `approved`.
- **Perubahan File**:
  - `app/Http/Controllers/PurchaseOrderController.php`: Validasi sisa kuota PR per baris item, pembungkusan store dalam DB transaction, pencatatan `procurement_request_item_id`, inkremen `qty_ordered` pada item PR dan MR, serta update otomatis status PR (`partially_ordered` vs `fully_ordered`).
  - `tests/Feature/ProcurementMultiPoTest.php`: Test merah awal (bukti gagal: `qty_ordered` tidak bertambah dan validasi kuota belum ada) lalu hijau setelah sinkronisasi diterapkan.
- **Hasil Pengujian**: Seluruh 171 test (562 assertions) berstatus HIJAU (PASS).

### T6. Sinkronisasi PO dan MR (Multi-PO per MR & Kuota Tersisa)
- **Tujuan**:
  - Memvalidasi item MR yang dibuatkan PO harus berstatus pemenuhan `fulfillment == 'purchase'`. Jika berstatus `stock`, pembuatan PO ditolak karena harus diproses lewat Surat Jalan.
  - Memvalidasi kuota PO tidak melebihi sisa kuota yang disetujui: `quantity PO <= (item.qty_approved - item.qty_ordered)`.
  - Mengakumulasikan `item.qty_ordered += qty_po` pada item MR saat PO dibuat.
  - Mengembalikan kuota `qty_ordered` pada item MR saat PO dibatalkan (cancel).
- **Perubahan File**:
  - `app/Http/Controllers/PurchaseOrderController.php`: Validasi pemenuhan `fulfillment` dan sisa kuota MR pada method `store`.
  - `tests/Feature/MaterialRequestMultiPoTest.php`: Test merah awal (bukti gagal: penolakan kuota & status fulfillment stock belum ada) lalu hijau setelah perbaikan.
- **Hasil Pengujian**: Seluruh 173 test (574 assertions) berstatus HIJAU (PASS).

### T18. Penamaan Tampilan "PO Lapangan" dan Istilah Proyek (Label Saja)
- **Tujuan**:
  - Mengganti teks label antarmuka pengguna dari "Permintaan Material" / "Material Request" menjadi "PO Lapangan" pada seluruh menu sidebar, judul halaman, breadcrumb, tombol aksi, modal, flash message, dan notifikasi.
  - Mempertahankan integritas teknis tanpa mengubah nama class, nama tabel, kolom database, nama route, route URL, dan permission spatie (`view material requests`, `create material requests`, `approve material requests`).
  - Memformat tampilan gudang tujuan MR menjadi "Nama Gudang - Nama Proyek" bila terhubung ke proyek via accessor `getDisplayNameAttribute()` pada model `Warehouse`.
  - Mengubah label field menjadi "Nama Proyek" untuk gudang bertipe proyek pada halaman detail info card dan formulir.
  - Asumsi & Batas: Permintaan yang menyebut contoh "pembangunan gedung, proyek, dll." diperlakukan murni sebagai penamaan/label display; tidak ada penambahan tabel atau jenis proyek baru di luar lingkup.
- **Perubahan File**:
  - `app/Models/Warehouse.php`: Penambahan accessor `display_name` yang memformat `Nama Gudang - Nama Proyek` jika memiliki relasi ke project aktif.
  - `resources/views/layouts/app.blade.php`: Penggantian menu sidebar Transaksi menjadi "PO Lapangan" dan sub-deskripsi headline.
  - `resources/views/material-requests/index.blade.php`: Penggantian judul, breadcrumb, tombol "Buat PO Lapangan", placeholder pencarian, header tabel "No. PO Lapangan", "Proyek / Gudang", format `display_name`, dan empty state.
  - `resources/views/material-requests/create.blade.php`: Penggantian judul, breadcrumb, label field "Nama Proyek (Gudang Pemohon)", pemakaian `display_name` pada opsi pilihan gudang, dan tombol "Ajukan PO Lapangan".
  - `resources/views/material-requests/show.blade.php`: Penggantian breadcrumb, info alert item manual, label "Item PO Lapangan", modal penolakan "Tolak PO Lapangan", serta info row dinamis "Nama Proyek" dengan format `display_name`.
  - `resources/views/dashboard.blade.php`: Penggantian widget metriks "PO Lapangan Pending", aksi cepat "PO Lapangan Baru", dan tabel "PO Lapangan Terbaru".
  - `resources/views/distributions/create.blade.php`, `material-usages/create.blade.php`, `material-usages/show.blade.php`, `purchase-orders/create.blade.php`, `users/create.blade.php`, `users/edit.blade.php`: Penggantian seluruh label referensi MR ke "PO Lapangan".
  - `app/Http/Controllers/MaterialRequestController.php`: Update flash success dan error message ke "PO Lapangan".
  - `app/Http/Controllers/RoleController.php`: Update deskripsi peran Karyawan ke "PO Lapangan".
  - `app/Services/MaterialRequestService.php`, `DistributionService.php`, `MaterialUsageService.php`: Update exception dan pesan notifikasi ke "PO Lapangan".
  - `tests/Feature/PoLapanganLabelTest.php`: Test merah awal (bukti gagal: assertSee 'PO Lapangan' & assertDontSee 'Permintaan Material') lalu hijau setelah refactor selesai.
- **Hasil Pengujian**: Seluruh 177 test (588 assertions) berstatus HIJAU (PASS).

### T19. Kategori pada PO Lapangan
- **Tujuan**:
  - Menambahkan kolom `category_id` (nullable FK ke `categories` ber-index) pada tabel `material_request_items`.
  - Mengisi `category_id` pada item master secara otomatis dari kategori material/alat di server dan mengabaikan nilai input klien.
  - Memvalidasi `category_id` pada item custom (harus valid di database).
  - Melakukan backfill aman pada migrasi untuk data lama: item master material dan tool diisi dari kategori master datanya; item custom lama dibiarkan NULL.
  - Menambahkan dropdown filter Kategori di halaman index PO Lapangan menggunakan `whereHas('items', ...)` dengan eager loading `items.category` tanpa N+1 query.
  - Menampilkan kolom Kategori pada tabel item di halaman detail (`show.blade.php`).
  - Menambahkan penyaringan pilihan material berbasis kategori di form pembuatan (`create.blade.php`) menggunakan client-side filtering instan tanpa request berulang ke server.
- **Perubahan File**:
  - `database/migrations/2026_10_12_000004_add_category_id_to_material_request_items_table.php`: Migrasi penambahan kolom `category_id`, foreign key, index, serta script backfill otomatis untuk item master lama.
  - `app/Models/MaterialRequestItem.php`: Penambahan `category_id` pada `$fillable`, relasi `category(): BelongsTo`, dan method helper `displayCategoryName()`.
  - `app/Services/MaterialRequestService.php`: Pengisian otomatis `category_id` dari master material untuk item master (mengabaikan input klien) dan validasi `category_id` untuk item custom.
  - `app/Http/Controllers/MaterialRequestController.php`: Eager load `items.category`, penanganan query filter `category_id`, pengiriman data kategori ke view index, serta eager loading pada method `show`.
  - `resources/views/material-requests/index.blade.php`: Dropdown filter Kategori pada toolbar pencarian.
  - `resources/views/material-requests/show.blade.php`: Penambahan kolom Kategori pada rincian tabel Item PO Lapangan.
  - `resources/views/material-requests/create.blade.php`: Dropdown filter Kategori di atas tabel material dan pembaruan fungsi `filterMaterials()` untuk menyaring material instan.
  - `tests/Feature/PoLapanganCategoryTest.php`: Pengujian komprehensif (test merah lalu hijau) yang memverifikasi penyimpanan kategori dari master material, penolakan kategori tidak valid pada custom item, penerimaan kategori valid pada custom item, tampilan fallback `-` untuk data lama dengan category NULL, dan filter kategori di index.
- **Hasil Pengujian**: Seluruh 182 test (603 assertions) berstatus HIJAU (PASS).

### T20. Field Header: Sumber Material, Gudang Pemohon, dan Dibutuhkan Tgl
- **Tujuan**:
  - Menyesuaikan label `from_warehouse_id` menjadi "Sumber Material (Kepada)". Pada saat pengajuan ini merupakan usulan pemohon dan penyetuju dapat mengubahnya saat approve. Nilai kosong/null berarti "Pengadaan / Beli ke Vendor". Sumber material divalidasi tidak boleh sama dengan gudang pemohon.
  - Dropdown Gudang Pemohon (`to_warehouse_id`): hanya memuat gudang proyek yang boleh diakses pengguna (menggunakan `accessibleWarehouseIds()` dan validasi server dengan `hasAccessToWarehouse()`). Menampilkan format `display_name` ("Nama Gudang - Nama Proyek").
  - Menangani field "Dibutuhkan Tgl" (`needed_at`): validasi `after_or_equal:today` saat pembuatan.
  - Memisahkan tampilan "Tanggal Order" (otomatis dari `created_at`, tidak dapat diedit) dan "Dibutuhkan Tgl" (`needed_at`) sebagai dua field/kolom terpisah di daftar (`index.blade.php`), detail (`show.blade.php`), dan cetakan dokumen (`print.blade.php`).
  - Menambahkan penanda "Terlambat" (overdue) yang dihitung secara dinamis saat tampil di view (`needed_at < today` dan status belum selesai), tanpa disimpan di database.
  - Menambahkan dukungan pengurutan (`created_at` vs `needed_at`) dan filter status waktu (terlambat) pada index.
- **Perubahan File**:
  - `app/Models/MaterialRequest.php`: Menambahkan `needed_at` ke `$fillable` dan `$casts` (date), serta accessor helper `is_overdue`.
  - `app/Services/MaterialRequestService.php`: Penyesuaian `createRequest` dengan `$sourceWarehouse` nullable (`?Warehouse`), parameter `$neededAt`, dan validasi sumber material tidak boleh sama dengan gudang pemohon.
  - `app/Http/Controllers/MaterialRequestController.php`:
    - Method `create`: Hanya mengambil gudang yang boleh diakses pengguna via `accessibleWarehouseIds()`.
    - Method `store`: Validasi `source_warehouse_id` nullable, validasi server `hasAccessToWarehouse` (abort 403 jika tidak berhak), validasi kesamaan gudang sumber dan pemohon, penyimpanan `needed_at` ke service.
    - Method `index`: Dukungan sorting (`created_at` / `needed_at`) dan filter `overdue`.
  - `resources/views/material-requests/create.blade.php`: Update label "Sumber Material (Kepada)" dengan opsi default "— Pengadaan / Beli ke Vendor —" (nullable), label "Dibutuhkan Tgl".
  - `resources/views/material-requests/show.blade.php`: Pemisahan "Tanggal Order" dan "Dibutuhkan Tgl", tampilan badge "Terlambat" jika tanggal dibutuhkan lewat, dan label "Sumber Material (Kepada)".
  - `resources/views/material-requests/index.blade.php`: Pemisahan kolom tabel "Tgl Order" dan "Dibutuhkan Tgl" dengan penanda "Terlambat", serta filter pengurutan dan status waktu.
  - `resources/views/material-requests/print.blade.php`: Pemisahan baris "Tanggal Order" dan "Dibutuhkan Tgl", serta fallback "Pengadaan / Beli ke Vendor" jika sumber null.
  - `tests/Feature/PoLapanganHeaderFieldTest.php`: Test komprehensif memverifikasi penolakan gudang di luar akses (403), penolakan sumber sama dengan target, penolakan tanggal dibutuhkan sebelum hari ini, penyimpanan terpisah tanggal order & dibutuhkan, dan indikator overdue dinamis.
- **Hasil Pengujian**: Seluruh 187 test (617 assertions) berstatus HIJAU (PASS).

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
