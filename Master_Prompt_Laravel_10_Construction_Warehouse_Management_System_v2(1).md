# MASTER PROMPT
# CONSTRUCTION WAREHOUSE MANAGEMENT SYSTEM
## Laravel 10

> **CATATAN REVISI**
> Versi ini menambahkan/menyempurnakan bagian yang sebelumnya kurang lengkap pada draft awal:
> - Field lengkap untuk `Category`, `Unit`, `Supplier`, `Role`, `Permission`, `ToolAssignment`, `ToolReturn`, `Maintenance`, `StockOpname`.
> - Flow **Stock Opname** (sebelumnya hanya disebut, belum ada model/flow).
> - Penanganan **partial shipment & partial receive** (qty kirim/terima tidak selalu sama dengan qty request).
> - Aturan **concurrency & row locking** (`lockForUpdate`) untuk stock.
> - Struktur teknis **Role & Permission** (pilihan package + alasan, sesuai aturan Section 1).
> - **Permission Matrix** dalam bentuk tabel (bukan hanya naratif).
> - Bagian **Konfigurasi Environment (.env)**.
> - Bagian **Coding Standard & Konvensi Penamaan**.
> - Bagian **Git Workflow**.
> - Detail **Phase 16 (Deployment)** yang sebelumnya kosong.
> - Daftar **Edge Case eksplisit** sebagai checklist tambahan untuk Phase 0.
> Semua penomoran section asli (1–54) dipertahankan agar referensi lama tetap valid; tambahan
> disisipkan sebagai section **53A–53J**, tepat sebelum Section 54 (START), supaya urutan
> "aturan agent → mulai eksekusi" tetap terakhir.

Anda adalah **Senior Laravel 10 Architect, Backend Developer, Frontend Developer, UI/UX Designer, Database Engineer, dan QA Engineer**.

Bangun sistem **Construction Warehouse Management System** menggunakan **Laravel 10** dari NOL hingga siap digunakan.

Jangan langsung membuat seluruh sistem sekaligus.

Kerjakan secara bertahap berdasarkan fase yang ditentukan di bawah ini.

Setiap fase harus dapat dijalankan dan diuji sebelum melanjutkan ke fase berikutnya.

---

# 1. STACK TEKNOLOGI

Gunakan:

```text
Laravel 10
PHP 8.1+
MySQL / MariaDB
Blade
Tailwind CSS
Alpine.js
Laravel Breeze
Eloquent ORM
Laravel Migration
Laravel Seeder
Laravel Factory
Laravel Policies
Laravel Gates
Laravel Storage
Laravel Validation
```

Gunakan struktur Laravel standar dan clean code.

Hindari dependency tambahan kecuali memang diperlukan.

Jika ingin menambahkan package eksternal, jelaskan:

- nama package
- alasan penggunaan
- manfaat
- alternatif tanpa package

Jangan memasang package secara otomatis tanpa alasan.

---

# 2. KONSEP BISNIS UTAMA

Sistem memiliki:

```text
1 Gudang Pusat
+
banyak Gudang Proyek
```

Gudang Pusat merupakan **pusat supply perusahaan**.

Gudang Proyek mendapatkan material dan alat dari Gudang Pusat.

Gudang Proyek **tidak melakukan pengadaan langsung**.

Alur utama:

```text
GUDANG PROYEK
      │
      ▼
   REQUEST
      │
      ▼
GUDANG PUSAT
      │
      ▼
   PROCESS
      │
      ▼
 DISTRIBUTION
      │
      ▼
GUDANG PROYEK
```

---

# 3. AKTOR

Gunakan 3 role utama:

```text
OWNER
ADMIN
USER
```

## OWNER

Owner adalah pengawas seluruh sistem.

Owner dapat:

- melihat semua gudang
- melihat seluruh proyek
- melihat inventory
- melihat stok
- melihat request
- melihat distribusi
- melihat alat
- melihat laporan
- melihat audit log
- mengelola user
- mengelola proyek
- mengelola gudang

Owner fokus pada monitoring.

---

# 4. ADMIN

Admin adalah pengelola Gudang Pusat.

Admin dapat:

- mengelola material
- mengelola alat
- menerima barang dari supplier
- mengelola stock pusat
- memproses request proyek
- approve request
- reject request
- menyiapkan barang
- membuat distribusi
- mengirim barang
- menerima pengembalian alat
- melakukan inspeksi alat
- maintenance alat
- stock opname
- melihat laporan

---

# 5. USER

User merupakan pengguna Gudang Proyek.

User hanya dapat mengakses:

```text
PROJECT
WAREHOUSE
```

yang diberikan kepada user tersebut.

User dapat:

- melihat stok proyek
- melihat material
- melihat alat
- membuat request
- melihat request miliknya
- menerima distribusi
- mengajukan pengembalian alat
- melihat histori proyek

User tidak boleh mengubah stock secara manual.

---

# 6. AUTHENTICATION

Gunakan Laravel Breeze untuk authentication.

Gunakan:

```text
email
password
```

Gunakan Laravel authentication standard.

Setelah login:

```text
LOGIN
 ↓
AUTHENTICATED USER
 ↓
ROLE
 ↓
ASSIGNED WAREHOUSE / PROJECT
 ↓
WORKSPACE
 ↓
DASHBOARD
```

---

# 7. DASHBOARD DINAMIS

Jangan membuat dashboard berdasarkan email atau username.

Jangan:

```php
if ($user->email == '...')
```

Jangan:

```php
if ($user->name == '...')
```

Gunakan:

```text
role
permissions
warehouse assignment
project assignment
workspace
```

Routing:

```text
OWNER
→ /owner/dashboard

ADMIN
→ /central/dashboard

USER
→ /project/dashboard
```

Tetapi jika satu user memiliki lebih dari satu workspace:

```text
LOGIN
 ↓
SELECT WORKSPACE
 ↓
DASHBOARD
```

Contoh:

```text
User:
Budi

Role:
USER

Access:
Gudang Proyek A
Gudang Proyek B
```

Setelah login:

```text
Pilih Workspace

Gudang Proyek A
[Masuk]

Gudang Proyek B
[Masuk]
```

Simpan workspace aktif pada session.

Contoh:

```php
session([
    'active_warehouse_id' => $warehouseId,
    'active_project_id' => $projectId,
]);
```

Jangan mempercayai ID yang dikirim browser tanpa melakukan authorization.

---

# 8. AUTHORIZATION

Gunakan:

```text
Policies
Gates
Middleware
```

Jangan hanya mengandalkan frontend.

Contoh middleware:

```text
role:owner
role:admin
role:user
```

Buat juga authorization berdasarkan:

```text
warehouse assignment
project assignment
```

User hanya boleh melihat data workspace yang dimilikinya.

---

# 9. INVENTORY

Inventory hanya memiliki:

```text
Material
Alat
```

Jangan membuat kategori:

```text
Barang
```

---

# 10. MATERIAL

Material adalah item yang digunakan dan biasanya berkurang.

Contoh:

```text
Semen
Besi
Pasir
Pipa
Cat
Keramik
Kabel
```

Model:

```text
Material / Item
```

Gunakan field:

```text
id
code
name
category_id
unit_id
minimum_stock
description
status
created_at
updated_at
```

---

# 10A. MASTER DATA PENDUKUNG (CATEGORY, UNIT, SUPPLIER)

Model-model ini disebut di ERD (Section 26) tetapi field-nya wajib didefinisikan agar tidak ambigu saat migration dibuat.

## Category

```text
id
name
type          -- enum: MATERIAL | TOOL
description
status
created_at
updated_at
```

Category dipisahkan berdasarkan `type` agar dropdown Material dan Alat tidak tercampur.

## Unit

```text
id
name          -- contoh: Sak, Kg, Meter, Batang, Unit, Pcs
symbol        -- contoh: sak, kg, m, btg, unit, pcs
created_at
updated_at
```

## Supplier

```text
id
code
name
contact_person
phone
email
address
status
created_at
updated_at
```

Supplier hanya digunakan pada modul **Goods Receipt** (Section 24). Supplier tidak memiliki akses login ke sistem.

---

# 11. ALAT

Alat digunakan berkali-kali dan dapat dipindahkan atau dipinjam.

Contoh:

```text
Bor
Gerinda
Mesin Las
Concrete Mixer
Vibrator
Tangga
```

Jika menggunakan tracking individual:

```text
tool_code
serial_number
condition
status
current_warehouse_id
current_project_id
```

Status:

```text
AVAILABLE
DISTRIBUTED
IN_USE
RETURNED
MAINTENANCE
DAMAGED
LOST
```

---

# 12. WAREHOUSE

Model:

```text
Warehouse
```

Field:

```text
id
code
name
type
project_id
address
status
created_at
updated_at
```

Type:

```text
CENTRAL
PROJECT
```

Aturan:

```text
CENTRAL
=
Gudang Pusat

PROJECT
=
Gudang Proyek
```

Gudang proyek harus terhubung dengan project.

---

# 13. PROJECT

Model:

```text
Project
```

Field:

```text
id
code
name
location
start_date
end_date
status
description
created_at
updated_at
```

Relationship:

```text
Project
 └── Warehouse
```

---

# 14. USER ASSIGNMENT

Buat relasi:

```text
users
warehouses
projects
```

User proyek hanya dapat melihat warehouse/project yang ditugaskan.

Contoh:

```text
user_warehouse
```

Bila diperlukan:

```text
user_project
```

Pastikan authorization memeriksa assignment.

---

# 15. STOCK

Stock harus berbasis:

```text
item
warehouse
quantity
```

Contoh:

```text
Semen
Gudang Pusat
1000

Semen
Gudang Proyek A
200
```

---

# 16. STOCK MOVEMENT

Jangan mengubah angka stock tanpa histori.

Buat:

```text
stock_movements
```

Minimal:

```text
id
item_id
from_warehouse_id
to_warehouse_id
quantity
transaction_type
reference_type
reference_id
user_id
notes
created_at
```

Contoh:

```text
Gudang Pusat
Semen -100

Gudang Proyek A
Semen +100
```

---

# 17. TRANSACTION TYPES

Gunakan enum/status yang jelas:

```text
RECEIPT
TRANSFER
ISSUE
RETURN
ADJUSTMENT
```

Jangan membuat perubahan stock tanpa transaction type.

---

# 18. STOCK NEGATIVE

Secara default:

```text
STOCK < 0
```

tidak diperbolehkan.

Sebelum stock keluar:

```php
if ($stock->quantity < $requestedQuantity) {
    // reject
}
```

Gunakan database transaction:

```php
DB::transaction(...)
```

untuk perubahan stock.

---

# 19. REQUEST

Request adalah inti sistem.

Model:

```text
Request
RequestItem
```

Request dibuat oleh Gudang Proyek.

Request menuju Gudang Pusat.

Field:

```text
request_number
requester_id
project_id
warehouse_id
request_date
needed_date
status
notes
```

Request item:

```text
item_id
quantity
unit_id
notes
```

---

# 20. REQUEST STATUS

Gunakan:

```text
DRAFT
SUBMITTED
REVIEW
APPROVED
REJECTED
PREPARING
READY_TO_SHIP
SHIPPED
RECEIVED
COMPLETED
```

Flow:

```text
DRAFT
 ↓
SUBMITTED
 ↓
REVIEW
 ↓
APPROVED
 ↓
PREPARING
 ↓
READY_TO_SHIP
 ↓
SHIPPED
 ↓
RECEIVED
 ↓
COMPLETED
```

Reject:

```text
REVIEW
 ↓
REJECTED
```

---

# 21. REQUEST MATERIAL

Flow:

```text
PROJECT WAREHOUSE
 ↓
CREATE REQUEST
 ↓
SUBMIT
 ↓
CENTRAL ADMIN
 ↓
APPROVE
 ↓
PREPARE
 ↓
SHIP
 ↓
PROJECT RECEIVE
 ↓
PROJECT STOCK +
```

Central stock:

```text
STOCK CENTRAL -
```

Project stock:

```text
STOCK PROJECT +
```

Semua transaksi harus dicatat.

---

# 22. REQUEST ALAT

Flow:

```text
PROJECT
 ↓
REQUEST TOOL
 ↓
CENTRAL REVIEW
 ↓
APPROVE
 ↓
PREPARE
 ↓
DISTRIBUTE
 ↓
PROJECT
 ↓
ASSIGN TOOL
```

Jika alat individual:

```text
Tool #ALT-001
Tool #ALT-002
```

harus dapat dilacak.

---

# 23. TOOL RETURN

Flow:

```text
PROJECT
 ↓
RETURN REQUEST
 ↓
SHIP TO CENTRAL
 ↓
CENTRAL RECEIVE
 ↓
INSPECTION
 ↓
CONDITION
```

Condition:

```text
GOOD
MINOR_DAMAGE
MAJOR_DAMAGE
LOST
```

Result:

```text
GOOD
→ AVAILABLE

MINOR_DAMAGE
→ MAINTENANCE

MAJOR_DAMAGE
→ REPAIR

LOST
→ LOST
```

---

# 23A. FIELD MODEL ALAT (DETAIL)

Field berikut sebelumnya hanya disebut sebagai nama model di ERD (Section 26), sekarang didefinisikan lengkap.

## ToolAssignment

Mencatat penugasan/peminjaman alat individual ke sebuah proyek.

```text
id
tool_id
distribution_id        -- nullable, relasi ke Distribution jika berasal dari distribusi
project_id
warehouse_id
assigned_by
assigned_date
expected_return_date
actual_return_date     -- nullable, diisi saat return selesai
status                  -- ASSIGNED | RETURNED | OVERDUE
notes
created_at
updated_at
```

## ToolReturn

Mencatat proses pengembalian alat dari proyek ke Gudang Pusat, termasuk hasil inspeksi.

```text
id
tool_assignment_id
tool_id
returned_by
received_by            -- nullable sampai diterima Admin Pusat
condition               -- GOOD | MINOR_DAMAGE | MAJOR_DAMAGE | LOST
notes
shipped_at
received_at
created_at
updated_at
```

## Maintenance

```text
id
tool_id
type                    -- PREVENTIVE | CORRECTIVE
description
cost
start_date
end_date                -- nullable selama masih berjalan
status                   -- SCHEDULED | IN_PROGRESS | COMPLETED
performed_by
created_at
updated_at
```

Tool yang sedang `MAINTENANCE` **tidak boleh** muncul di daftar alat yang bisa di-assign/distribusikan.

---

# 24. GOODS RECEIPT

Barang dari supplier hanya diterima di Gudang Pusat.

Flow:

```text
SUPPLIER
 ↓
GOODS RECEIPT
 ↓
CHECK
 ↓
RECEIVE
 ↓
CENTRAL STOCK +
```

Model:

```text
GoodsReceipt
GoodsReceiptItem
```

---

# 25. DISTRIBUTION

Buat model:

```text
Distribution
DistributionItem
```

Distribution:

```text
central warehouse
→
project warehouse
```

Field:

```text
distribution_number
request_id
from_warehouse_id
to_warehouse_id
status
shipping_date
received_date
```

---

# 25A. PARTIAL SHIPMENT & PARTIAL RECEIVE

Master prompt awal mengasumsikan qty yang di-request, dikirim, dan diterima selalu sama persis. Di lapangan hal ini sering tidak terjadi (stock pusat tidak cukup, atau barang rusak saat pengecekan penerimaan), sehingga wajib ditangani eksplisit.

Tambahkan field berikut pada `DistributionItem`:

```text
requested_quantity
shipped_quantity
received_quantity
```

Aturan:

```text
shipped_quantity  <= requested_quantity
received_quantity <= shipped_quantity
```

Jika `received_quantity < shipped_quantity`:

```text
selisih dicatat sebagai discrepancy
Request TIDAK otomatis COMPLETED
Admin/Owner mendapat notifikasi discrepancy
```

Status Request/Distribution baru untuk mengakomodasi ini:

```text
PARTIALLY_SHIPPED
PARTIALLY_RECEIVED
```

Sisa quantity yang belum terpenuhi dapat:

```text
dibuatkan Distribution baru (lanjutan), atau
ditutup manual oleh Admin dengan alasan (notes wajib diisi)
```

---

# 25B. STOCK OPNAME

Disebut sebagai tugas Admin di Section 4, namun belum punya model/flow. Berikut definisinya.

Model:

```text
StockOpname
StockOpnameItem
```

Field `StockOpname`:

```text
id
opname_number
warehouse_id
opname_date
status          -- DRAFT | REVIEW | APPROVED
created_by
approved_by
notes
```

Field `StockOpnameItem`:

```text
id
stock_opname_id
item_id
system_quantity   -- quantity menurut sistem saat opname dibuat
physical_quantity -- quantity hasil hitung fisik, diinput user
variance          -- physical_quantity - system_quantity (computed)
notes
```

Flow:

```text
CREATE OPNAME (DRAFT)
 ↓
INPUT PHYSICAL COUNT per item
 ↓
SUBMIT (REVIEW)
 ↓
APPROVE
 ↓
SISTEM MEMBUAT stock_movement (transaction_type = ADJUSTMENT)
UNTUK SETIAP item DENGAN variance != 0
 ↓
STOCK WAREHOUSE = physical_quantity
```

Aturan:

```text
Hanya OWNER atau ADMIN yang boleh APPROVE stock opname.
Stock TIDAK berubah sebelum status APPROVED.
Setiap ADJUSTMENT wajib tercatat di stock_movements dan Audit Log.
```

---

# 25C. CONCURRENCY & ROW LOCKING

Master prompt awal sudah mewajibkan `DB::transaction()` (Section 18, 28) tetapi belum menegaskan proteksi terhadap **race condition** ketika dua proses mengubah stock item+warehouse yang sama secara bersamaan (misal dua request disetujui hampir bersamaan).

Wajib:

```php
DB::transaction(function () use ($itemId, $warehouseId, $qty) {
    $stock = Stock::where('item_id', $itemId)
        ->where('warehouse_id', $warehouseId)
        ->lockForUpdate()
        ->firstOrFail();

    if ($stock->quantity < $qty) {
        throw new InsufficientStockException();
    }

    $stock->decrement('quantity', $qty);

    StockMovement::create([...]);
});
```

Aturan:

```text
Setiap operasi yang MENGURANGI atau MENAMBAH stock wajib menggunakan lockForUpdate()
di dalam DB::transaction().
Validasi stock cukup/tidak cukup dilakukan SETELAH lock diperoleh, bukan sebelumnya.
```

---

# 26. DATABASE

Sebelum coding final, buat ERD.

Minimal model:

```text
User
Role
Permission

Project
Warehouse

Category
Unit

Item
Stock
StockMovement

Supplier

GoodsReceipt
GoodsReceiptItem

Request
RequestItem

Distribution
DistributionItem

ToolAssignment
ToolReturn
Maintenance

StockOpname
StockOpnameItem

Notification

AuditLog
Attachment
```

Field lengkap setiap model baru (Category, Unit, Supplier, ToolAssignment, ToolReturn,
Maintenance, StockOpname, StockOpnameItem) sudah didefinisikan pada Section 10A, 23A,
dan 25B. Struktur Role & Permission dijelaskan pada Section 55.

Gunakan Eloquent Relationships.

Contoh:

```php
User::hasMany(...)
Project::hasOne(...)
Warehouse::belongsTo(...)
Stock::belongsTo(...)
Request::hasMany(...)
Distribution::belongsTo(...)
```

---

# 27. LARAVEL STRUCTURE

Gunakan struktur:

```text
app/
├── Http/
│   ├── Controllers/
│   ├── Middleware/
│   ├── Requests/
│   └── Resources/
│
├── Models/
│
├── Policies/
│
├── Services/
│
├── Actions/
│
├── Enums/
│
└── Support/
```

Gunakan Service/Action class untuk business logic kompleks.

Jangan meletakkan seluruh business logic di Controller.

Contoh:

```text
RequestController
       ↓
RequestService
       ↓
StockService
       ↓
Database
```

---

# 28. DATABASE TRANSACTION

Gunakan:

```php
DB::transaction()
```

untuk proses:

- stock receipt
- distribution
- stock transfer
- request approval yang mengubah stock
- receiving
- tool return

Pastikan tidak terjadi partial update.

---

# 29. FORM REQUEST VALIDATION

Gunakan Laravel Form Request:

```text
StoreMaterialRequest
UpdateMaterialRequest

StoreRequestRequest
ApproveRequestRequest

StoreDistributionRequest
ReceiveDistributionRequest

ReturnToolRequest
```

Jangan melakukan validasi kompleks hanya di Blade.

---

# 30. UI

Gunakan:

```text
Blade
Tailwind CSS
Alpine.js
```

Buat reusable component:

```text
components/
├── button
├── modal
├── badge
├── table
├── input
├── select
├── alert
├── card
├── dropdown
├── pagination
└── empty-state
```

---

# 31. OWNER DASHBOARD

Owner melihat:

```text
Total Inventory
Material
Alat
Gudang
Proyek
Pending Request
Distribution
Tool Borrowed
Low Stock
Maintenance
```

Tambahkan:

```text
Inventory by Warehouse
Request by Project
Material Usage
Tool Distribution
Stock Movement
```

---

# 32. CENTRAL DASHBOARD

Admin melihat:

```text
Central Stock
Pending Request
Approved Request
Preparing
Ready to Ship
Shipping
Returned Tools
Low Stock
Maintenance
```

Quick actions:

```text
Barang Masuk
Review Request
Create Distribution
Receive Return
Stock Opname
```

---

# 33. PROJECT DASHBOARD

User proyek melihat:

```text
Project Stock
Material
Tools

Pending Request
Approved Request
Preparing
Shipped
Received

Borrowed Tools
Overdue Tools
```

Quick actions:

```text
Create Request
Receive Distribution
Return Tool
```

---

# 34. SIDEBAR OWNER

```text
Dashboard

Inventory
├── Material
├── Alat
└── Stock

Gudang
├── Gudang Pusat
└── Gudang Proyek

Proyek

Request

Distribusi

Laporan

User

Audit Log

Settings
```

---

# 35. SIDEBAR ADMIN

```text
Dashboard

Inventory
├── Material
├── Alat
└── Stock Pusat

Transaksi
├── Barang Masuk
├── Distribusi
└── Pengembalian Alat

Request

Gudang Proyek

Proyek

Laporan

Audit Log
```

---

# 36. SIDEBAR USER PROYEK

```text
Dashboard

Inventory
├── Material
└── Alat

Request
├── Buat Request
└── Request Saya

Transaksi
├── Penerimaan
└── Pengembalian Alat

Laporan
```

---

# 37. AUDIT LOG

Gunakan model:

```text
AuditLog
```

Simpan:

```text
user_id
action
module
reference_type
reference_id
old_values
new_values
ip_address
user_agent
created_at
```

Audit wajib untuk aktivitas:

```text
CREATE
UPDATE
DELETE
APPROVE
REJECT
RECEIVE
SHIP
TRANSFER
RETURN
ADJUST
LOGIN
LOGOUT
```

---

# 38. NOTIFICATION

Buat notification system Laravel.

Notifikasi:

```text
Request baru
Request approved
Request rejected
Ready to ship
Shipped
Received
Low stock
Tool due
Tool overdue
Maintenance
```

Gunakan Laravel Notification.

---

# 39. ROUTING

Pisahkan route berdasarkan role.

Contoh:

```php
Route::middleware(['auth', 'role:owner'])
    ->prefix('owner')
    ->group(...);

Route::middleware(['auth', 'role:admin'])
    ->prefix('central')
    ->group(...);

Route::middleware(['auth', 'role:user'])
    ->prefix('project')
    ->group(...);
```

Tetap lakukan authorization pada Policy.

---

# 40. RESOURCE CONTEXT

Pastikan URL tidak memungkinkan user mengakses warehouse/project lain.

Contoh buruk:

```text
/project/warehouse/999
```

tanpa authorization.

Gunakan:

```text
Policy
Scope
Assignment
```

untuk memastikan user memiliki akses.

---

# 41. ROUTE MODEL BINDING

Gunakan Laravel Route Model Binding.

Contoh:

```php
Route::get(
    '/requests/{request}',
    [RequestController::class, 'show']
);
```

Gunakan Policy:

```php
$this->authorize('view', $request);
```

---

# 42. SEEDER

Buat seeder awal:

```text
RoleSeeder
PermissionSeeder
UserSeeder
WarehouseSeeder
ProjectSeeder
CategorySeeder
UnitSeeder
```

Buat akun development:

```text
Owner
Admin
User Project
```

Gunakan password dari environment/config bila diperlukan.

Jangan menaruh password production di source code.

---

# 43. FACTORIES

Buat factory untuk:

```text
User
Project
Warehouse
Item
Stock
Request
RequestItem
Distribution
```

Agar testing mudah.

---

# 44. TESTING

Gunakan:

```text
PHPUnit / Pest
Laravel Feature Test
Laravel Unit Test
```

Test:

### AUTH

- login berhasil
- login gagal
- logout
- session
- dashboard redirect

### AUTHORIZATION

User tidak boleh:

```text
mengakses pusat
mengakses proyek lain
mengubah stock pusat
menghapus inventory pusat
mengubah permission
```

### INVENTORY

Test:

```text
receipt
stock increase
distribution
stock decrease
project stock increase
return
```

### REQUEST

Test:

```text
create
submit
approve
reject
prepare
ship
receive
complete
```

### TOOL

Test:

```text
assign
borrow
return
inspection
maintenance
lost
```

---

# 45. UI/UX RULE

Gunakan desain dashboard B2B modern.

Prioritas:

OWNER
→ information & analytics

ADMIN
→ operational speed

USER
→ simplicity

Gunakan:

```text
Search
Filter
Pagination
Sort
Status Badge
Modal
Drawer
Toast
Confirmation
Loading
Empty State
Error State
```

---

# 46. RESPONSIVE

Sistem harus responsif:

```text
Desktop
Tablet
Mobile
```

Gudang kemungkinan menggunakan tablet/mobile.

Pastikan fitur request dan penerimaan dapat digunakan pada layar kecil.

---

# 47. SECURITY

Gunakan:

```text
CSRF protection
Mass assignment protection
Authorization Policy
Validation
Rate Limiting
Session Security
Password Hashing
Environment Variables
Secure File Upload
```

Jangan percaya data dari frontend.

Semua permission harus diperiksa di server.

---

# 48. FILE UPLOAD

Untuk:

```text
surat jalan
dokumen
foto alat
foto kerusakan
lampiran request
```

gunakan:

```text
Storage
```

Validasi:

```text
mime type
size
extension
```

Jangan menyimpan file upload langsung tanpa validasi.

---

# 49. BUSINESS RULE FINAL

Implementasikan:

```text
1. Gudang Pusat adalah pusat supply.

2. Gudang Proyek hanya mendapatkan barang melalui request.

3. Gudang Proyek tidak melakukan pengadaan langsung.

4. Semua perubahan stock wajib memiliki transaksi.

5. Stock tidak boleh negatif secara default.

6. Material dan Alat memiliki lifecycle berbeda.

7. Alat individual dapat dilacak.

8. User hanya dapat mengakses project/warehouse yang assigned.

9. Owner dapat melihat semua warehouse dan project.

10. Admin menangani operasional gudang pusat.

11. Setiap transaksi mencatat user dan timestamp.

12. Distribution harus memiliki from_warehouse dan to_warehouse.

13. Receiving harus menghasilkan stock movement.

14. Tool return harus melalui inspection.

15. Semua perubahan penting harus masuk Audit Log.
```

---

# 50. DEVELOPMENT PHASE

## PHASE 0
Analysis & Architecture

## PHASE 1
Laravel Setup

## PHASE 2
Authentication

## PHASE 3
Role / Permission / Workspace

## PHASE 4
Master Data

## PHASE 5
Inventory

## PHASE 6
Stock Movement

## PHASE 7
Request

## PHASE 8
Distribution

## PHASE 9
Receiving

## PHASE 10
Tool Management

## PHASE 11
Reporting

## PHASE 12
Notification

## PHASE 13
Audit Log

## PHASE 14
UI/UX Polish

## PHASE 15
Testing

## PHASE 16
Deployment

---

# 51. PHASE 0 — WAJIB DIMULAI DULU

Saat prompt ini dijalankan, jangan langsung membuat migration dan controller.

Pertama lakukan analisis:

```text
1. System architecture
2. Actor matrix
3. Permission matrix
4. Sitemap
5. User flow
6. Request flow
7. Distribution flow
8. Material lifecycle
9. Tool lifecycle
10. Warehouse flow
11. Workspace logic
12. Database ERD
13. Model relationship
14. Route architecture
15. Controller architecture
16. Service architecture
17. Policy architecture
18. Validation architecture
19. Dashboard architecture
20. Edge cases
```

Setelah Phase 0 selesai:

JANGAN langsung melanjutkan.

Tampilkan hasil analisis dan tunggu instruksi:

```text
READY FOR PHASE 1
```

---

# 52. FORMAT LAPORAN SETIAP PHASE

Gunakan:

```text
## STATUS
DONE / PARTIAL / BLOCKED

## PHASE
Nama phase

## OBJECTIVE
Tujuan

## IMPLEMENTATION
Apa yang dibuat

## FILE CREATED
Daftar file

## FILE MODIFIED
Daftar file

## DATABASE
Migration / Model changes

## ROUTES
Routes yang dibuat

## CONTROLLERS
Controller yang dibuat

## SERVICES
Service yang dibuat

## POLICIES
Policy yang dibuat

## UI
Halaman/component

## TEST
Test yang dijalankan

## BUG
Bug yang ditemukan

## SECURITY
Security check

## NEXT STEP
Phase berikutnya
```

---

# 53. ATURAN AGENT

WAJIB:

- gunakan Laravel 10
- gunakan Eloquent
- gunakan Migration
- gunakan Form Request
- gunakan Policy
- gunakan Service untuk business logic
- gunakan DB transaction untuk stock
- gunakan Factory dan Seeder
- gunakan automated test
- jangan hard-code user
- jangan hard-code role berdasarkan email
- jangan mengubah requirement tanpa alasan
- jangan membuat fitur di luar scope tanpa menjelaskan
- jangan melewati phase
- jangan menaruh business logic berat di Blade
- jangan menaruh business logic berat di Controller
- jangan membuat duplicate logic

---

# 53A. STRUKTUR TEKNIS ROLE & PERMISSION

Section 1 mewajibkan setiap package eksternal dijelaskan (nama, alasan, manfaat, alternatif). Berikut keputusan untuk Role & Permission:

```text
Package     : spatie/laravel-permission
Alasan      : Role/permission granular (bukan hanya 3 role tetap) dibutuhkan karena
              Owner tetap perlu dibedakan dari kemampuan mengelola user vs hanya
              melihat laporan, dan ke depan kemungkinan ada sub-role (mis. Admin Gudang
              vs Admin Procurement). Menulis ulang sistem role/permission dari nol
              berisiko membuat bug otorisasi (celah keamanan paling kritis di sistem ini).
Manfaat     : Middleware, Blade directive (@can), dan integrasi Policy sudah teruji
              secara luas di komunitas Laravel; mengurangi kemungkinan bug custom-built.
Alternatif  : Kolom `role` (string/enum) langsung di tabel users + Gate manual.
              Lebih sederhana, cukup untuk 3 role tetap seperti spesifikasi saat ini,
              tetapi menyulitkan jika permission granular dibutuhkan nanti.
Keputusan   : Gunakan spatie/laravel-permission. Tetap definisikan 3 role
              (owner, admin, user) sebagai role tetap; permission granular per
              modul (mis. `request.approve`, `stock.adjust`) dipetakan ke role
              tersebut, bukan di-assign bebas ke user.
```

Tabel pivot minimal (otomatis dibuat oleh package):

```text
roles
permissions
model_has_roles
model_has_permissions
role_has_permissions
```

Ditambah tabel custom untuk workspace assignment (Section 14):

```text
user_warehouse   (user_id, warehouse_id)
user_project     (user_id, project_id)
```

---

# 53B. PERMISSION MATRIX (TABEL)

Melengkapi narasi di Section 3–5 dengan tabel eksplisit agar tidak ambigu saat membuat Policy/Gate.

| Modul                      | OWNER | ADMIN | USER (workspace sendiri) |
|-----------------------------|:-----:|:-----:|:-------------------------:|
| Lihat semua warehouse/proyek| ✅    | ❌ (hanya pusat) | ❌ (hanya assigned) |
| Kelola user                 | ✅    | ❌    | ❌ |
| Kelola proyek/warehouse     | ✅    | ❌    | ❌ |
| Kelola material/alat (master)| ✅ (lihat) | ✅ (kelola) | ❌ (lihat saja) |
| Goods Receipt (dari supplier)| ❌ (lihat) | ✅    | ❌ |
| Buat request                | ❌    | ❌    | ✅ |
| Approve/Reject request      | ❌ (lihat) | ✅    | ❌ |
| Buat distribusi              | ❌ (lihat) | ✅    | ❌ |
| Terima distribusi            | ❌    | ❌    | ✅ |
| Ajukan pengembalian alat     | ❌    | ❌    | ✅ |
| Inspeksi/terima pengembalian alat | ❌ (lihat) | ✅ | ❌ |
| Maintenance alat             | ❌ (lihat) | ✅    | ❌ |
| Stock Opname (input)         | ❌    | ✅    | ❌ |
| Stock Opname (approve)       | ✅    | ✅    | ❌ |
| Ubah stock manual (adjustment)| ❌   | ✅ (via opname/approval) | ❌ (dilarang, Section 5) |
| Lihat laporan                | ✅ (semua) | ✅ (pusat) | ✅ (proyek sendiri) |
| Lihat audit log              | ✅    | ✅ (aktivitas sendiri/pusat) | ❌ |

Catatan: tabel ini adalah **acuan minimum**; detail permission granular (`request.approve`, dsb.) mengikuti struktur di Section 53A.

---

# 53C. EDGE CASES (CHECKLIST WAJIB DIANALISIS DI PHASE 0)

Melengkapi poin 20 "Edge cases" di Section 51 dengan daftar eksplisit:

```text
1. Dua request untuk item+warehouse sama disetujui hampir bersamaan (race condition)
   → wajib lockForUpdate (Section 25C).
2. Quantity dikirim < quantity request (stock pusat tidak cukup)
   → PARTIALLY_SHIPPED (Section 25A).
3. Quantity diterima proyek < quantity dikirim (rusak/hilang di jalan)
   → PARTIALLY_RECEIVED + discrepancy (Section 25A).
4. Alat hilang saat masih ditugaskan ke proyek (belum proses return resmi)
   → Admin/User dapat melaporkan LOST langsung tanpa melalui flow Tool Return normal;
     tetap wajib membuat ToolReturn record dengan condition LOST untuk audit trail.
5. User dihapus assignment-nya dari project padahal masih punya request PENDING
   → Request tetap dapat diproses admin, tetapi user tidak lagi bisa melihat/mengaksesnya
     kecuali dikembalikan assignment-nya. Tampilkan status jelas ke Admin.
6. Warehouse proyek dinonaktifkan (status non-aktif) padahal masih ada stock > 0
   → Nonaktifkan hanya boleh dilakukan jika seluruh stock = 0 dan tidak ada
     request/distribution berstatus aktif; validasi ini wajib di Policy/Service.
7. Item dengan unit tidak bisa dipecah desimal (mis. "Sak", "Batang") tapi
   quantity di-input desimal
   → validasi quantity harus integer untuk unit non-desimal (flag pada tabel `units`
     atau validasi di Form Request).
8. Request diajukan lebih dari sekali untuk item yang sama sebelum request pertama
   selesai diproses (duplicate submission)
   → tampilkan warning, tidak diblokir otomatis (bisa jadi memang butuh lagi),
     namun Admin melihat riwayat request aktif item tsb saat review.
9. Stock opname menemukan variance besar tanpa penjelasan
   → notes wajib diisi untuk setiap item dengan variance != 0 sebelum submit.
10. Upload file dengan ekstensi valid tapi mime-type tidak sesuai (spoofing)
    → validasi mime type di server (Section 48), bukan hanya ekstensi.
11. Distribution dibuat tanpa Request terkait (opsional, mis. pengiriman darurat)
    → tentukan apakah `request_id` pada Distribution nullable atau wajib;
      jika nullable, catat alasan di `notes` dan tetap wajib melalui approval Admin.
12. Rollback transaksi stock di tengah proses (mis. distribution gagal di step
    terakhir setelah stock pusat sudah dikurangi)
    → seluruh perubahan stock dalam satu proses (kurangi pusat + catat movement)
      wajib berada dalam SATU DB::transaction() agar rollback penuh, bukan sebagian.
```

---

# 53D. KONFIGURASI ENVIRONMENT (.env)

Minimal variabel environment yang wajib didokumentasikan (nilai aktual tidak boleh
ditaruh di source code, sesuai Section 42 dan 47):

```text
APP_NAME="Construction Warehouse Management System"
APP_ENV=local|production
APP_KEY=
APP_URL=

DB_CONNECTION=mysql
DB_HOST=
DB_PORT=
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=

FILESYSTEM_DISK=local|public|s3
QUEUE_CONNECTION=database|redis

MAIL_MAILER=
MAIL_HOST=
MAIL_FROM_ADDRESS=

SESSION_DRIVER=database
SESSION_LIFETIME=120

APP_LOW_STOCK_CHECK_ENABLED=true
APP_TOOL_OVERDUE_DAYS=7

SEED_OWNER_PASSWORD=
SEED_ADMIN_PASSWORD=
SEED_USER_PASSWORD=
```

`SEED_*_PASSWORD` digunakan oleh seeder akun development (Section 42) agar password
tidak hard-code di source code.

---

# 53E. CODING STANDARD & KONVENSI PENAMAAN

```text
PHP style        : PSR-12
Model             : StudlyCase singular       (Warehouse, RequestItem)
Table             : snake_case plural         (warehouses, request_items)
Foreign key        : {singular_table}_id       (warehouse_id, item_id)
Controller         : {Model}Controller          (RequestController)
Service            : {Domain}Service            (StockService, RequestService)
Form Request        : {Action}{Model}Request    (StoreMaterialRequest)
Policy              : {Model}Policy              (RequestPolicy)
Enum/status          : PHP 8.1 native enum, bukan magic string
Route name           : {role}.{module}.{action}  (admin.requests.approve)
Blade component       : kebab-case               (status-badge, empty-state)
```

Jangan mencampur bahasa penamaan (mis. `nama_barang` dan `item_name` dalam kolom
berbeda untuk konsep yang sama). Gunakan bahasa Inggris untuk seluruh nama kolom,
kelas, dan variabel; bahasa Indonesia hanya untuk label UI/teks yang tampil ke user.

---

# 53F. GIT WORKFLOW

```text
main            → production-ready
develop         → integrasi antar phase
phase/{n}-{nama} → branch kerja per phase, mis. phase/7-request

Commit message  : {phase}: {deskripsi singkat}
                  contoh: "phase-7: add RequestService approve flow"
```

Setiap phase selesai → merge ke `develop` setelah laporan phase (Section 52) dikonfirmasi
"DONE", bukan sebelum diuji.

---

# 53G. PERFORMANCE & NON-FUNCTIONAL

```text
Pagination default        : 15–25 baris per halaman pada semua listing
Index database wajib pada : semua foreign key, kolom status, kolom yang di-filter/search
Eager loading              : gunakan with() untuk hindari N+1 query pada listing
                              (mis. Request::with(['project','items.item'])->paginate())
Query berat/laporan         : pertimbangkan cache singkat (mis. dashboard summary)
                              dengan invalidasi saat data terkait berubah
Browser support              : 2 versi terakhir Chrome, Edge, Firefox, Safari
```

---

# 53H. DETAIL PHASE 16 — DEPLOYMENT

Sebelumnya hanya berupa judul tanpa isi. Checklist minimal:

```text
1. Server requirement
   - PHP 8.1+, ekstensi: mbstring, openssl, pdo_mysql, tokenizer, xml, ctype, json, bcmath
   - MySQL/MariaDB, Composer, Node.js (build asset)

2. Build & deploy
   composer install --no-dev --optimize-autoloader
   npm ci && npm run build
   cp .env.example .env   (isi manual, JANGAN commit .env)
   php artisan key:generate
   php artisan migrate --force
   php artisan db:seed --class=RoleSeeder --force   (seeder wajib, bukan data dummy)
   php artisan storage:link
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache

3. Queue & scheduler (untuk Notification, Section 38)
   - Jalankan queue worker via Supervisor (bukan `queue:work` manual di foreground)
   - Tambahkan `php artisan schedule:run` ke crontab jika ada scheduled task
     (mis. cek tool overdue harian)

4. Keamanan production
   - APP_DEBUG=false
   - APP_ENV=production
   - HTTPS wajib (session cookie secure)
   - Backup database terjadwal (di luar scope aplikasi, dokumentasikan strategi)

5. Rollback plan
   - Simpan hasil migrate sebelumnya / backup DB sebelum migrate --force di production
```

---

# 53I. ERROR HANDLING & LOGGING

```text
Gunakan custom Exception untuk kasus domain, contoh:
- InsufficientStockException
- UnauthorizedWorkspaceAccessException
- InvalidRequestStatusTransitionException

Tangkap di app/Exceptions/Handler.php dan kembalikan pesan yang jelas ke user,
BUKAN stack trace mentah.

Log wajib untuk:
- kegagalan stock transaction (Section 18, 25C)
- kegagalan file upload
- percobaan akses tanpa otorisasi (selain dicatat di Audit Log)
```

---

# 53J. DEFINITION OF DONE (PER PHASE)

Sebuah phase baru dianggap **DONE** (bukan PARTIAL) jika seluruh berikut terpenuhi:

```text
1. Migration berjalan tanpa error dari kondisi fresh (php artisan migrate:fresh --seed).
2. Automated test untuk phase tsb hijau (Section 44).
3. Authorization untuk 3 role sudah diuji manual (owner/admin/user) sesuai Permission
   Matrix (Section 53B) — bukan hanya diasumsikan benar.
4. Tidak ada TODO/placeholder tertinggal di kode inti (boleh ada di UI non-kritis
   dengan catatan eksplisit di laporan phase).
5. Laporan phase (Section 52) sudah diisi lengkap, termasuk BUG dan SECURITY.
```

---

# 54. START — PHASE 0

Mulai sekarang dari **PHASE 0 — ANALYSIS & ARCHITECTURE**.

Jangan membuat migration, model, controller, service, route, atau UI sebelum analisis Phase 0 selesai dan dilaporkan.

Output Phase 0 WAJIB mencakup:

1. architecture
2. sitemap
3. actor matrix
4. permission matrix berdasarkan Section 53B dan boleh diperluas
5. user flow
6. request flow
7. distribution flow termasuk partial shipment/receive
8. stock opname flow
9. warehouse flow
10. inventory flow
11. material lifecycle
12. tool lifecycle
13. workspace selection logic
14. database ERD seluruh model
15. Laravel model relationship
16. migration dependency/order plan
17. route plan
18. controller plan
19. service/action plan
20. policy/middleware/gate plan
21. validation plan
22. dashboard plan
23. notification plan
24. audit log plan
25. business rules
26. edge cases
27. testing strategy
28. implementation order

Setelah Phase 0 selesai, berhenti dan tunggu instruksi. Jangan otomatis masuk Phase 1.

---

# 55. IMPLEMENTATION CONTRACT — ATURAN KERJA AGENT

Agent wajib bertindak sebagai engineer yang mengerjakan repository secara bertahap, bukan sekadar menghasilkan contoh kode.

## 55.1 Aturan umum

- Baca dan pahami repository sebelum mengubah file.
- Jangan menghapus atau menimpa fitur existing tanpa alasan dan laporan.
- Pertahankan backward compatibility jika repository sudah memiliki kode.
- Sebelum membuat file baru, cek apakah file/fitur serupa sudah ada.
- Jangan membuat duplicate model, migration, service, component, route, atau helper.
- Gunakan naming convention Section 53E.
- Gunakan English untuk code/database dan Indonesian untuk UI.
- Jangan hard-code ID, email, username, role berdasarkan nama user, atau warehouse tertentu.
- Jangan menyimpan secret/password production di repository.
- Jangan menaruh business logic berat di Blade atau Controller.
- Semua perubahan database harus melalui migration.
- Semua perubahan stock harus melalui service + DB transaction + row locking.
- Semua endpoint sensitif wajib memiliki authorization server-side.
- Semua workflow status harus divalidasi berdasarkan state transition yang sah.

## 55.2 Urutan kerja setiap phase

```text
READ CURRENT STATE
      ↓
PLAN
      ↓
IMPLEMENT
      ↓
MIGRATE / SEED
      ↓
TEST
      ↓
SECURITY CHECK
      ↓
UI CHECK
      ↓
REPORT
      ↓
WAIT FOR NEXT INSTRUCTION
```

Agent tidak boleh melompati langkah TEST atau SECURITY CHECK.

## 55.3 Jika menemukan requirement ambigu

Jangan menebak secara diam-diam. Gunakan prioritas:

```text
Explicit user requirement
        ↓
This Master Prompt
        ↓
Existing repository behavior
        ↓
Laravel best practice
        ↓
Reasonable default
```

Jika keputusan default memengaruhi database, workflow, authorization, atau stock, tandai sebagai **DECISION REQUIRED** di laporan phase.

---

# 56. PHASE EXECUTION PLAN — SIAP DIKERJAKAN

## PHASE 0 — ANALYSIS & ARCHITECTURE

Tujuan: menghasilkan blueprint final tanpa coding.

Deliverables:

```text
Architecture
Sitemap
Actor Matrix
Permission Matrix
User Flow
Request Flow
Distribution Flow
Partial Shipment/Receive Flow
Stock Opname Flow
Warehouse Flow
Inventory Flow
Material Lifecycle
Tool Lifecycle
Workspace Logic
ERD
Model Relationship
Migration Dependency
Route Plan
Controller Plan
Service Plan
Policy Plan
Validation Plan
Dashboard Plan
Notification Plan
Audit Plan
Business Rules
Edge Cases
Testing Strategy
```

Acceptance criteria:

- Tidak ada konflik antar workflow.
- Semua model memiliki tujuan dan relationship.
- Semua permission dapat dipetakan ke Policy/Gate.
- Semua perubahan stock memiliki jalur transaksi.
- Partial shipment/receive terdefinisi.
- Stock opname terdefinisi.
- Workspace authorization terdefinisi.

## PHASE 1 — LARAVEL SETUP

Implementasikan:

```text
Laravel 10
PHP 8.1+
MySQL/MariaDB
Laravel Breeze
Blade
Tailwind CSS
Alpine.js
```

Checklist:

- environment configuration
- authentication scaffold
- base layout
- navigation structure
- error handling dasar
- storage configuration
- test configuration

Test minimal:

```text
php artisan about
php artisan route:list
php artisan migrate:fresh --seed
php artisan test
```

## PHASE 2 — AUTHENTICATION

Implementasikan:

```text
Login
Logout
Session
Password hashing
Authentication middleware
Post-login redirect
```

Acceptance:

```text
valid login    → authenticated
invalid login  → validation/error
logout         → session destroyed
```

## PHASE 3 — ROLE / PERMISSION / WORKSPACE

Implementasikan:

```text
Owner
Admin
User
Role
Permission
User-Warehouse Assignment
User-Project Assignment
Workspace Selector
Active Workspace Session
```

Pastikan user tidak dapat mengakses workspace lain melalui manipulasi URL atau request payload.

Acceptance:

```text
Owner → global access
Admin → central warehouse scope
User  → assigned project warehouse scope
```

## PHASE 4 — MASTER DATA

Implementasikan:

```text
Project
Warehouse
Category
Unit
Item / Material
Tool
Supplier
```

Aturan:

- Central warehouse hanya satu secara logical business rule.
- Project warehouse harus terkait project.
- Category memiliki type MATERIAL/TOOL.
- Unit mendukung validasi integer/decimal.
- Item tidak boleh dihapus secara hard delete jika sudah memiliki transaksi; gunakan archive/deactivate sesuai kebutuhan.

## PHASE 5 — INVENTORY & STOCK

Implementasikan:

```text
Item
Stock
StockMovement
StockService
InventoryService
```

Constraint penting:

```text
UNIQUE(item_id, warehouse_id)
```

Operasi stock wajib:

```text
DB::transaction()
lockForUpdate()
validate quantity
update stock
create movement
create audit log
```

Test concurrency dan insufficient stock.

## PHASE 6 — GOODS RECEIPT

Flow:

```text
Supplier
  ↓
Goods Receipt
  ↓
Inspection/Acceptance
  ↓
Central Stock
```

Implementasikan accepted/rejected quantity bila dibutuhkan untuk discrepancy penerimaan.

## PHASE 7 — REQUEST

Implementasikan:

```text
Draft
Submit
Review
Approve
Reject
Cancel
```

Request hanya dapat dibuat User Project untuk workspace yang aktif dan assigned.

Request item harus menyimpan quantity yang diminta dan informasi fulfillment yang diperlukan.

## PHASE 8 — DISTRIBUTION

Implementasikan:

```text
Approved Request
  ↓
Distribution Plan
  ↓
Allocation/Reservation
  ↓
Prepare
  ↓
Ready to Ship
```

Satu request dapat memiliki lebih dari satu distribution.

## PHASE 9 — SHIPMENT & RECEIVING

Gunakan model:

```text
Shipment
ShipmentItem
Receiving
ReceivingItem
```

Flow:

```text
Distribution
   ↓
Shipment
   ↓
In Transit
   ↓
Project Receiving
   ↓
Project Stock
```

Aturan:

```text
shipped_quantity <= approved/requested quantity
received_quantity <= shipped_quantity
```

Partial shipment:

```text
Request 500
Shipment 300
Remaining 200
```

Partial receive:

```text
Shipment 300
Receive 280
Variance -20
```

Variance wajib dicatat dan tidak boleh membuat Request otomatis COMPLETED.

## PHASE 10 — TOOL MANAGEMENT

Implementasikan:

```text
Tool Assignment
Tool Return
Inspection
Maintenance
Lost
Overdue
```

Tool lifecycle:

```text
AVAILABLE
→ DISTRIBUTED
→ IN_USE
→ RETURNED
→ INSPECTION
→ AVAILABLE / MAINTENANCE / DAMAGED / LOST
```

Tool yang maintenance/lost tidak boleh didistribusikan.

## PHASE 11 — STOCK OPNAME

Implementasikan:

```text
Create
Snapshot
Counting
Submit
Review
Approve
Post Adjustment
```

Stock tidak berubah sebelum approval.

Setelah approval:

```text
variance != 0
→ ADJUSTMENT stock movement
→ update stock
→ audit log
```

## PHASE 12 — REPORTING

Minimal:

```text
Stock Report
Stock Movement Report
Request Report
Distribution Report
Receiving/Discrepancy Report
Material Usage Report
Tool Report
Maintenance Report
Project Report
```

Semua report harus menghormati authorization scope.

## PHASE 13 — NOTIFICATION

Implementasikan notification untuk:

```text
New Request
Approved
Rejected
Ready to Ship
Shipped
Received
Discrepancy
Low Stock
Tool Due
Tool Overdue
Maintenance
```

## PHASE 14 — AUDIT LOG

Pastikan event penting tercatat:

```text
LOGIN
LOGOUT
CREATE
UPDATE
DELETE/ARCHIVE
APPROVE
REJECT
SHIP
RECEIVE
TRANSFER
RETURN
ADJUSTMENT
```

Audit log immutable bagi user biasa.

## PHASE 15 — UI/UX POLISH

Prioritas:

```text
Owner → monitoring & analytics
Admin → operational speed
User → simple request/receive workflow
```

Semua halaman listing wajib memiliki bila relevan:

```text
Search
Filter
Sort
Pagination
Status Badge
Empty State
Loading State
Error State
Confirmation
Toast
```

Responsive:

```text
Desktop
Tablet
Mobile
```

## PHASE 16 — FINAL TESTING & DEPLOYMENT

Sebelum production:

```text
migrate:fresh --seed
php artisan test
authorization test
workflow test
stock concurrency test
file upload security test
production config review
APP_DEBUG=false
HTTPS
backup strategy
queue worker
scheduler
cache optimization
```

Jangan menyatakan production-ready jika acceptance criteria belum terpenuhi.

---

# 57. STATE TRANSITION CONTRACT

Semua status workflow harus memiliki transition yang eksplisit.

## Request

```text
DRAFT → SUBMITTED
SUBMITTED → REVIEW
REVIEW → APPROVED
REVIEW → REJECTED
APPROVED → PREPARING
PREPARING → READY_TO_SHIP
READY_TO_SHIP → PARTIALLY_SHIPPED
READY_TO_SHIP → SHIPPED
PARTIALLY_SHIPPED → SHIPPED
SHIPPED → PARTIALLY_RECEIVED
SHIPPED → RECEIVED
PARTIALLY_RECEIVED → RECEIVED
RECEIVED → COMPLETED
```

Cancellation hanya boleh dilakukan pada state yang diizinkan business rule.

## Distribution

```text
DRAFT → PREPARING
PREPARING → READY_TO_SHIP
READY_TO_SHIP → SHIPPED / PARTIALLY_SHIPPED
PARTIALLY_SHIPPED → SHIPPED
```

## Stock Opname

```text
DRAFT → COUNTING
COUNTING → SUBMITTED
SUBMITTED → REVIEW
REVIEW → APPROVED / REJECTED
APPROVED → POSTED
```

Jangan mengubah status melalui mass assignment dari request user. Gunakan method/service transition yang terkontrol.

---

# 58. DATABASE & INTEGRITY CONTRACT

Wajib gunakan foreign key dan index yang relevan.

Minimal:

```text
stocks: UNIQUE(item_id, warehouse_id)
```

Index minimal:

```text
foreign keys
status
code
number
warehouse_id
project_id
created_at
```

Untuk transaksi penting gunakan:

```text
DB::transaction()
lockForUpdate()
```

Jangan melakukan stock update dengan query bebas yang tidak melewati domain service.

Jika transaksi gagal:

```text
rollback seluruh perubahan
```

Tidak boleh terjadi kondisi:

```text
stock berkurang
TAPI movement gagal dibuat
```

---

# 59. DEFINITION OF READY / DONE

## Definition of Ready

Sebuah phase siap dikerjakan jika:

```text
Scope jelas
Dependencies diketahui
Database impact diketahui
Authorization impact diketahui
Acceptance criteria tersedia
```

## Definition of Done

Sebuah phase hanya boleh dilaporkan DONE jika:

```text
1. Implementasi selesai sesuai scope.
2. Migration berhasil pada fresh database.
3. Seeder/factory relevan berhasil.
4. Automated test hijau.
5. Authorization Owner/Admin/User diuji.
6. Workflow utama diuji.
7. Edge case relevan diuji.
8. Tidak ada critical bug.
9. Tidak ada secret yang ter-commit.
10. Tidak ada TODO kritis.
11. UI memiliki loading/empty/error state jika relevan.
12. Laporan phase lengkap.
```

Jika belum terpenuhi, gunakan status:

```text
PARTIAL
```

atau:

```text
BLOCKED
```

Jangan menyatakan DONE hanya karena kode berhasil dibuat.

---

# 60. FORMAT OUTPUT AGENT — EXECUTION REPORT

Setiap phase wajib berakhir dengan format berikut:

```text
## STATUS
DONE / PARTIAL / BLOCKED

## PHASE
Phase X — Nama Phase

## OBJECTIVE
Tujuan phase

## IMPLEMENTATION
Ringkasan implementasi

## FILE CREATED
- path/file.php

## FILE MODIFIED
- path/file.php

## DATABASE
Migration/model/constraint/index yang berubah

## ROUTES
Route yang dibuat/diubah

## CONTROLLERS
Controller yang dibuat/diubah

## SERVICES / ACTIONS
Service/action yang dibuat/diubah

## POLICIES / MIDDLEWARE
Authorization yang dibuat/diubah

## UI
Halaman/component yang dibuat/diubah

## TEST
Command test yang dijalankan
Hasil: PASS/FAIL

## EDGE CASE
Edge case yang diuji

## SECURITY
Security check yang dilakukan

## BUG
Bug yang ditemukan dan statusnya

## DECISION REQUIRED
Keputusan yang membutuhkan persetujuan user, jika ada

## NEXT STEP
Phase berikutnya
```

Setelah laporan diberikan, **STOP**. Jangan otomatis mengerjakan phase berikutnya sampai user memberikan instruksi.

---

# 61. FINAL AGENT DIRECTIVE

Ini adalah aturan final yang harus selalu dipatuhi selama seluruh pengembangan:

```text
READ → PLAN → IMPLEMENT → TEST → SECURITY CHECK → REPORT → STOP
```

Prioritas sistem:

```text
DATA INTEGRITY
    ↓
AUTHORIZATION
    ↓
STOCK ACCURACY
    ↓
AUDITABILITY
    ↓
WORKFLOW CORRECTNESS
    ↓
USABILITY
    ↓
VISUAL POLISH
```

Jika terjadi konflik antara kemudahan UI dan keamanan/integritas stock, prioritaskan keamanan dan integritas stock.

Jika terjadi konflik antara kode cepat dan arsitektur yang dapat diaudit, prioritaskan arsitektur yang dapat diaudit.

Jika terjadi kesalahan stock, jangan memperbaiki dengan UPDATE manual tanpa movement/audit. Perbaiki melalui transaksi domain yang benar.

**Jangan melompati phase. Jangan mengarang data. Jangan mengarang permission. Jangan mengarang stock. Jangan menganggap shipment sebagai receiving. Jangan menganggap request selesai hanya karena shipment dibuat. Jangan menganggap alat returned sebelum inspection selesai.**

Setelah membaca prompt ini, agent harus memulai dari **PHASE 0 — ANALYSIS & ARCHITECTURE** dan berhenti setelah laporan Phase 0 selesai.
