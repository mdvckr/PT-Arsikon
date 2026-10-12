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

### T6. Sinkronisasi PO dan MR serta Pemisahan Kuota Pemakaian (qty_received vs qty_issued)
- **Tujuan**:
  - Memvalidasi item MR yang dibuatkan PO harus berstatus pemenuhan `fulfillment == 'purchase'`. Jika berstatus `stock`, pembuatan PO ditolak karena harus diproses lewat Surat Jalan.
  - Memvalidasi kuota PO tidak melebihi sisa kuota yang disetujui: `quantity PO <= (item.qty_approved - item.qty_ordered)`.
  - Mengakumulasikan `item.qty_ordered += qty_po` pada item MR saat PO dibuat.
  - Mengembalikan kuota `qty_ordered` pada item MR saat PO dibatalkan (cancel).
  - **Pemisahan Tegas `qty_received` dan `qty_issued` pada Material Usage**:
    - Material Usage terhubung MR membatasi kuota hanya sebesar barang yang sudah tiba di gudang proyek: `kuota = max(0, qty_received - qty_issued)`. Barang yang belum tiba (`qty_in_transit` atau belum dikirim) tidak dapat dikeluarkan.
    - Pemakaian material menaikkan `qty_issued`, BUKAN `qty_received`.
    - Pembatalan bon pemakaian (`cancelUsage`) mengembalikan / mengurangi `qty_issued`.
  - **Eliminasi Pembacaan `qty_fulfilled`**:
    - Seluruh pembacaan kolom `qty_fulfilled` pada controller (`MaterialUsageController`, `DistributionController`), model, dan blade view (`purchase-orders/create`, `material-requests/show`, `material-usages/create`) telah dieliminasi dan dialihkan ke `qty_received` / `qty_issued` / `qty_ordered`.
    - Kolom `qty_fulfilled` hanya dipertahankan untuk penulisan sinkronisasi backfill legacy demi kompatibilitas ke belakang tanpa pembacaan aktif.
- **Perubahan File**:
  - `app/Http/Controllers/PurchaseOrderController.php`: Validasi pemenuhan `fulfillment` dan sisa kuota MR pada method `store`.
  - `app/Services/MaterialUsageService.php`: Validasi kuota MR berbasis `qty_received - qty_issued`, penambahan `qty_issued` saat pemakaian, dan pengurangan `qty_issued` saat pembatalan.
  - `app/Http/Controllers/MaterialUsageController.php`: Penyesuaian `getMRDetails` menghitung `remaining` dari `qty_received - qty_issued`.
  - `app/Http/Controllers/DistributionController.php`: Validasi kuota sisa kirim MR dihitung dari `qty_approved - qty_in_transit - qty_received`.
  - `resources/views/purchase-orders/create.blade.php`: Penggantian kalkulasi sisa order MR menjadi `qty_approved - qty_ordered`.
  - `resources/views/material-requests/show.blade.php`: Penggantian tampilan progress barang tiba menggunakan `qty_received` dan pemakaian `qty_issued`.
  - `resources/views/material-usages/create.blade.php`: Penggantian label tampilan `item.qty_fulfilled` menjadi `item.qty_issued` dan header "Tiba / Pakai".
  - `tests/Feature/MaterialRequestMultiPoTest.php`: Test sinkronisasi kuota PO dan MR.
  - `tests/Feature/MaterialUsageTest.php`: Test pembatasan kuota pemakaian MR `qty_received - qty_issued`, penambahan `qty_issued`, dan pengembalian saat pembatalan.
- **Hasil Pengujian**: Seluruh test lulus, suite pengujian 278 passed (1018 assertions) berstatus HIJAU (PASS).

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

### T21. Fitur di Sebelah Kolom Alokasi ("Stok Tersedia")
- **Tujuan**:
  - Menemukan letak kolom "Alokasi" di antarmuka dan menerapkan fitur kolom baru di sebelahnya.
  - **Temuan Kolom "Alokasi"**: Kolom bertuliskan "Alokasi / Zona" ditemukan pada:
    1. `resources/views/material-requests/show.blade.php` (tabel rincian item pada halaman detail & persetujuan PO Lapangan) -> Diterapkan di sini.
    2. `resources/views/material-requests/create.blade.php` (form isian alokasi pemakaian zona kerja saat pengajuan) -> Dicatat sebagai temuan; kolom form input zona tidak memerlukan indikator stok tersedia karena gudang sumber belum tentu dipilih/diverifikasi saat pengetikan item custom.
  - Menambahkan kolom baru "Stok Tersedia" tepat di sebelah kolom "Alokasi / Zona" pada tabel item halaman detail PO Lapangan (`show.blade.php`):
    - Untuk item master material: menghitung stok gudang sumber dikurangi alokasi lain yang aktif di inventori (`available_stock = quantity - qty_allocated`).
    - Menampilkan badge "Cukup" (hijau) jika stok tersedia >= target kuantitas (disetujui/diminta), dan badge "Kurang" (merah) jika stok tersedia < target kuantitas.
    - Untuk item pemenuhan 'purchase': menampilkan "Sisa dipesan: (qty_approved - qty_ordered)" dilengkapi badge/tautan ke nomor Purchase Order terkait.
  - Sifat fitur: **hanya baca (read-only)** dihitung di sisi server tanpa mengubah stok fisik/alokasi database dan tidak memblokir tombol persetujuan (murni indikator visual dan peringatan keputusan bagi penyetuju).
  - Menggunakan query batch agregat (`whereIn('material_id', ...)`) dan eager loading relasi tanpa N+1 query.
- **Perubahan File**:
  - `app/Models/MaterialRequestItem.php`: Menambahkan relasi `purchaseOrderItems(): HasMany` ke model `PurchaseOrderItem`.
  - `app/Http/Controllers/MaterialRequestController.php`: Eager load `items.purchaseOrderItems.purchaseOrder` dan perhitungan agregat batch `$sourceInventories` pada method `show`.
  - `resources/views/material-requests/show.blade.php`: Penambahan kolom header `Stok Tersedia` dan data baris dengan badge Cukup/Kurang serta info sisa dipesan & tautan PO.
  - `tests/Feature/PoLapanganAvailableStockTest.php`: Test suite memverifikasi keakuratan perhitungan stok tersedia dengan alokasi aktif, badge Cukup/Kurang, sisa dipesan pada item purchase dengan tautan PO, integritas read-only (stok tidak berubah setelah halaman dilihat), dan bebas N+1 query (query inventori tidak bertambah seiring jumlah item).
- **Hasil Pengujian**: Seluruh 191 test (631 assertions) berstatus HIJAU (PASS).

### T22. Pemohon: Opsi Site Manager / Site Engineering (Metadata Pengajuan)
- **Tujuan**:
  - Menyediakan opsi peran kontekstual pemohon saat mengajukan PO Lapangan (`requested_as`: 'Admin Proyek', 'Site Manager', 'Site Engineering', 'Karyawan').
  - **Keputusan Otonom & Batas**: Opsi "Site Manager" dan "Site Engineering" diimplementasikan murni sebagai **field metadata** pada tabel `material_requests` (`requested_as`), bukan role/permission Spatie baru. Pengguna yang membuat tetap akun pengguna aktif yang login (`requested_by_user_id`).
  - Menambahkan kolom `requested_as` (nullable string, default 'Admin Proyek') dengan migrasi dan backfill aman untuk seluruh data lama.
  - Menambahkan pilihan dropdown "Diajukan Sebagai" pada form pengajuan (`create.blade.php`) dengan nilai default 'Admin Proyek'.
  - Menampilkan metadata pemohon pada halaman detail (`show.blade.php`) dan lembar cetakan (`print.blade.php`) dalam format "Nama Pemohon (Jabatan/Peran Pemohon)".
- **Perubahan File**:
  - `database/migrations/2026_10_12_000005_add_requested_as_to_material_requests_table.php`: Migrasi kolom `requested_as` (default 'Admin Proyek') dan backfill otomatis.
  - `app/Models/MaterialRequest.php`: Menambahkan `requested_as` ke `$fillable`.
  - `app/Services/MaterialRequestService.php`: Penambahan parameter `?string $requestedAs = null` pada `createRequest` dengan fallback default 'Admin Proyek'.
  - `app/Http/Controllers/MaterialRequestController.php`: Validasi `requested_as` pada method `store` dan penerusan ke service.
  - `resources/views/material-requests/create.blade.php`: Dropdown pilihan "Diajukan Sebagai" (Admin Proyek, Site Manager, Site Engineering, Karyawan).
  - `resources/views/material-requests/show.blade.php`: Tampilan nama pemohon disertai peran kontekstual pada sidebar info card.
  - `resources/views/material-requests/print.blade.php`: Tampilan nama pemohon dan jabatan pada header informasi serta label tanda tangan pemohon.
  - `tests/Feature/PoLapanganRequestedAsTest.php`: Test suite memverifikasi penyimpanan `requested_as`, fallback default ke 'Admin Proyek', tampilan di `show` dan `print`, serta kepastian tidak ada role Spatie baru yang dibuat di database.
- **Hasil Pengujian**: Seluruh 194 test (643 assertions) berstatus HIJAU (PASS).

### T23. Fitur Edit PO Lapangan (Status Draft & Submitted)
- **Tujuan**:
  - Menyediakan fitur edit untuk PO Lapangan (Material Request) yang masih dalam status `draft` atau `submitted`.
  - Memblokir pengeditan jika status dokumen sudah `approved`, `rejected`, `partially_fulfilled`, atau `fulfilled` dengan status HTTP 403 / abort.
  - Membatasi hak edit hanya kepada pembuat dokumen (`requested_by_user_id == auth()->id()`) atau Admin Pusat / Owner yang memiliki izin pengelolaan. Pengguna dari gudang/proyek lain ditolak dengan HTTP 403.
  - Validasi ketat saat update: `source_warehouse_id != target_warehouse_id`, `needed_at` divalidasi `after_or_equal:today` hanya jika tanggal diubah (agar PO historis dengan tanggal masa lampau tetap dapat diedit catatannya).
  - Sinkronisasi item cerdas (smart update): mempertahankan item lama yang dipertahankan tanpa delete-all & recreate, memperbarui kuantitas/catatan, membuat baris item baru, dan menghapus item yang dihapus dari form, serta mencegah injeksi `qty_approved`.
  - Antarmuka edit yang ramah pengguna dengan JavaScript dinamis untuk menambah/menghapus baris item, serta tombol edit di halaman `show.blade.php` dan `index.blade.php` yang hanya muncul bila status dan hak akses valid.
- **Perubahan File**:
  - `routes/web.php`: Menambahkan route actions `edit` dan `update` untuk `material-requests`.
  - `app/Http/Controllers/MaterialRequestController.php`: Method `edit` dan `update` dengan otorisasi, validasi, DB transaction, dan logging.
  - `app/Services/MaterialRequestService.php`: Method `updateRequest` dengan `lockForUpdate()`, sinkronisasi item aman, dan kalkulasi ulang.
  - `resources/views/material-requests/edit.blade.php`: Halaman form edit PO Lapangan lengkap dengan dynamic item rows.
  - `resources/views/material-requests/show.blade.php`: Tombol navigasi "Edit PO Lapangan".
  - `resources/views/material-requests/index.blade.php`: Icon aksi edit kondisional per baris.
  - `tests/Feature/PoLapanganEditTest.php`: Test suite pengujian 7 skenario komprehensif.
- **Hasil Pengujian**: Seluruh 201 test (668 assertions) berstatus HIJAU (PASS).

### T7. PO Langsung dari PO Lapangan / MR (Tanpa PR)
- **Tujuan**:
  - Mengizinkan penerbitan Purchase Order (PO) langsung dari PO Lapangan (Material Request) yang telah disetujui (`approved` / `partially_fulfilled`) tanpa harus melalui Procurement Request (PR).
  - Menghapus pembatasan lama di mana PO dari MR hanya boleh untuk item manual/custom (`whereNull('material_id')`). Sekarang seluruh item material terdaftar, item custom, maupun item alat yang memiliki jalur pemenuhan pembelian (`fulfillment == 'purchase'`) dan masih memiliki sisa kuota (`qty_approved - qty_ordered > 0`) dapat diterbitkan menjadi PO langsung ke vendor.
  - Menampilkan tombol aksi "Buat PO ke Vendor" dan info banner pengadaan pada halaman detail MR (`material-requests.show`) hanya bila status dokumen memenuhi syarat (`approved` / `partially_fulfilled` dan memiliki item purchase dengan sisa kuota).
  - Membawa `material_request_item_id` ke form pembuatan PO (`purchase-orders.create`), memuat otomatis sisa kuota pesanan, dan menetapkan `delivery_warehouse_id` secara otomatis ke gudang proyek pemohon (`to_warehouse_id`).
  - Menegakkan integritas sumber pengadaan:
    - **PO wajib memiliki sumber pengadaan yang sah**: Setiap item dalam PO wajib bersumber dari item MR yang telah disetujui atau item PR yang telah disetujui. Pembuatan PO tanpa sumber yang valid ditolak dengan validasi error.
    - Menolak pembuatan PO untuk MR atau PR yang belum disetujui (misalnya status `submitted` atau `draft`).
    - Menolak pemesanan item MR yang dialokasikan untuk pemindahan stok gudang (`fulfillment == 'stock'`).
    - Validasi kuota sisa dihitung ulang dengan penguncian pesimistik `lockForUpdate()` di dalam transaksi DB untuk mencegah race condition.
    - Satu PO tetap terikat ke satu vendor (`supplier_id` / `supplier_name`).
    - Jalur PR tetap dipertahankan untuk kebutuhan restock gudang pusat tanpa MR, dengan pembaruan kuota `qty_ordered` dan kalkulasi status `partially_ordered` / `fully_ordered`.
- **Perubahan File**:
  - `app/Http/Controllers/PurchaseOrderController.php`: Refaktor method `create` (dukung multi-item MR purchase master/alat/custom, redirect jika kuota habis, dropdown gudang tujuan) dan `store` (guard sumber pengadaan sah, guard status approved MR/PR, penetapan otomatis `delivery_warehouse_id`, sinkronisasi `qty_ordered` dengan DB transaction dan lock).
  - `resources/views/material-requests/show.blade.php`: Penambahan tombol "Buat PO ke Vendor" di action header dan banner informasi pengadaan vendor langsung ke proyek.
  - `resources/views/purchase-orders/create.blade.php`: Header gudang tujuan pengiriman (`delivery_warehouse_id`), penanggung risiko (`risk_bearer`), banner informasi pengadaan MR, dan tabel item otomatis dari `mrPurchaseItems`.
  - `tests/Feature/PoDirectFromMrTest.php`: Test suite TDD memverifikasi alur PO langsung dari MR, pembuktian bug awal (merah) dan hasil hijau (8 passed, 25 assertions).
### T8. Approval PO dan Syarat Pengiriman
- **Tujuan**:
  - Menambahkan permission kanonikal Spatie baru `'approve purchase orders'`.
  - Mendaftarkan permission ke `database/seeders/RoleAndPermissionSeeder.php` pada array `$permissions` dan role `Admin PO`, serta grup 'Pengadaan & Pembayaran' di `app/Http/Controllers/RoleController.php`. Role `Admin Pusat` otomatis mendapatkan permission ini.
  - Memperkuat pengamanan action `approve()` pada `PurchaseOrderController`:
    - Otorisasi ketat di server: hanya user dengan permission `'approve purchase orders'` atau role `Admin Pusat`/`Admin` yang dapat menyetujui PO. Pengguna tanpa izin ditolak dengan HTTP 403.
    - Penegakan pemisahan peran (T2): pembuat PO (`created_by === auth()->id()`) dilarang keras menyetujui PO buatannya sendiri, ditolak dengan flash pesan error.
    - Guard status: hanya PO berstatus `draft` yang dapat disetujui; status lain ditolak.
    - Proteksi konkurensi: eksekusi di dalam `DB::transaction` dengan `lockForUpdate()` pada baris PO.
    - Mengisi `approved_by = auth()->id()`, `approved_at = now()`, dan memperbarui status menjadi `approved`.
  - Syarat pengiriman & penanggung risiko (`risk_bearer`):
    - Validasi `risk_bearer` (`vendor` atau `buyer`) di `PurchaseOrderController@store`.
    - Menyimpan nilai `risk_bearer` pada saat pembuatan PO (default: `vendor`).
    - Menambahkan accessor `risk_bearer_label` pada model `PurchaseOrder`.
    - Menampilkan informasi penanggung risiko pengiriman (badge Vendor / Supplier vs Pembeli PT Arsikon), informasi gudang tujuan, estimasi tiba, serta detail pengesahan (nama approver dan tanggal approval) pada halaman detail `purchase-orders.show`.
    - Tombol "Setujui PO" pada antarmuka hanya muncul untuk PO berstatus `draft`, bagi user yang berhak, dan bukan pembuat PO tersebut.
- **Perubahan File**:
  - `database/seeders/RoleAndPermissionSeeder.php`: Penambahan permission `'approve purchase orders'` dan assign ke `Admin PO`.
  - `app/Http/Controllers/RoleController.php`: Pendaftaran permission `'approve purchase orders'` pada modul Pengadaan & Pembayaran.
  - `app/Http/Controllers/PurchaseOrderController.php`: Validasi dan persistensi `risk_bearer` di `store()`, transaksi pesimistik dan guard pada `approve()`.
  - `app/Models/PurchaseOrder.php`: Penambahan accessor `getRiskBearerLabelAttribute()`.
  - `resources/views/purchase-orders/show.blade.php`: Header detail PO menampilkan Gudang Tujuan, Penanggung Risiko Pengiriman, pembuat, dan approver.
  - `tests/Feature/PurchaseOrderApprovalAndRiskTest.php`: Test suite pengujian 5 skenario (creator tolak approve sendiri, non-draft tolak, unauthorized 403, approver sah berhasil approve, persistensi dan tampilan risk_bearer buyer/vendor).
- **Hasil Pengujian**: Seluruh 214 test (713 assertions) berstatus HIJAU (PASS).

### T9. Kunci Harga PO
- **Tujuan**:
  - Mengunci harga satuan (`unit_price`) pada item PO agar tidak dapat diubah setelah PO melewati status `draft`.
  - Guard di level model Eloquent `PurchaseOrderItem` melalui model event `booted()` -> `static::updating()`:
    - Jika atribut `unit_price` mengalami perubahan (`$item->isDirty('unit_price')`) dan status dokumen PO terkait bukan `draft`, sistem langsung melempar `DomainException`.
    - Perubahan field lain (seperti `received_qty`, `lost_qty`, `short_closed_qty`) pada tahap operasional berikutnya tetap diizinkan dan tidak terhalang.
  - Memastikan tidak ada route atau antarmuka yang mengizinkan modifikasi harga satuan PO. Revisi harga wajib dilakukan dengan pembatalan PO dan pembuatan PO baru (approval ulang).
  - Mengamankan alur Goods Receipt (GR):
    - Pada `GoodsReceiptService::create()` dan `receiveFromSupplier()`, untuk item yang bersumber dari PO (`purchase_order_item_id` terisi), `unit_price` SELALU disalin dari `$poItem->unit_price` dan mengabaikan nilai `unit_price` yang dikirim dari klien.
    - Penerimaan langsung tanpa PO tetap diizinkan mengisi harga satuan secara manual.
  - Modul Pembayaran (Payment) tidak mengubah atau menimpa `unit_price` item PO.
- **Perubahan File**:
  - `app/Models/PurchaseOrderItem.php`: Penambahan method `booted()` dengan event listener `updating` untuk mencegah manipulasi `unit_price` di luar status `draft`.
  - `app/Services/GoodsReceiptService.php`: Penyalinan `unit_price` otomatis dari baris PO item terkait pada metode pembuatan GR draft dan penolakan manipulasi nilai harga dari request klien.
  - `tests/Feature/PurchaseOrderPriceLockTest.php`: Test suite pengujian 5 skenario (update harga draft diizinkan, update harga non-draft melempar DomainException, update field non-harga tetap aman, GR menyalin harga PO item dan mengabaikan request klien, GR tanpa PO tetap dapat memakai harga manual).
- **Hasil Pengujian**: Seluruh 219 test (720 assertions) berstatus HIJAU (PASS).

### T10. Short Close PO
- **Tujuan**:
  - Menyediakan fitur Short Close untuk Purchase Order (PO) yang berstatus `sent` atau `partial_received` dan masih memiliki sisa kuota (`outstanding > 0`).
  - Menambahkan permission kanonikal Spatie baru `'close purchase orders'`.
  - Mendaftarkan permission ke `database/seeders/RoleAndPermissionSeeder.php` pada array `$permissions` dan role `Admin PO`, serta grup 'Pengadaan & Pembayaran' di `app/Http/Controllers/RoleController.php`. Role `Admin Pusat` otomatis mendapatkan permission ini.
  - Menambahkan route `POST /purchase-orders/{purchaseOrder}/close` dengan name `purchase-orders.close`.
  - Otorisasi dan guard ketat di server:
    - Hanya user dengan permission `'close purchase orders'` atau role `Admin Pusat`/`Admin` yang berhak melakukan penutupan (status HTTP 403 bila tidak berhak).
    - Penegakan pemisahan peran (T2): Pembuat PO (`created_by === auth()->id()`) dilarang keras melakukan short close pada PO buatannya sendiri, ditolak dengan pesan error.
    - Hanya PO berstatus `sent` atau `partial_received` yang dapat di-short close; status lain ditolak.
    - PO tanpa outstanding ditolak.
    - Input `reason` wajib diisi (alasan penutupan).
  - Mekanisme transaksi dan integritas kuota (`lockForUpdate()`):
    - Untuk setiap item PO yang masih memiliki outstanding (`quantity - received_qty - lost_qty - short_closed_qty > 0`), atribut `short_closed_qty` ditambahkan sebesar sisa outstanding saat itu.
    - Mengurangi `qty_ordered` pada item PO Lapangan / MR terkait (`material_request_items`) sebesar kuota yang ditutup, sehingga sisa kuota yang tidak dapat dipenuhi vendor tersebut kembali tersedia untuk dipesan ke vendor lain.
    - Mengurangi `qty_ordered` pada item PR terkait jika PO bersumber dari restock PR.
    - Membatalkan sisa jadwal kedatangan terencana (`po_delivery_schedules`) jika ada.
    - Mencatat jejak riwayat alasan short close beserta identitas dan waktu penutupan pada atribut `notes` PO.
    - Mengubah status PO menjadi `closed`.
  - Tampilan dan antarmuka (`purchase-orders.show`):
    - Tombol aksi "Short Close" interaktif (dengan input dialog alasan) hanya muncul untuk PO yang memenuhi syarat (status `sent`/`partial_received`, memiliki outstanding, user berwenang, dan bukan pembuat PO).
    - Menampilkan riwayat catatan short close pada header card detail PO.
    - Menampilkan kolom realisasi "Diterima / Ditutup" pada tabel barang PO dengan rincian qty diterima dan badge sisa yang ditutup.
- **Perubahan File**:
  - `database/seeders/RoleAndPermissionSeeder.php`: Pendaftaran permission `'close purchase orders'` dan assign ke `Admin PO`.
  - `app/Http/Controllers/RoleController.php`: Penambahan permission `'close purchase orders'` pada modul Pengadaan & Pembayaran.
  - `routes/web.php`: Route action `close` untuk `purchase-orders`.
  - `app/Models/PurchaseOrderItem.php`: Penambahan accessor `getOutstandingAttribute()`.
  - `app/Models/PurchaseOrder.php`: Penambahan accessor `getOutstandingAttribute()`.
  - `app/Http/Controllers/PurchaseOrderController.php`: Method `close()` dengan validasi, otorisasi, guard pemisahan peran, DB transaction, dan rollback kuota pesanan.
  - `resources/views/purchase-orders/show.blade.php`: Tombol aksi Short Close, tampilan catatan riwayat penutupan, dan kolom realisasi item Diterima / Ditutup.
  - `tests/Feature/PurchaseOrderShortCloseTest.php`: Test suite komprehensif menguji 6 skenario (creator tolak close sendiri, unauthorized 403, status tidak valid ditolak, tanpa outstanding ditolak, validasi wajib alasan, dan proses short close berhasil beserta rollback kuota MR).
- **Hasil Pengujian**: Seluruh 225 test (737 assertions) berstatus HIJAU (PASS).

### Tugas 11: Penerimaan Barang di Gudang Proyek, Penerimaan Sebagian, dan Kondisi Barang
- **Deskripsi**:
  - Implementasi migrasi penambahan tabel `po_delivery_schedules` dan kolom tracking kondisi fisik barang pada `goods_receipt_items` (`qty_shipped`, `qty_damaged`, `qty_missing`, `missing_status`, `po_delivery_schedule_id`).
  - Pembuatan Model `PoDeliverySchedule` dan pembaruan fillable/relasi pada `GoodsReceiptItem`.
  - Service `GoodsReceiptService`:
    - Validasi kuota pesanan PO secara pesimistik (`lockForUpdate`), menolak penerimaan melebihi sisa outstanding PO (`DomainException`).
    - Otomatisasi perhitungan kuantitas fisik: `qty_missing = max(0, qty_shipped - qty_received - qty_damaged)`, dan otomatisasi status `missing_status` ('unresolved').
    - Pengondisian stok gudang: stok gudang bertambah HANYA dari barang berkondisi baik (`qty_received`), sedangkan barang rusak (`qty_damaged`) dicatat tanpa menambah stok gudang maupun `received_qty` PO.
    - Resolusi bertahap selisih barang: penerimaan susulan untuk item PO yang sama secara proporsional menyelesaikan status `unresolved` menjadi `resolved`.
    - Sinkronisasi penerimaan alat (`tool`) ke `purchase_order_items.received_qty` dan isolasi inventaris proyek.
    - Sinkronisasi realisasi penerimaan ke `material_request_items.qty_received`, pembaruan status MR (`partially_fulfilled` atau `fulfilled`), serta notifikasi ke pemohon MR (`NotificationHelper::notifyUser`).
    - Status PO otomatis menjadi `received` hanya jika seluruh item outstanding = 0, atau `partial_received` jika masih ada sisa outstanding.
  - Sanitasi nama supplier di `GoodsReceiptController@store` (`preg_replace('/\s+/', ' ', trim($name))` dan pencarian case-insensitive) serta fallback gudang tujuan otomatis dari PO.
  - Tampilan visual antarmuka rincian item dokumen penerimaan (`goods-receipts/show.blade.php`) menampilkan kolom Qty Kirim, Diterima (Baik), Rusak, dan Selisih/Hilang beserta status resolusinya.
- **Perubahan File**:
  - `database/migrations/2026_10_12_000006_update_goods_receipt_items_and_schedules_schema.php`: Migrasi tabel `po_delivery_schedules` dan kolom kondisi barang pada `goods_receipt_items`.
  - `app/Models/PoDeliverySchedule.php`: Model jadwal pengiriman PO.
  - `app/Models/GoodsReceiptItem.php`: Relasi & fillable baru `qty_shipped`, `qty_damaged`, `qty_missing`, `missing_status`, `po_delivery_schedule_id`.
  - `app/Services/GoodsReceiptService.php`: Logika guard outstanding, tracking kondisi fisik, bugfix alat received_qty, resolusi missing, update status PO/MR.
  - `app/Http/Controllers/GoodsReceiptController.php`: Sanitasi supplier, validasi form, auto-fill gudang tujuan PO.
  - `public/js/goods-receipt-create.js`: Auto-fill gudang saat PO dipilih.
  - `resources/views/goods-receipts/show.blade.php`: Tampilan detail kolom fisik & kondisi barang masuk.
  - `tests/Feature/GoodsReceiptProjectAndConditionTest.php`: Feature test suite mencakup 6 skenario komprehensif.
  - `tests/Feature/GoodsReceiptTest.php`: Penyesuaian assertions test eksisting.
- **Hasil Pengujian**: Seluruh 231 test (754 assertions) berstatus HIJAU (PASS).

### T12. Barang Hilang di Jalan / Transit Loss dari Vendor
- **Tujuan**:
  - Penanganan kehilangan barang dalam perjalanan dari vendor pada penerimaan barang (Goods Receipt item `qty_missing > 0`).
  - Batas waktu (grace period) pelaporan kehilangan maksimal 3 hari kerja (`config('procurement.loss_grace_days', 3)`). Pelaporan melewati grace period wajib oleh Admin Pusat.
  - Pemisahan peran persetujuan kehilangan: approver kehilangan harus berbeda dari pelapor kehilangan (`recorded_by`) DAN petugas penerima barang/pembuat GR (`created_by`, `confirmed_by`, `received_by_user_id`).
  - Persetujuan klaim (`claim_filed`): memperbarui status missing item GR menjadi `confirmed_lost`, menambah `lost_qty` pada Purchase Order Item terkait, mengurangi outstanding PO hingga dapat menyelesaikan status PO menjadi `received`, serta memastikan stok tersedia di gudang proyek tidak bertambah untuk kuantitas yang hilang.
  - Notifikasi dan peringatan dini (alert) otomatis kepada Admin Pengadaan jika vendor mencatat tingkat kehilangan barang melebihi ambang batas (`config('procurement.loss_alert_percent', 3)`%) dalam kurun waktu 30 hari.
- **Perubahan File**:
  - `config/procurement.php`: File konfigurasi ambang batas `loss_grace_days` dan `loss_alert_percent`.
  - `database/seeders/RoleAndPermissionSeeder.php`: Pendaftaran permission `create transit losses` dan `approve transit losses`.
  - `app/Http/Controllers/RoleController.php`: Sinkronisasi modul permissions pada sistem otorisasi peran.
  - `app/Services/GoodsReceiptService.php`: Membuka akses publik `updatePurchaseOrderStatus` untuk sinkronisasi status PO setelah kehilangan disetujui.
  - `app/Services/TransitLossService.php`: Business logic pencatatan kehilangan, validasi grace period, guard pemisahan peran approver, approval klaim, audit log, dan deteksi alert rasio kehilangan vendor.
  - `app/Http/Controllers/TransitLossController.php`: Endpoint handler untuk listing, deklarasi kehilangan, approval kehilangan, dan update status penyelesaian klaim (`recovered` / `written_off`).
  - `routes/web.php`: Rute web `transit-losses.*` terlindungi middleware auth, active-workspace, dan permissions.
  - `resources/views/transit-losses/index.blade.php`: Halaman UI daftar kehilangan transit, filter status, modal update klaim vendor, dan badge peringatan.
  - `resources/views/goods-receipts/show.blade.php`: Tombol dan modal "Nyatakan Hilang" terintegrasi langsung pada item barang GR yang memiliki kuantitas kurang (`qty_missing > 0`).
  - `tests/Feature/TransitLossVendorTest.php`: Feature test suite mencakup 5 skenario komprehensif (otorisasi, bukti & catatan wajib, pembatasan grace period, pemisahan peran approver, dan integrasi flow approval & pembaruan status PO).
- **Hasil Pengujian**: Seluruh 236 test (773 assertions) berstatus HIJAU (PASS).

### T13. Jadwal Kedatangan Pengadaan (po_delivery_schedules Menggantikan incoming_stages)
- **Tujuan**:
  - Menggantikan mekanisme tahapan kedatangan statis `incoming_stages` pada Material dan Alat dengan tabel dinamis relasional `po_delivery_schedules` per item Purchase Order.
  - Per item PO dapat memuat beberapa jadwal kedatangan bertahap (`stage_label`, `planned_date`, `planned_qty`, `warehouse_id`). Total kuantitas jadwal divalidasi tidak boleh melebihi kuantitas yang dipesan. Jika jadwal dikosongkan, sistem secara otomatis menerbitkan satu jadwal penuh sesuai estimasi tiba PO (`expected_delivery`). Jadwal hanya boleh diubah saat status PO adalah `draft`.
  - Endpoint AJAX `getScheduledIncoming` dialihkan sepenuhnya untuk membaca jadwal terbuka (`(planned_qty - received_qty) > 0`) dari `po_delivery_schedules` milik PO berstatus `approved`, `sent`, atau `partial_received`, bukan lagi dari field JSON `incoming_stages`.
  - Konfirmasi penerimaan barang (`GoodsReceiptService@confirm`): meningkatkan `received_qty` pada jadwal dan mengubah status menjadi `partial` atau `received`. Jika pengguna tidak memilih jadwal tertentu saat input, sistem mengalokasikan penerimaan secara otomatis ke jadwal terbuka terdekat berdasarkan urutan tanggal rencana kedatangan.
  - Stok fisik gudang SELALU bertambah murni dari kuantitas diterima baik (`qty_received`) melalui `StockService` / `ToolInventoryService`, tidak pernah dari entri jadwal.
  - Penutupan celah penambahan stok tanpa GR: tahap berstatus `received` pada form master material/alat tidak lagi menambah stok awal. Field `incoming_stages` dibekukan sebagai data historis read-only dan dilindungi agar tidak ditimpa saat update.
  - Scheduled command harian `procurement:check-late-schedules` (didaftarkan pada `app/Console/Kernel.php`): mendeteksi jadwal yang melewati tanggal rencana dan masih memiliki sisa barang belum tiba, lalu mengirim notifikasi peringatan ke Admin PO tepat 1 kali per jadwal menggunakan timestamp `late_notified_at`.
  - Integrasi Dashboard: widget pemantauan kartu metrik "Kedatangan Hari Ini" dan "Jadwal Terlambat" pada dashboard operasional.
- **Perubahan File**:
  - `database/migrations/2026_10_12_000008_add_late_notified_at_to_po_delivery_schedules_table.php`: Migrasi kolom timestamp notifikasi keterlambatan.
  - `app/Models/PoDeliverySchedule.php`: Penambahan accessors (`remaining_qty`, `is_late`), scopes (`open`, `late`, `today`), dan relasi.
  - `app/Models/PurchaseOrderItem.php`: Penambahan relasi `deliverySchedules()` dan method helper `isTool()`.
  - `app/Models/GoodsReceiptItem.php`: Penambahan relasi `deliverySchedule()`.
  - `app/Http/Controllers/PurchaseOrderController.php`: Validasi jadwal bertahap, auto-generate jadwal default, eager load jadwal di show, dan endpoint `updateSchedules` terlindung guard draft.
  - `app/Services/GoodsReceiptService.php`: Penghapusan mutasi `incoming_stages`, penambahan alokasi otomatis dan pembaruan `received_qty` / `status` pada `po_delivery_schedules`.
  - `app/Http/Controllers/GoodsReceiptController.php`: Refaktor `getScheduledIncoming` membaca dari `po_delivery_schedules`.
  - `app/Http/Controllers/MaterialController.php`: Penghapusan auto initial stock dari stages dan perlindungan freeze `incoming_stages` saat update.
  - `app/Http/Controllers/ToolController.php`: Perlindungan freeze `incoming_stages` saat update alat.
  - `app/Console/Commands/CheckLateDeliverySchedules.php`: Artisan command harian pendeteksi jadwal terlambat dan pengirim notifikasi Admin PO satu kali per jadwal.
  - `app/Console/Kernel.php`: Pendaftaran scheduled daily task command `procurement:check-late-schedules`.
  - `app/Http/Controllers/DashboardController.php`: Perhitungan metrik `incomingTodayCount` dan `incomingLateCount`.
  - `resources/views/dashboard.blade.php`: Tampilan kartu KPI "Kedatangan Hari Ini" dan "Jadwal Terlambat".
  - `resources/views/purchase-orders/show.blade.php`: Tampilan rincian jadwal kedatangan per item PO dengan status tuntas/menunggu/terlambat.
  - `routes/web.php`: Pendaftaran rute PUT `purchase-orders.update-schedules`.
  - `tests/Feature/PoDeliveryScheduleTest.php`: Feature test suite komprehensif mencakup 7 skenario (default schedule, validasi kuota jadwal bertahap, pembatasan edit hanya saat draft, AJAX scheduled incoming, auto-alokasi GR, notifikasi keterlambatan satu kali, dan pembekuan incoming_stages).
- **Hasil Pengujian**: Seluruh 243 test (811 assertions) berstatus HIJAU (PASS).

### T14. Alat Dibeli dan Milik Pusat
- **Tujuan**:
  - Memastikan seluruh alat/mesin yang dibeli dan diterima lewat Goods Receipt (GR) di gudang proyek tercatat kepemilikannya sebagai aset kantor pusat: `tools.owner_warehouse_id = gudang pusat` bila masih NULL.
  - Alat yang diterima lewat GR di gudang proyek TIDAK membuat `tool_loans` atau `tool_assignments` (penerimaan pengadaan bukan peminjaman internal). Stok langsung bertambah ke inventaris gudang proyek via `ToolInventoryService::addStock` dan berstatus siap pakai (`stock_available`) dengan label kepemilikan "Milik Pusat".
  - Laporan baru di Laporan Status Alat (`reports/tools.blade.php`): menyajikan tabel khusus "Alat Milik Pusat yang Sedang Berada di Tiap Proyek" dengan kolom: Kode & Nama Alat, Qty, Gudang Proyek, Tanggal Diterima, Kepemilikan (Milik Pusat), dan Rincian Kondisi Stok, lengkap dengan kartu KPI dan filter per gudang proyek.
  - Pemindahan alat kembali ke pusat tetap memakai mekanisme yang sudah ada (Surat Jalan / Distribusi).
  - Validasi ketat kuantitas alat: kuantitas alat (termasuk kuantitas jadwal dan barang rusak/dikirim) WAJIB berupa bilangan bulat (integer) di seluruh modul Permintaan Material (MR), Purchase Order (PO), dan Goods Receipt (GR).
- **Perubahan File**:
  - `database/migrations/2026_10_12_000009_add_owner_warehouse_id_to_tools_table.php`: Migrasi penambahan foreign key `owner_warehouse_id` nullable ke tabel `warehouses`.
  - `app/Models/Tool.php`: Penambahan fillable, cast integer, relasi `ownerWarehouse()`, dan helper method `isOwnedByCentral()`.
  - `app/Services/GoodsReceiptService.php`: Pada saat konfirmasi GR item alat, isi `tools.owner_warehouse_id = gudang pusat` jika masih NULL, dan validasi kuantitas alat (terima, rusak, kirim) wajib bilangan bulat.
  - `app/Http/Controllers/GoodsReceiptController.php`: Validasi bilangan bulat untuk kuantitas alat pada form store GR.
  - `app/Http/Controllers/PurchaseOrderController.php`: Validasi bilangan bulat untuk kuantitas alat dan kuantitas jadwal alat pada form store PO.
  - `app/Services/MaterialRequestService.php`: Penanganan item alat pada MR dengan validasi kuantitas wajib bilangan bulat.
  - `app/Http/Controllers/MaterialRequestController.php`: Validasi bilangan bulat untuk item alat manual/kategori alat.
  - `app/Services/ReportService.php`: Method baru `centralToolsInProjectsReport(?int $warehouseId = null)` menghitung alat milik pusat di gudang proyek beserta tanggal diterima (dari GR/Distribusi).
  - `app/Http/Controllers/ReportController.php`: Method `toolReport` memuat data `centralToolsInProjects` dan filter gudang proyek.
  - `resources/views/reports/tools.blade.php`: Tampilan baru tabel dan metrik "Alat Milik Pusat yang Sedang Berada di Tiap Proyek".
  - `resources/views/tools/index.blade.php`: Tampilan badge "Milik Pusat" pada baris alat milik pusat.
  - `resources/views/tools/show.blade.php`: Tampilan badge "Milik Pusat" pada header detail alat, tabel inventaris per gudang, dan sidebar informasi kepemilikan.
  - `tests/Feature/CentralToolInProjectTest.php`: Feature test suite 6 skenario mencakup kepemilikan alat pusat saat GR, tidak adanya loan/assignment fiktif, data laporan alat pusat di proyek, dan validasi bilangan bulat di PO, GR, serta MR.
- **Hasil Pengujian**: Seluruh 249 test (835 assertions) berstatus HIJAU (PASS).

### T17. Peminjaman Alat Internal (Status Overdue & Pemisahan Peran)
- **Tujuan**:
  - Menegakkan pemisahan peran persetujuan (Separation of Duties) pada peminjaman alat internal (`ToolAssignmentController`): pengguna yang mengajukan peminjaman (`assigned_by_user_id === auth()->id()`) dilarang keras menyetujui (`approve`) atau menolak (`reject`) permohonannya sendiri, meskipun pengguna tersebut memiliki hak akses/permission approval. Permintaan ditolak dengan pesan error.
  - Memastikan otorisasi persetujuan peminjaman hanya dapat dieksekusi oleh pengguna berwenang yang berbeda dari pengaju.
  - Menangani status `overdue` (keterlambatan pengembalian) secara menyeluruh:
    - Artisan scheduled command harian `tools:check-overdue-loans` (didaftarkan di `app/Console/Kernel.php`): mendeteksi peminjaman aktif (`status == 'active'`) yang melewati batas rencana tanggal pengembalian (`expected_return_at < today`), mengubah status `tool_loans` beserta itemnya menjadi `overdue`.
    - Mengirimkan notifikasi peringatan keterlambatan tepat satu kali kepada peminjam (bila berakun), pengaju peminjaman, dan penanggung jawab gudang dengan pencatatan timestamp `overdue_notified_at`.
    - Peminjaman berstatus `overdue` tetap dapat dikembalikan (`return`) melalui form pengembalian dan transisi status secara valid menjadi `returned` (keluar dari status overdue).
    - Peminjaman berstatus `overdue` dapat dibatalkan (`cancel`) dengan alasan pembatalan valid, dan memulihkan stok inventaris alat kembali ke gudang asal (sama seperti pembatalan peminjaman `active`).
    - Metrik harian log proyek (`DailyLogService@compile`): KPI alat aktif (`totalToolsActive`) mencakup peminjaman berstatus `active` dan `overdue` agar operasional tetap memantau alat yang masih berada di lapangan.
- **Perubahan File**:
  - `database/migrations/2026_10_12_000010_add_overdue_notified_at_to_tool_loans_table.php`: Migrasi penambahan kolom timestamp nullable `overdue_notified_at` pada tabel `tool_loans`.
  - `app/Models/ToolLoan.php`: Penambahan `overdue_notified_at` ke `$fillable` dan `$casts`.
  - `app/Http/Controllers/ToolAssignmentController.php`: Guard pemisahan peran pada `approve()` dan `reject()`, penerimaan status `overdue` pada `return()` dan `cancel()`, serta pemulihan stok saat cancel dari status overdue.
  - `app/Console/Commands/CheckOverdueToolLoans.php`: Command harian `tools:check-overdue-loans` untuk deteksi peminjaman overdue dan notifikasi satu kali.
  - `app/Console/Kernel.php`: Pendaftaran `$schedule->command('tools:check-overdue-loans')->daily();`.
  - `app/Services/DailyLogService.php`: Penyesuaian hitungan KPI alat aktif mencakup status `active` dan `overdue`.
  - `tests/Feature/InternalToolLoanOverdueTest.php`: Feature test suite 7 skenario (pemisahan peran approve/reject, approval oleh user berbeda, scheduled command overdue & notifikasi 1x, return dari overdue, cancel dari overdue dengan restorasi stok, dan KPI daily log).
- **Hasil Pengujian**: Seluruh 256 test (860 assertions) berstatus HIJAU (PASS).

### T15. Aturan Nilai yang Boleh Dibayar (Payable Rules & Financial Breakdown)
- **Tujuan**:
  - Menegakkan aturan nominal pembayaran sah untuk Purchase Order:
    `nilai_sah = sum((received_qty + (risk_bearer == 'buyer' ? lost_qty : 0)) * unit_price_PO)`
  - Pembayaran hanya dapat diajukan untuk Purchase Order yang memiliki minimal satu Penerimaan Barang (Goods Receipt / GR) berstatus `confirmed` (bukan sekadar PO berstatus `sent` tanpa kedatangan barang). PO tanpa GR confirmed ditolak di form input dan endpoint penyimpanan.
  - Jumlah komitmen pembayaran (`pending` + `verified`) divalidasi tidak boleh melebihi total `nilai_sah` seluruh item PO.
  - Penanganan penanggung risiko pengiriman:
    - `risk_bearer = 'vendor'`: barang hilang (`lost_qty`) TIDAK menambah nilai sah; selisih ditahan dan tidak ditagihkan ke pembeli.
    - `risk_bearer = 'buyer'`: barang hilang yang sudah berstatus `confirmed_lost` (disetujui klaim kehilangan) menambah nilai sah dan dapat dibayarkan.
  - Selama status kehilangan masih `unresolved` (belum ada GR susulan atau persetujuan kehilangan), nominal tersebut ditahan dan belum dapat dibayar.
  - PO yang berstatus `closed` (short close) tetap dapat dibayarkan hingga batas nilai sah barang yang telah diterima baik sebelum ditutup.
  - Penyajian ringkasan finansial transparan di antarmuka pengguna:
    - Halaman Detail PO (`purchase-orders/show.blade.php`): kartu KPI finansial memuat Total Nilai PO, Nilai Sah Saat Ini, Sudah Dibayar, Pending Verifikasi, Sisa Bisa Dibayar, dan Nilai Tertahan, dilengkapi rincian item tertahan (rusak / missing unresolved / tanggungan vendor / belum tiba) beserta alasannya, serta tabel riwayat transaksi pembayaran PO.
    - Halaman Pengajuan Pembayaran (`payments/create.blade.php`): dropdown PO hanya menampilkan PO yang memiliki minimal satu GR confirmed dan sisa nilai sah > 0; preview finansial komprehensif; input nominal otomatis dibatasi maksimal sebesar sisa nilai sah (`payable_remaining_amount`).
    - Halaman Detail Pembayaran (`payments/show.blade.php`): kartu informasi PO menampilkan progress pembayaran terhadap nilai sah, nilai sah saat ini, sisa bisa dibayar, dan rincian nilai tertahan.
  - Transaksi pesimistik dengan `lockForUpdate()` pada model `PurchaseOrder` saat pengajuan pembayaran untuk mencegah double submit / race condition.
- **Perubahan File**:
  - `app/Models/PurchaseOrderItem.php`: Penambahan relasi `goodsReceiptItems()` dan accessor helper `getNilaiSahAttribute()`.
  - `app/Models/PurchaseOrder.php`: Penambahan method `hasConfirmedGoodsReceipt()`, accessors `getNilaiSahAttribute()`, `getCommittedPaymentAmountAttribute()`, `getPendingPaymentAmountAttribute()`, `getVerifiedPaymentAmountAttribute()`, `getPayableRemainingAmountAttribute()`, dan `getWithheldSummaryAttribute()`.
  - `app/Http/Controllers/PaymentController.php`: Filter PO di `create()` wajib memiliki GR confirmed dan sisa nilai sah > 0; validasi DB transaction pesimistik di `store()` dengan penolakan jika melebihi sisa nilai sah atau tanpa GR confirmed; eager loading relasi di `show()`.
  - `resources/views/purchase-orders/show.blade.php`: Penambahan section kartu ringkasan finansial, nilai sah, nilai tertahan, dan riwayat pembayaran PO.
  - `resources/views/payments/create.blade.php`: Pembaruan filter dropdown, preview status finansial PO berbasis nilai sah, batas input nominal, dan rincian nilai tertahan.
  - `resources/views/payments/show.blade.php`: Pembaruan kartu info PO terkait menampilkan progress terhadap nilai sah, nilai sah, dan rincian nilai tertahan.
  - `tests/Feature/PurchaseOrderPayableRuleTest.php`: Feature test suite 7 skenario komprehensif membuktikan aturan nilai sah dan penahanan nominal.
- **Hasil Pengujian**: Seluruh 263 test (896 assertions) berstatus HIJAU (PASS).

### T16. Seeder Role & Permission serta Command Idempotent
- **Tujuan**:
  - Menyediakan artisan command idempotent `php artisan permissions:sync-procurement` yang dapat dijalankan di production tanpa `migrate:fresh` untuk memastikan seluruh canonical permissions dan role Spatie sinkron dengan aturan pengadaan & logistik PT-Arsikon.
  - Memutakhirkan `RoleAndPermissionSeeder.php` dan `RoleController.php`:
    - **Owner**: View-only / read-only seluruh modul (tidak memiliki izin mutasi atau approval).
    - **Admin Pusat**: Full access ke seluruh sistem.
    - **Admin PO**: Menambahkan izin `approve material requests` (MR / PO Lapangan). Menghapus izin `approve purchase orders`, `close purchase orders`, `approve procurement`, dan `verify payments` (hanya membuat dan mengajukan pembayaran; verifikasi pembayaran serta persetujuan dan penutupan PO/PR adalah wewenang Admin Pusat / Admin).
    - **Admin Gudang Pusat** & **Admin Gudang Proyek**: Izin operasional pergudangan, penerimaan barang (`confirm goods receipts`), pencatatan kehilangan (`create transit losses`), dll.
    - **Karyawan**: Menghapus izin `create distributions` (surat jalan hanya boleh diterbitkan oleh divisi gudang/logistik). Karyawan tetap berhak membuat permintaan material (`create material requests` / PO Lapangan) dan peminjaman alat mandiri.
  - Memperbarui deskripsi metadata `Admin PO` di `RoleController::ROLE_METADATA` menjadi pengajuan pembayaran (bukan verifikasi).
  - Menyelaraskan suite pengujian:
    - Memperbarui `tests/Feature/KaryawanRoleTest.php`: memastikan `test_karyawan_does_not_have_create_distributions_permission` dan penolakan 403 saat Karyawan mengakses `/distributions/create`.
    - Menyelaraskan role user pada `PurchaseOrderApprovalAndRiskTest` dan `PurchaseOrderShortCloseTest` sesuai batasan wewenang baru.
    - Membuat feature test `tests/Feature/ProcurementRolePermissionSyncTest.php` untuk memverifikasi eksekusi artisan command, batasan hak akses tiap role, dan sifat idempotensinya.
- **Perubahan File**:
  - `app/Console/Commands/SyncProcurementPermissions.php`: Artisan command `permissions:sync-procurement`.
  - `database/seeders/RoleAndPermissionSeeder.php`: Sinkronisasi hak akses Spatie untuk Karyawan dan Admin PO.
  - `app/Http/Controllers/RoleController.php`: Pembaruan deskripsi role metadata Admin PO.
  - `tests/Feature/KaryawanRoleTest.php`: Pembaruan assertion Karyawan tidak memiliki izin create distributions.
  - `tests/Feature/PurchaseOrderApprovalAndRiskTest.php` & `tests/Feature/PurchaseOrderShortCloseTest.php`: Penyelarasan role/permission approver/closer PO.
  - `tests/Feature/ProcurementRolePermissionSyncTest.php`: Test suite pengujian artisan command dan kepatuhan Spatie role-permission alur pengadaan.
- **Hasil Pengujian**: Seluruh 269 test (955 assertions) berstatus HIJAU (PASS).

### T24. Susunan Menu Navigasi Sidebar (Grup Teknis, Pengadaan, Logistik)
- **Tujuan**:
  - Menyusun ulang navigasi sidebar utama di bawah Dashboard menjadi 3 grup terstruktur berurutan:
    1. **Teknis**: Pemakaian Material (`material-usages.*`), Retur Material (`returns.*`), Master Alat (`tools.*`), Peminjaman Alat (`tool-assignments.*`), dan Log Harian Proyek (`daily-log.*`).
    2. **Pengadaan**: PO Lapangan (`material-requests.*`), Permintaan Pengadaan (`procurement.*`), Purchase Order (`purchase-orders.*`), Nota Pembelian Harian (`purchase-receipts.*`), Pembayaran (`payments.*`), dan Template Kop Surat (`print-templates.*`).
    3. **Logistik**: Penerimaan Barang (`goods-receipts.*`), Surat Jalan (`distributions.*`), Penerimaan Surat Jalan (`distributions.index?status=shipped`), Inventori (`inventory.*`), Mutasi Stok (`reports.mutation`), Stock Opname (`stock-opnames.*`), dan Master & Stok Minimum (`materials.*`).
    4. **Grup Lanjutan**: Laporan & Audit (`reports.*`, `audit-logs.*`) serta Admin Sistem (`users.*`, `roles.*`, `warehouses.*`, `projects.*`, `supabase-sync.*`) tetap berada di bagian bawah sesuai otorisasi.
  - Menerapkan perlindungan visibilitas ketat:
    - Sembunyikan grup sepenuhnya (`@if`) jika pengguna tidak memiliki izin ke satupun item di dalamnya.
    - Sembunyikan item individual memakai `@can` / rule otorisasi tanpa memperluas hak akses.
  - Menerapkan collapsible interaktif berbasis Alpine.js:
    - Grup yang memuat route aktif terbuka secara otomatis (`open: true`).
    - Dapat di-expand dan di-collapse secara halus dengan klik header section.
- **Perubahan File**:
  - `resources/views/layouts/app.blade.php`: Restrukturisasi navigasi sidebar ke grup Teknis -> Pengadaan -> Logistik dengan Alpine.js state `open`.
  - `tests/Feature/SidebarNavigationTest.php`: Test suite 7 skenario (urutan grup Teknis->Pengadaan->Logistik, link PO Lapangan ke `material-requests.index`, visibilitas per role Admin PO/Admin Proyek/Karyawan/Owner, penyembunyian grup kosong untuk pengguna tanpa izin, serta auto-open grup route aktif).
- **Hasil Pengujian**: Seluruh 276 test (1011 assertions) berstatus HIJAU (PASS).

---

## 2. Asumsi & Keputusan Otonom
1. **Database Test Terpisah**: Dibuat database MySQL `pt_arsikon_test` dan file `.env.testing` agar seluruh pengujian otomatis berjalan independen tanpa menyentuh database operasional/lokal utama (`pt_pbag`).
2. **Kategori dan Master Data**: Master data awal di-seed secara idempotent menggunakan `firstOrCreate` guna mencegah integritas constraint violation saat test suite dijalankan ulang.
3. **Pemisahan Dokumen Counter**: Generator nomor dokumen (`DocumentNumberService`) menggunakan tabel khusus `document_counters` dengan pessimistic locking (`lockForUpdate()`), menginisialisasi nomor awal dari record dokumen tertinggi eksisting jika baris counter belum ada.
4. **Alur Status PO**: Penambahan enum status PO `approved` dan `closed` (short close). Pengiriman ke supplier hanya sah jika PO berstatus `approved`.
5. **Short Close PO**: Penutupan parsial PO (short close) hanya dapat dilakukan pada PO berstatus `sent` atau `partial_received` oleh pengguna selain pembuat dokumen (separation of duties), mengurangi kuota `qty_ordered` pada MR/PR terkait secara proporsional.
6. **Nilai Sah Pembayaran (T15)**: Nilai yang dapat dibayar dihitung berdasarkan kuantitas yang telah diterima secara fisik (GR confirmed) ditambah barang hilang yang disepakati menjadi tanggungan pembeli (`risk_bearer == 'buyer'` dan `status == 'confirmed_lost'`). Seluruh selisih unresolved atau rusak ditahan dari nilai sah.
7. **Penyelarasan Role Spatie (T16)**: Admin PO berhak membuat PO, PR, dan pembayaran serta menyetujui PO Lapangan (MR), namun persetujuan PO/PR dan verifikasi pembayaran dibatasi ketat untuk Admin Pusat / Admin. Karyawan tidak berhak membuat surat jalan distribusi antar gudang.
8. **Struktur Navigasi Sidebar (T24)**: Dashboard tetap menjadi menu tunggal di puncak sidebar. Tiga grup utama di bawahnya berurutan: Teknis -> Pengadaan -> Logistik. Menu Laporan dan Admin Sistem tetap berada di bagian bawah dan hanya dapat diakses oleh role yang berwenang.

---

## 3. Penyimpangan dari Dokumen Rencana & Alasan
1. **Test Karyawan Lama (`test_karyawan_has_create_distributions_permission`)**:
   - Diubah menjadi `test_karyawan_does_not_have_create_distributions_permission` dan akses Karyawan ke route `/distributions/create` diblokir (403). Hal ini merupakan instruksi langsung spesifikasi T16 di mana surat jalan merupakan kewenangan unit logistik/gudang.
2. **Setup Role pada Approval dan Short Close Test**:
   - Pada `PurchaseOrderApprovalAndRiskTest` dan `PurchaseOrderShortCloseTest`, approver dan closer PO diselaraskan dari role `Admin PO` ke `Admin Pusat` / user berizin eksplisit `approve purchase orders` & `close purchase orders`, karena wewenang approval dan short close PO telah dicabut dari role Admin PO sesuai instruksi T16.

---

## 4. Risiko untuk Data Lama & Mitigasi
1. **Risiko Kolom Baru pada Tabel Transaksi Eksisting**:
   - Kolom migrasi baru (`risk_bearer`, `delivery_warehouse_id`, `lost_qty`, `short_closed_qty`, `procurement_request_item_id`, dll.) dibuat dengan nilai default aman atau nullable sehingga record PO, MR, dan PR lama tetap valid dan terbaca normal.
2. **Risiko Backfill Status Dokumen**:
   - PO lama berstatus `sent` tetap dapat diproses GR dan pembayarannya. Perhitungan `payable_remaining_amount` secara dinamis menghitung `nilai_sah` dari GR confirmed yang ada.
3. **Risiko Sinkronisasi Hak Akses (Role/Permission)**:
   - Disediakan artisan command `php artisan permissions:sync-procurement` yang bersifat idempotent (memakai `firstOrCreate` dan `syncPermissions`) sehingga tidak memerlukan `migrate:fresh` atau pembersihan tabel produksi.

---

## 5. Langkah Deploy & Rollback
### Deploy
1. Aktifkan maintenance mode:
   ```bash
   php artisan down
   ```
2. Jalankan migrasi database produksi:
   ```bash
   php artisan migrate --force
   ```
3. Sinkronkan permission dan role alur pengadaan & logistik:
   ```bash
   php artisan permissions:sync-procurement
   ```
4. Bersihkan dan segarkan cache framework:
   ```bash
   php artisan optimize:clear
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```
5. Matikan maintenance mode:
   ```bash
   php artisan up
   ```

### Rollback
1. Jika terjadi anomali kritis, aktifkan maintenance mode:
   ```bash
   php artisan down
   ```
2. Rollback batch migrasi terakhir jika diperlukan:
   ```bash
   php artisan migrate:rollback --step=3
   ```
3. Segarkan cache:
   ```bash
   php artisan optimize:clear
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```
4. Matikan maintenance mode:
   ```bash
   php artisan up
   ```

---

## 6. Hal yang Sengaja Tidak Dikerjakan (Di Luar Lingkup)
- Modul di luar lingkup yang ditegaskan dokumen panduan:
  - Anggaran Biaya / RAB / BoQ.
  - Approval berjenjang dinamis berbasis batasan nominal rupiah tertentu.
  - Perbandingan penawaran vendor / RFQ.
  - PPN dan faktur pajak.
  - Sewa alat berbayar dan perhitungan depresiasi alat berat.
  - Retensi dan opname subkontraktor lapangan.
  - Modul pembuatan tabel invoice terpisah (nomor invoice tetap disimpan sebagai string pada GR).
  - Skrip deployment di folder `deploy/` dan service sinkronisasi cloud `SupabaseSyncService`.

---

## 7. Keputusan Otonom yang Perlu Dikonfirmasi Manusia (T18 - T24)
- (a) **T21**: Arti fitur di sebelah Alokasi diimplementasikan sebagai kolom "Stok Tersedia" (stok gudang sumber dikurangi alokasi aktif + badge Cukup/Kurang, serta sisa dipesan untuk purchase).
- (b) **T22**: Site Manager / Site Engineering diimplementasikan sebagai metadata pemohon (`requested_as`) pada form dan entitas `material_requests`, bukan sebagai permission Spatie baru.
- (c) **T18**: Penamaan tampilan "PO Lapangan" dikhususkan untuk antarmuka Material Request di UI, sementara Purchase Order ke vendor tetap menggunakan label "Purchase Order".
- (d) **T24**: Susunan grup sidebar: Dashboard di puncak, diikuti tiga grup utama: **Teknis** -> **Pengadaan** -> **Logistik**, lalu **Laporan & Audit** dan **Admin Sistem** di posisi bawah.
- (e) **T20**: "Dibutuhkan Tgl" (`needed_at`) dan "Tanggal Order" (`created_at`) diimplementasikan sebagai dua atribut tanggal terpisah di antarmuka.

