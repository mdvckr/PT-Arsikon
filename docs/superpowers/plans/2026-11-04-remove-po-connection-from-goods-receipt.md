# Redesign Rincian Penerimaan Barang (Direct Material/Tool Entry Scoped to Own Inventory) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Redesign the *"Rincian Barang & Alat Diterima"* section on the Goods Receipt form (`create.blade.php` and `edit.blade.php`) to function like Create Material/Alat (direct editable inputs for Name, Category, Unit, Quantity, Brand/Specs, and Notes without PO target/selisih columns), with auto-complete references strictly scoped to the user's own warehouse inventory rather than leaking other roles'/projects' items.

**Architecture:**
- **Inventory-Scoped References:** In `GoodsReceiptController::create` and `edit`, filter master `$materials` and `$tools` so they only contain items currently registered in the user's accessible warehouse inventory (`whereHas('inventories', fn($q) => $q->whereIn('warehouse_id', $accessibleWarehouseIds))`).
- **Create Material/Alat Style Inputs:** In the items table:
  - Remove obsolete PO columns: `Qty Target (PO)` and `Selisih` (and their discrepancy calculator JS).
  - Provide direct editable fields in every row:
    1. **Tipe**: Material / Alat (badge toggle).
    2. **Nama Barang \***: Input text with datalist/autocomplete from the user's own warehouse inventory. If an existing item is selected, auto-fill Category, Unit, and SKU. If a new item is typed, allow free typing.
    3. **Kategori \***: Dropdown select containing all material/tool categories.
    4. **Satuan \***: Dropdown or datalist select containing standard units (`Zak`, `Batang`, `M3`, `Kg`, `Lembar`, `Unit`, `Pcs`, `Roll`, `Dus`, etc.).
    5. **Qty Diterima (Stok) \***: Number input (minimum > 0).
    6. **Spesifikasi / Merek**: Input text (e.g. `Holcim 50kg`, `D13mm x 12m`).
    7. **Catatan**: Input text for condition/notes.
    8. **Aksi**: Delete button.
- **Form Header (Data Sesi Penerimaan):** Remove `Hubungkan PO Lapangan` and `Hubungkan Purchase Order (PO)` fields from Column 2; move `No. Surat Jalan / Nota Vendor` into Column 2 so all 3 columns are balanced.
- **Stock & Pipeline Compatibility:** Maintain the automatic matching and stock increment pipeline:
  - If received item name matches existing master in destination warehouse: update `incoming_stages` status `received` and increment stock.
  - If received item is brand new: auto-register new `Material` or `Tool` with the given category, unit, and specification, initialize stock and `incoming_stages`.

**Tech Stack:** Laravel 11, Blade, Tailwind CSS, Alpine.js, Vanilla JS, PHPUnit / Pest.

**Spec:** User requests:
1. *"hubungkan PO itu hapus tidak perlu soalnya saat penerimaan baranag mereka harus mengecek nota sendiri yg di print olah gudang"*
2. *"dibagian Rincian Barang & Alat Diterima buatkan seperti creat material/alat dan jika ada referensi mungkin dari invontory akunya sendiri bukan dari inventori role akun yg berbeda"*
3. Uploaded image showing current table with `QTY TARGET (PO)`, `SELISIH`, and rigid `-- Pilih Material --` select.

## Global Constraints
- Database columns `purchase_order_id` and `material_request_id` remain nullable on `goods_receipts` table (backward compatibility for historical records).
- Access control: Project warehouse staff must only see materials and tools from their own assigned project warehouse(s). Central warehouse staff see central warehouse items.
- All existing tests in `tests/Feature/GoodsReceiptTest.php` and `tests/Feature/GoodsReceiptStandaloneControllerTest.php` must pass.

---

### Task 1: Scope Material & Tool Reference Data to User's Own Inventory
**Files:**
- Modify: `app/Http/Controllers/GoodsReceiptController.php:60-125`
- Test: `tests/Feature/GoodsReceiptStandaloneControllerTest.php`

- [ ] **Step 1: Write test verifying reference items are scoped to user's warehouse**
  In `tests/Feature/GoodsReceiptStandaloneControllerTest.php`, add test `test_goods_receipt_create_only_references_items_from_users_own_warehouse()`:
  - Create Warehouse A (assigned to User A) and Warehouse B (assigned to User B).
  - Create Material A with inventory in Warehouse A.
  - Create Material B with inventory in Warehouse B.
  - Authenticate as User A and visit `route('goods-receipts.create')`.
  - Assert `$materials` passed to view contains Material A and DOES NOT contain Material B.

- [ ] **Step 2: Run test to observe failure**
  Run: `php artisan test --filter="test_goods_receipt_create_only_references_items_from_users_own_warehouse"`

- [ ] **Step 3: Update `GoodsReceiptController::create` and `edit`**
  - Get `$accessibleWarehouseIds = $warehouses->pluck('id');`.
  - Filter `$materials` query:
    ```php
    $materials = Material::whereHas('inventories', function ($q) use ($accessibleWarehouseIds) {
        $q->whereIn('warehouse_id', $accessibleWarehouseIds);
    })->with(['unit', 'category', 'supplier'])->orderBy('name')->get();
    ```
  - Filter `$tools` query:
    ```php
    $tools = Tool::where(function ($tq) use ($accessibleWarehouseIds) {
        $tq->whereIn('current_warehouse_id', $accessibleWarehouseIds)
           ->orWhereHas('inventories', fn($iq) => $iq->whereIn('warehouse_id', $accessibleWarehouseIds));
    })->where('is_active', true)->with(['category'])->orderBy('name')->get();
    ```
  - If user is super admin/owner without specific warehouse restrictions, fall back to all active items.

- [ ] **Step 4: Run test to verify PASS**
  Run: `php artisan test --filter="test_goods_receipt_create_only_references_items_from_users_own_warehouse"`

- [ ] **Step 5: Commit changes**
  Commit: `git commit -m "feat(goods-receipt): scope material and tool references to user warehouse inventory"`

---

### Task 2: Redesign "Rincian Barang & Alat Diterima" Table & Row Structure
**Files:**
- Modify: `resources/views/goods-receipts/create.blade.php:1165-1255`
- Modify: `resources/views/goods-receipts/edit.blade.php`
- Modify: `public/js/goods-receipt-create.js:185-450`
- Test: `tests/Feature/GoodsReceiptStandaloneControllerTest.php`

- [ ] **Step 1: Write test for new table column headers and removal of PO columns**
  In `tests/Feature/GoodsReceiptStandaloneControllerTest.php`:
  - `assertDontSee('Qty Target (PO)')`
  - `assertDontSee('Selisih')`
  - `assertSee('Jumlah Diterima (Stok)')`
  - `assertSee('Kategori')`
  - `assertSee('Satuan')`

- [ ] **Step 2: Update Blade table headers in `create.blade.php` and `edit.blade.php`**
  Replace old 9 columns with clean material/tool input columns:
  - `Tipe` (width: 80px)
  - `Nama Barang *` (min-width: 200px)
  - `Kategori *` (width: 170px)
  - `Satuan *` (width: 110px)
  - `Jumlah Diterima (Stok) *` (width: 130px)
  - `Spesifikasi / Merek` (width: 150px)
  - `Catatan` (min-width: 130px)
  - `Aksi` (width: 45px)

- [ ] **Step 3: Redesign `addRow()` in `public/js/goods-receipt-create.js`**
  - Implement direct editable inputs matching Create Material / Tool:
    - **Nama Barang**: Input text with HTML `<datalist>` populated from `$materialsJson` or `$toolsJson`.
      When user selects from datalist or enters name:
      - If matches existing item: auto-fill category, unit, specification, and set hidden `material_id`/`tool_id`.
      - If new item: user freely selects category from dropdown, chooses unit, enters specs.
    - **Kategori**: Dropdown `<select name="items[idx][category_id]">` populated with `$materialCategories` or `$toolCategories`.
    - **Satuan**: `<select name="items[idx][unit]">` or `<input list="unit_datalist">` containing standard units (`Zak`, `Batang`, `M3`, `Kg`, `Unit`, `Pcs`, `Dus`, `Roll`, `Meter`, `Liter`, etc.).
    - **Jumlah Diterima (Stok) \***: Number input `<input type="number" name="items[idx][quantity]" min="0.01" step="any" required>`.
    - **Spesifikasi / Merek**: Input `<input type="text" name="items[idx][brand]" placeholder="mis. Holcim 50kg / D13">`.
    - **Catatan**: Input `<input type="text" name="items[idx][notes]" placeholder="Kondisi barang...">`.
  - Remove all `qty_shipped`, `qty_missing`, `missing_action`, and PO discrepancy JS recalculations.
  - On `DOMContentLoaded`: if `itemsBody` is empty, auto-call `addRow('material')` so 1 fresh row is ready immediately.

- [ ] **Step 4: Update `create.blade.php` header card**
  - Remove `Hubungkan PO Lapangan` and `Hubungkan Purchase Order (PO)`.
  - Move `invoice_number` (`No. Surat Jalan / Nota Vendor`) to Column 2.
  - Remove "Atau Impor PO" buttons from empty state.

- [ ] **Step 5: Run tests and verify PASS**
  Run: `php artisan test --filter="GoodsReceiptStandaloneControllerTest"`

- [ ] **Step 6: Commit changes**
  Commit: `git commit -m "feat(goods-receipt): redesign items table like create material and remove po columns"`

---

### Task 3: Controller Store & Update Normalization & Regression Verification
**Files:**
- Modify: `app/Http/Controllers/GoodsReceiptController.php:570-650`
- Test: `tests/Feature/GoodsReceiptTest.php`
- Test: `tests/Feature/StandaloneGoodsReceiptServiceTest.php`
- Test: `tests/Feature/GoodsReceiptStandaloneControllerTest.php`

- [ ] **Step 1: Verify `GoodsReceiptController@store` handles the new row format**
  - Ensure `name` / `custom_item_name`, `category_id`, `unit`, `brand`, and `quantity` are properly passed to `GoodsReceiptService`.
  - Ensure items already in master are detected by name and their stock incremented without duplicate creation.
  - Ensure new items are auto-registered with their chosen category and unit.

- [ ] **Step 2: Run all Goods Receipt tests**
  Run: `php artisan test --filter="GoodsReceipt"`
  Verify that all feature and unit tests pass.

- [ ] **Step 3: Clear view and route caches**
  Run: `php artisan view:clear; php artisan config:clear; php artisan route:clear`

- [ ] **Step 4: Commit changes**
  Commit: `git commit -m "chore(goods-receipt): finalize direct material style goods receipt with scoped inventory"`
