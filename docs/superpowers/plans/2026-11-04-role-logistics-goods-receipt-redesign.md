# Role Refinement, Logistics Navigation & Standalone Goods Receipt Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Menata ulang wewenang peran (Admin Proyek/Site Manager fokus teknis, PO Lapangan, inventory, log harian; Gudang Proyek khusus seluruh operasional logistik), memindahkan Surat Jalan ke Logistik, menonaktifkan Retur Material & Stock Opname, serta merombak Penerimaan Barang menjadi input langsung tanpa wajib PO dengan pencocokan cerdas ke master barang dan penambahan tahapan masuk (staged arrival).

**Architecture:** 
- Pemetaan ulang role Spatie Permission: Admin Proyek / Site Manager fokus pada domain teknis (MR/PO Lapangan, Inventory, Daily Log) tanpa beban operasional fisik logistik; Gudang Proyek memiliki akses penuh ke seluruh modul logistik fisik lapangan (Distributions/Surat Jalan, Goods Receipts, Material Usages, Tool Assignments, Inventory) tanpa akses teknis/keuangan.
- Navigasi sidebar (`resources/views/layouts/app.blade.php`) direstrukturisasi: Surat Jalan dipindahkan dari grup Teknis ke grup Logistik, Retur Material dan Stock Opname dihapus dari menu dan routing dinonaktifkan.
- Alur Penerimaan Barang (`GoodsReceipt`): `purchase_order_id` dijadikan opsional penuh. Form input dirancang fleksibel untuk input langsung (nama barang, kategori, spesifikasi, unit, qty, kondisi). Pada saat simpan/konfirmasi, sistem mencocokkan barang ke master `materials` atau `tools`. Jika barang sudah ada, sistem tidak menduplikasi data melainkan mencatat transaksi sebagai entri baru pada `incoming_stages` (tahapan masuk) dan menambah stok fisik gudang; jika barang belum ada, sistem mendaftarkannya otomatis ke master data sekaligus menginisialisasi tahapan masuk dan stok gudang.

**Tech Stack:** Laravel 11, PHP 8.2+, MySQL / SQLite (testing), Blade Templates, Tailwind CSS, Alpine.js, Lucide Icons, Spatie Laravel-Permission.

**Spec:** User request tertanggal 2026-11-04:
1. Mengubah Role:
   - Admin Proyek / Site Manager, Site Engineer, Project Manager: akses teknis, PO Lapangan, inventory, log harian.
   - Gudang Proyek: akses HANYA logistik semuanya.
   - Surat Jalan dipindah ke grup Logistik.
   - Hapus Retur Material dan Stock Opname.
2. Perubahan pada Penerimaan Barang:
   - Penerimaan barang tidak terhubung pada PO, menjadi inputan barang langsung seperti inputan pada material/alat.
   - Menjadi tahapan masuk baru jika barang yang diinputkan ternyata sudah ada di database.

## Global Constraints

- Standar penamaan permission menggunakan format spasi "kata kerja + objek" (contoh: `view distributions`, `create goods receipts`).
- Konsistensi skema database: seluruh kolom tetap selaras dengan 6 file domain migration master; tidak membuat file alter migration baru tanpa alasan mendesak.
- Seluruh pencocokan nama barang dilakukan secara *case-insensitive* dan *trimmed* untuk mencegah duplikasi (contoh: "Semen Gresik 50kg" cocok dengan "semen gresik 50kg").
- Perhitungan stok gudang (`inventories`) dan status kedatangan (`incoming_stages`) harus selalu sinkron saat barang diterima dalam kondisi baik (`qty_received`).
- Pengujian otomatis (PHPUnit / Pest) harus mencakup izin akses role, pemindahan menu, serta logika pencocokan barang dan tahapan masuk barang baru maupun barang eksisting.

## Review Focus

- User dengan role Admin Proyek/Site Manager tidak boleh melihat tombol aksi atau menu penerimaan fisik logistik (Goods Receipts / Tool Assignments), dan sebaliknya Gudang Proyek tidak boleh dapat membuat/mengedit PO Lapangan.
- Penerimaan barang tanpa PO tidak boleh menghasilkan error foreign key constraint pada database (`purchase_order_id` nullable).
- Barang eksisting yang diinputkan berulang kali tidak boleh membuat duplikat baris di tabel `materials` atau `tools`.
- Riwayat `incoming_stages` pada material/alat harus memuat nomor bukti penerimaan (`receipt_number`), tanggal, gudang, kuantitas, dan status `received`.
- Navigasi sidebar tidak boleh menyisakan link broken ke route retur material atau stock opname.

---

### Task 1: Role & Permission Alignment for Technical Roles and Warehouse Project Role

**Files:**
- Modify: `database/seeders/RoleAndPermissionSeeder.php`
- Create: `tests/Feature/RolePermissionsRefinementTest.php`

**Interfaces:**
- Consumes: Spatie Permission models `Role` & `Permission`, User models.
- Produces: Definisi hak akses spesifik untuk `Admin Proyek` (Teknis, PO Lapangan, Inventory, Log Harian) dan `Admin Gudang Proyek` (Hanya Logistik: Surat Jalan, Penerimaan Barang, Pemakaian Material, Peminjaman Alat, Inventory, Material, Alat).

- [ ] **Step 1: Write the failing test**

```php
// tests/Feature/RolePermissionsRefinementTest.php
namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RolePermissionsRefinementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
    }

    public function test_admin_proyek_has_access_to_technical_po_lapangan_inventory_and_daily_log_only(): void
    {
        $role = Role::findByName('Admin Proyek');

        // Boleh: PO Lapangan (material requests), inventory, daily log (reports)
        $this->assertTrue($role->hasPermissionTo('view material requests'));
        $this->assertTrue($role->hasPermissionTo('create material requests'));
        $this->assertTrue($role->hasPermissionTo('view inventory'));
        $this->assertTrue($role->hasPermissionTo('view reports'));

        // Tidak Boleh: Logistik fisik operasional lapangan
        $this->assertFalse($role->hasPermissionTo('create goods receipts'));
        $this->assertFalse($role->hasPermissionTo('create tool assignments'));
        $this->assertFalse($role->hasPermissionTo('create material usages'));
        $this->assertFalse($role->hasPermissionTo('create distributions'));
    }

    public function test_gudang_proyek_has_access_strictly_to_logistics_only(): void
    {
        $role = Role::findByName('Admin Gudang Proyek');

        // Boleh: Surat Jalan (distributions), Penerimaan (goods receipts), Peminjaman alat, Pemakaian material, Inventory
        $this->assertTrue($role->hasPermissionTo('view distributions'));
        $this->assertTrue($role->hasPermissionTo('create distributions'));
        $this->assertTrue($role->hasPermissionTo('view goods receipts'));
        $this->assertTrue($role->hasPermissionTo('create goods receipts'));
        $this->assertTrue($role->hasPermissionTo('view tool assignments'));
        $this->assertTrue($role->hasPermissionTo('view material usages'));
        $this->assertTrue($role->hasPermissionTo('view inventory'));

        // Tidak Boleh: PO Lapangan teknis & Modul Retur / Stock Opname yang dihapus
        $this->assertFalse($role->hasPermissionTo('create material requests'));
        $this->assertFalse($role->hasPermissionTo('view returns'));
        $this->assertFalse($role->hasPermissionTo('view stock opname'));
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test tests/Feature/RolePermissionsRefinementTest.php`
Expected: FAIL due to missing permissions or legacy permissions still mapped.

- [ ] **Step 3: Update permissions mapping in `database/seeders/RoleAndPermissionSeeder.php`**

- Atur permissions untuk `Admin Proyek`:
  - Sertakan: `view material requests`, `create material requests`, `edit material requests`, `delete material requests`, `view inventory`, `view reports`.
  - Hapus/lepaskan: permissions `goods receipts`, `tool assignments`, `material usages`, `distributions`, `returns`, `stock opname`.
- Atur permissions untuk `Admin Gudang Proyek`:
  - Sertakan: `view distributions`, `create distributions`, `ship distributions`, `receive distributions`, `view goods receipts`, `create goods receipts`, `confirm goods receipts`, `view tool assignments`, `create tool assignments`, `return tool assignments`, `approve tool assignments`, `cancel tool assignments`, `inspect return tool assignments`, `view material usages`, `create material usages`, `cancel material usages`, `view inventory`, `view materials`, `create materials`, `edit materials`, `view tools`, `create tools`, `edit tools`, `view categories`, `view suppliers`.
  - Hapus/lepaskan: `view material requests`, `create material requests`, `view returns`, `create returns`, `approve returns`, `receive returns`, `view stock opname`, `create stock opname`.
- Buat alias role jika diperlukan: `Site Manager`, `Site Engineer`, `Project Manager` mewarisi permission yang sama dengan `Admin Proyek`.

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test tests/Feature/RolePermissionsRefinementTest.php`
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add database/seeders/RoleAndPermissionSeeder.php tests/Feature/RolePermissionsRefinementTest.php
git commit -m "feat(roles): align Admin Proyek to technical/PO lapangan and Gudang Proyek strictly to logistics"
```

---

### Task 2: Navigation Restructuring & Decommissioning Retur Material & Stock Opname

**Files:**
- Modify: `resources/views/layouts/app.blade.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/NavigationAndDecommissionModuleTest.php`

**Interfaces:**
- Consumes: Blade template layout, Laravel Route Collection.
- Produces: Sidebar navigasi dengan Surat Jalan berada di bawah grup Logistik, Retur Material dan Stock Opname dihapus dari menu, dan route nonaktif/terproteksi.

- [ ] **Step 1: Write the failing test**

```php
// tests/Feature/NavigationAndDecommissionModuleTest.php
namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavigationAndDecommissionModuleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
    }

    public function test_sidebar_renders_surat_jalan_under_logistik_and_removes_retur_and_stock_opname(): void
    {
        $gudangProyekUser = User::factory()->create(['email_verified_at' => now()]);
        $gudangProyekUser->assignRole('Admin Gudang Proyek');

        $response = $this->actingAs($gudangProyekUser)->get(route('dashboard'));

        $response->assertOk();
        // Surat Jalan harus tampil untuk user logistik gudang proyek
        $response->assertSee(route('distributions.index'));
        $response->assertSee('Surat Jalan');

        // Retur Material dan Stock Opname tidak boleh tampil di sidebar
        $response->assertDontSee('Retur Material');
        $response->assertDontSee('Stock Opname');
    }

    public function test_returns_and_stock_opname_routes_are_decommissioned_or_forbidden(): void
    {
        $gudangProyekUser = User::factory()->create(['email_verified_at' => now()]);
        $gudangProyekUser->assignRole('Admin Gudang Proyek');

        $resReturn = $this->actingAs($gudangProyekUser)->get('/returns');
        $this->assertTrue(in_array($resReturn->status(), [403, 404]));

        $resStockOpname = $this->actingAs($gudangProyekUser)->get('/stock-opnames');
        $this->assertTrue(in_array($resStockOpname->status(), [403, 404]));
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test tests/Feature/NavigationAndDecommissionModuleTest.php`
Expected: FAIL because Surat Jalan is still in TEKNIS, and Retur & Stock Opname are still in the sidebar and routes.

- [ ] **Step 3: Modify `resources/views/layouts/app.blade.php` and `routes/web.php`**

- Di `resources/views/layouts/app.blade.php`:
  - Pindahkan link `route('distributions.index')` dari bagian `<!-- 1. GRUP TEKNIS -->` ke dalam bagian `<!-- 3. GRUP LOGISTIK -->`.
  - Di grup TEKNIS, hanya tampilkan `PO Lapangan` (`material-requests.index`) dan `Log Harian Proyek` (`daily-log.index`).
  - Di grup LOGISTIK, tampilkan: `Surat Jalan`, `Penerimaan Barang`, `Pemakaian Material`, `Peminjaman Alat`, `Inventori`, `Material`, `Alat`.
  - Hapus tag menu `<a href="{{ route('returns.index') }}">` dan `<a href="{{ route('stock-opnames.index') }}">`.
- Di `routes/web.php`:
  - Berikan middleware pembatasan atau proteksi `can:view returns` / `can:view stock opname` (yang sudah dicabut) atau nonaktifkan resource route tersebut sehingga menghasilkan HTTP 403 / 404 bagi role operasional.

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test tests/Feature/NavigationAndDecommissionModuleTest.php`
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add resources/views/layouts/app.blade.php routes/web.php tests/Feature/NavigationAndDecommissionModuleTest.php
git commit -m "refactor(nav): move Surat Jalan to Logistik, decommission Retur Material and Stock Opname"
```

---

### Task 3: Backend Standalone Goods Receipt Service & Automated Incoming Stage Matching

**Files:**
- Modify: `app/Services/GoodsReceiptService.php`
- Modify: `app/Models/Material.php`
- Modify: `app/Models/Tool.php`
- Modify: `app/Models/GoodsReceipt.php`
- Create: `tests/Feature/StandaloneGoodsReceiptServiceTest.php`

**Interfaces:**
- Consumes: `GoodsReceiptService::create()`, `GoodsReceiptService::confirm()`, `Material::autoRegisterCustom()`, `Tool::autoRegisterCustom()`.
- Produces: Kemampuan membuat penerimaan barang tanpa PO (`purchase_order_id` null), mendeteksi barang eksisting secara case-insensitive/SKU, mencatat transaksi sebagai riwayat `incoming_stages` baru, dan meng-update stok `inventories`.

- [ ] **Step 1: Write the failing test**

```php
// tests/Feature/StandaloneGoodsReceiptServiceTest.php
namespace Tests\Feature;

use App\Models\Category;
use App\Models\GoodsReceipt;
use App\Models\Inventory;
use App\Models\Material;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\GoodsReceiptService;
use App\Services\StockService;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StandaloneGoodsReceiptServiceTest extends TestCase
{
    use RefreshDatabase;

    protected GoodsReceiptService $service;
    protected User $gudangUser;
    protected Warehouse $warehouse;
    protected Supplier $supplier;
    protected Category $category;
    protected Unit $unit;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        $this->service = app(GoodsReceiptService::class);

        $this->warehouse = Warehouse::firstOrCreate(['code' => 'W-TEST-SITE'], ['name' => 'Gudang Site Proyek Test', 'is_central' => false]);
        $this->supplier = Supplier::firstOrCreate(['name' => 'Supplier Semen Mandiri']);
        $this->category = Category::firstOrCreate(['name' => 'STRUKTUR'], ['type' => 'material']);
        $this->unit = Unit::firstOrCreate(['code' => 'SAK'], ['name' => 'Sak']);

        $this->gudangUser = User::factory()->create(['email_verified_at' => now()]);
        $this->gudangUser->assignRole('Admin Gudang Proyek');
        $this->gudangUser->warehouses()->attach($this->warehouse->id);
    }

    public function test_goods_receipt_without_po_appends_incoming_stage_and_increases_stock_for_existing_material(): void
    {
        // 1. Buat material eksisting
        $existingMaterial = Material::create([
            'name' => 'Semen Tiga Roda 50kg',
            'sku' => 'MAT-STR-001',
            'category_id' => $this->category->id,
            'unit_id' => $this->unit->id,
            'incoming_stages' => [],
        ]);

        // 2. Buat Goods Receipt langsung tanpa PO
        $payload = [
            'purchase_order_id'   => null,
            'material_request_id' => null,
            'supplier_id'         => $this->supplier->id,
            'supplier_name'       => $this->supplier->name,
            'warehouse_id'        => $this->warehouse->id,
            'received_at'         => now()->toDateString(),
            'invoice_number'      => 'SJ-VENDOR-999',
            'notes'               => 'Pengiriman material semen langsung dari pabrik',
            'items' => [
                [
                    'item_type'     => 'material',
                    'material_id'   => $existingMaterial->id,
                    'name'          => 'Semen Tiga Roda 50kg',
                    'category_id'   => $this->category->id,
                    'qty_shipped'   => 100,
                    'qty_received'  => 100,
                    'condition'     => 'good',
                    'notes'         => 'Cor lantai 1',
                ]
            ]
        ];

        $gr = $this->service->create($payload, $this->gudangUser->id);
        $this->assertNull($gr->purchase_order_id);
        $this->assertEquals('draft', $gr->status);

        // 3. Konfirmasi GR
        $confirmedGr = $this->service->confirm($gr, $this->gudangUser->id);
        $this->assertEquals('confirmed', $confirmedGr->status);

        // 4. Periksa stok bertambah di gudang tujuan
        $inv = Inventory::where('warehouse_id', $this->warehouse->id)
            ->where('material_id', $existingMaterial->id)
            ->first();
        $this->assertNotNull($inv);
        $this->assertEquals(100, (float) $inv->quantity);

        // 5. Periksa incoming_stages tercatat di material
        $existingMaterial->refresh();
        $stages = $existingMaterial->incoming_stages ?? [];
        $this->assertNotEmpty($stages);
        $lastStage = end($stages);
        $this->assertEquals($gr->receipt_number, $lastStage['receipt_number'] ?? null);
        $this->assertEquals(100, (float) ($lastStage['qty'] ?? 0));
        $this->assertEquals('received', $lastStage['status'] ?? null);
    }

    public function test_goods_receipt_without_po_auto_registers_new_material_when_not_exists(): void
    {
        $payload = [
            'purchase_order_id'   => null,
            'supplier_id'         => $this->supplier->id,
            'warehouse_id'        => $this->warehouse->id,
            'received_at'         => now()->toDateString(),
            'invoice_number'      => 'SJ-BARU-001',
            'items' => [
                [
                    'item_type'     => 'material',
                    'material_id'   => null,
                    'name'          => 'Bata Ringan Hebel 10cm',
                    'category_id'   => $this->category->id,
                    'custom_type'   => 'Hebel SNI',
                    'custom_unit'   => 'm3',
                    'qty_shipped'   => 25,
                    'qty_received'  => 25,
                    'condition'     => 'good',
                ]
            ]
        ];

        $gr = $this->service->create($payload, $this->gudangUser->id);
        $this->service->confirm($gr, $this->gudangUser->id);

        $newMaterial = Material::whereRaw('LOWER(name) = ?', ['bata ringan hebel 10cm'])->first();
        $this->assertNotNull($newMaterial);
        $this->assertEquals(25, (float) $newMaterial->inventories->first()->quantity);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test tests/Feature/StandaloneGoodsReceiptServiceTest.php`
Expected: FAIL due to missing handling for appending incoming stages or standalone GR items.

- [ ] **Step 3: Implement standalone GR processing and incoming stage appending in `GoodsReceiptService.php`**

- Di `GoodsReceiptService::create()`:
  - Terima `purchase_order_id` nullable tanpa syarat.
  - Untuk setiap item:
    - Jika `material_id` belum diisi, periksa apakah ada `Material` eksisting dengan nama yang sama (case-insensitive & trimmed). Jika ada, kaitkan ke `material_id` tersebut.
    - Jika tidak ada, panggil `Material::autoRegisterCustom()` untuk mendaftarkan master baru.
    - Lakukan hal serupa untuk item alat (`tools`).
- Di `GoodsReceiptService::confirm()`:
  - Setelah `StockService::addStock()` berhasil, perbarui array `incoming_stages` pada `Material` / `Tool` terkait:
    ```php
    $stages = $material->incoming_stages ?? [];
    $stages[] = [
        'stage_name'     => "Penerimaan #{$receipt->receipt_number}",
        'receipt_number' => $receipt->receipt_number,
        'invoice_number' => $receipt->invoice_number,
        'supplier_name'  => $receipt->supplier_name ?: $receipt->supplier?->name,
        'warehouse_id'   => $warehouse->id,
        'warehouse_name' => $warehouse->name,
        'qty'            => (float) $item->qty_received,
        'date'           => $receiptDateStr,
        'status'         => 'received',
        'condition'      => $item->condition,
        'notes'          => $item->notes,
        'created_at'     => now()->toISOString(),
    ];
    $material->update(['incoming_stages' => $stages]);
    ```
  - Hilangkan dependensi wajib PO pada saat update status dokumen.

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test tests/Feature/StandaloneGoodsReceiptServiceTest.php`
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add app/Services/GoodsReceiptService.php app/Models/Material.php app/Models/Tool.php tests/Feature/StandaloneGoodsReceiptServiceTest.php
git commit -m "feat(goods-receipts): support standalone receipts with automated incoming stages tracking"
```

---

### Task 4: GoodsReceiptController Refactor for Standalone Direct Item Entry

**Files:**
- Modify: `app/Http/Controllers/GoodsReceiptController.php`
- Modify: `routes/web.php`
- Create: `tests/Feature/GoodsReceiptStandaloneControllerTest.php`

**Interfaces:**
- Consumes: `GoodsReceiptController@create`, `GoodsReceiptController@store`, `GoodsReceiptController@confirm`.
- Produces: Endpoint web yang melayani form penerimaan barang mandiri, validasi item dinamis tanpa PO, dan redirect yang aman ke show view.

- [ ] **Step 1: Write the failing test**

```php
// tests/Feature/GoodsReceiptStandaloneControllerTest.php
namespace Tests\Feature;

use App\Models\Category;
use App\Models\GoodsReceipt;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GoodsReceiptStandaloneControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $gudangUser;
    protected Warehouse $warehouse;
    protected Supplier $supplier;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);

        $this->warehouse = Warehouse::firstOrCreate(['code' => 'W-PRJ-001'], ['name' => 'Gudang Site FK Teknik', 'is_central' => false]);
        $this->supplier = Supplier::firstOrCreate(['name' => 'TB Sumber Bangunan']);
        $this->category = Category::firstOrCreate(['name' => 'ARSITEK'], ['type' => 'material']);

        $this->gudangUser = User::factory()->create(['email_verified_at' => now()]);
        $this->gudangUser->assignRole('Admin Gudang Proyek');
        $this->gudangUser->warehouses()->attach($this->warehouse->id);
    }

    public function test_can_store_and_confirm_goods_receipt_directly_without_po(): void
    {
        $response = $this->actingAs($this->gudangUser)->post(route('goods-receipts.store'), [
            'warehouse_id'    => $this->warehouse->id,
            'supplier_id'     => $this->supplier->id,
            'received_at'     => now()->toDateString(),
            'invoice_number'  => 'DO-VENDOR-404',
            'notes'           => 'Penerimaan keramik lantai langsung',
            'items' => [
                [
                    'item_type'     => 'material',
                    'name'          => 'Keramik Granit 60x60 Putih',
                    'category_id'   => $this->category->id,
                    'custom_unit'   => 'dus',
                    'qty_received'  => 50,
                    'condition'     => 'good',
                ]
            ]
        ]);

        $response->assertSessionHasNoErrors();
        $gr = GoodsReceipt::latest('id')->first();
        $this->assertNotNull($gr);
        $this->assertEquals('DO-VENDOR-404', $gr->invoice_number);
        $this->assertNull($gr->purchase_order_id);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test tests/Feature/GoodsReceiptStandaloneControllerTest.php`
Expected: FAIL due to validation requiring PO or PO item IDs.

- [ ] **Step 3: Update `GoodsReceiptController.php`**

- Pada `create(Request $request)`:
  - Muat daftar kategori material & alat, master material & alat untuk fitur autocomplete/pencarian cepat di form.
  - Sederhanakan data yang dikirim ke view tanpa mewajibkan parameter `po_id` atau query PO yang memberatkan.
- Pada `store(Request $request)`:
  - Perbarui aturan validasi:
    - `'purchase_order_id' => 'nullable|exists:purchase_orders,id'`
    - `'warehouse_id' => 'required|exists:warehouses,id'`
    - `'supplier_id' => 'required|exists:suppliers,id'`
    - `'items' => 'required|array|min:1'`
    - `'items.*.name' => 'required|string|max:255'`
    - `'items.*.qty_received' => 'required|numeric|min:0.01'`
  - Tangani pendaftaran barang baru atau pengaitan ke barang eksisting secara otomatis sebelum memanggil `service->create()` dan `service->confirm()`.

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test tests/Feature/GoodsReceiptStandaloneControllerTest.php`
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add app/Http/Controllers/GoodsReceiptController.php tests/Feature/GoodsReceiptStandaloneControllerTest.php
git commit -m "feat(goods-receipts): refactor controller to handle standalone item entry"
```

---

### Task 5: Frontend Redesign of Goods Receipt Form & Staged Arrival Display

**Files:**
- Modify: `resources/views/goods-receipts/create.blade.php`
- Modify: `resources/views/goods-receipts/edit.blade.php`
- Modify: `resources/views/goods-receipts/show.blade.php`
- Modify: `resources/views/goods-receipts/print.blade.php`
- Test: `tests/Feature/GoodsReceiptStandaloneControllerTest.php`

**Interfaces:**
- Consumes: Alpine.js / vanilla JS reactive item table, Lucide icons, Blade form fields.
- Produces: Form penerimaan barang langsung yang bersih dan modern tanpa dropdown PO wajib, dilengkapi pencarian barang eksisting, tombol tambah baris, serta tampilan tahapan masuk di detail & lembar cetak.

- [ ] **Step 1: Write UI assertions in test**

```php
// Tambahkan di tests/Feature/GoodsReceiptStandaloneControllerTest.php
public function test_goods_receipt_create_view_renders_direct_item_inputs_without_mandatory_po_select(): void
{
    $response = $this->actingAs($this->gudangUser)->get(route('goods-receipts.create'));
    $response->assertOk();
    $response->assertSee('Penerimaan Barang Langsung');
    $response->assertSee('Tambah Baris Barang');
    $response->assertDontSee('Pilih Purchase Order (Wajib)');
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test tests/Feature/GoodsReceiptStandaloneControllerTest.php --filter=test_goods_receipt_create_view_renders_direct_item_inputs_without_mandatory_po_select`
Expected: FAIL

- [ ] **Step 3: Redesign `resources/views/goods-receipts/create.blade.php` & supporting views**

- Buat layout form:
  - Header: Tanggal Terima, Gudang Tujuan (default gudang aktif), Nama Supplier/Vendor (bisa pilih atau ketik langsung), Nomor Surat Jalan Vendor, Upload Foto Bukti Pengiriman, Catatan.
  - Tabel Barang Diterima:
    - Kolom: Jenis (Material / Alat), Nama Barang (input teks dengan datalist/autocomplete daftar barang yang sudah ada), Kategori, Spesifikasi / Tipe, Satuan, Qty Diterima, Kondisi (Baik/Rusak), Catatan, Aksi (Hapus).
    - Tombol `+ Tambah Baris Barang` dengan JavaScript untuk menambah baris secara dinamis.
  - Indikator Status: Jika nama barang cocok dengan barang yang sudah ada, beri badge kecil "Tersambung ke Master: [Nama] &bull; Dicatat sebagai Tahapan Masuk".
- Perbarui `show.blade.php` & `print.blade.php`:
  - Hilangkan label PO jika penerimaan berstatus *Non-PO / Direct Receipt*.
  - Tampilkan Nomor Surat Jalan Vendor dan rincian kuantitas secara rapi.

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test tests/Feature/GoodsReceiptStandaloneControllerTest.php`
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add resources/views/goods-receipts/create.blade.php resources/views/goods-receipts/edit.blade.php resources/views/goods-receipts/show.blade.php resources/views/goods-receipts/print.blade.php tests/Feature/GoodsReceiptStandaloneControllerTest.php
git commit -m "feat(goods-receipts-ui): redesign direct item entry form and receipt views"
```

---

### Task 6: Comprehensive Regression Testing & End-to-End Verification

**Files:**
- Test: `tests/Feature/RolePermissionsRefinementTest.php`
- Test: `tests/Feature/NavigationAndDecommissionModuleTest.php`
- Test: `tests/Feature/StandaloneGoodsReceiptServiceTest.php`
- Test: `tests/Feature/GoodsReceiptStandaloneControllerTest.php`
- Test: `tests/Feature/GoodsReceiptTest.php`

**Interfaces:**
- Consumes: Seluruh test suites di modul Role, Logistik, Goods Receipt, Material, dan Tool.
- Produces: Laporan verifikasi bebas regresi dan sistem siap rilis.

- [ ] **Step 1: Run all new feature tests**

Run: `php artisan test --filter="RolePermissionsRefinementTest|NavigationAndDecommissionModuleTest|StandaloneGoodsReceiptServiceTest|GoodsReceiptStandaloneControllerTest"`
Expected: All tests PASS.

- [ ] **Step 2: Run existing regression tests**

Run: `php artisan test --filter="GoodsReceipt|MaterialRequest|PurchaseOrder|Inventory"`
Expected: All core tests PASS.

- [ ] **Step 3: Clear and refresh view & config caches**

Run: `php artisan view:clear; php artisan config:clear; php artisan route:clear`
Expected: Clean cache output.

- [ ] **Step 4: Final Git Status Verification & Clean Commit**

Run: `git status`
Expected: Clean working tree.

