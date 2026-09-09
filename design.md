# DESIGN.md
# PT ARSIKON CIPTA KARYA
# Construction Warehouse Management System
# Sequential UI/UX Design Specification

## 0. PURPOSE

Dokumen ini adalah sumber utama (single source of truth) untuk desain UI/UX
Construction Warehouse Management System milik PT ARSIKON CIPTA KARYA.

AI/developer harus mengerjakan halaman **SATU PER SATU**, sesuai urutan di bawah.

### Aturan pengerjaan

1. Baca GLOBAL DESIGN SYSTEM terlebih dahulu.
2. Kerjakan hanya halaman/step yang sedang aktif.
3. Jangan mengerjakan halaman berikutnya secara otomatis.
4. Setelah satu halaman selesai:
   - implementasikan UI;
   - pastikan responsive;
   - pastikan interaction state dasar tersedia;
   - test/build halaman;
   - laporkan hasil;
   - STOP.
5. Tunggu instruksi user untuk melanjutkan ke halaman berikutnya.
6. Jangan mengubah desain halaman yang sudah selesai tanpa instruksi.
7. Semua halaman harus menggunakan design system yang sama.
8. Jangan membuat business logic di UI.
9. UI harus mengikuti workflow dan authorization yang ditetapkan oleh sistem.

Urutan:

01 Login
02 Dashboard
03 Projects
04 Warehouses
05 Inventory
06 Requests
07 Distribution
08 Shipments
09 Receiving & Returns
10 Tools
11 Stock Opname
12 Notifications
13 Audit Log
14 Reports

---

# 1. GLOBAL DESIGN SYSTEM

## Brand

Brand:
**PT ARSIKON CIPTA KARYA**

Gunakan logo PT ARSIKON CIPTA KARYA yang diberikan user.

**Jangan mengubah, menggambar ulang, menyederhanakan, atau memodifikasi logo.**

## Brand Color Direction

Identitas visual berasal dari logo:

- Dominant Orange
- Dominant Blue
- Black
- White

Gunakan pendekatan:

- White / very light gray = 70–80% area interface
- Orange = primary action, CTA, active indicator, important highlight
- Blue = secondary accent, information, navigation accent, chart
- Black / dark charcoal = heading dan primary text
- Light blue = subtle informational background
- Light orange = subtle warning/highlight background

Jangan membuat seluruh UI berwarna orange atau biru.

## Visual Style

- Premium enterprise SaaS
- Modern construction / industrial
- Corporate
- Clean
- Professional
- Spacious
- Functional
- High information clarity
- Strong visual hierarchy
- Suitable for a real construction company
- Bukan tampilan CRUD mahasiswa yang generik

Hindari:

- Purple
- Neon colors
- Excessive gradients
- Excessive glassmorphism
- Cartoon UI
- Excessive rounded elements
- Excessive animations
- Low contrast
- Decorative elements yang mengganggu workflow

## Typography

Gunakan salah satu:

- Inter
- Geist
- Manrope

Hierarchy:

- Page title: strong
- Section heading: medium/bold
- Body: readable
- Metadata: small
- Table text: compact but readable

## Layout

Desktop:

- Persistent sidebar
- Top navigation
- Main content
- Wide data tables
- Multi-column dashboard

Tablet:

- Collapsible sidebar
- 2-column cards
- Horizontal table scrolling bila diperlukan

Mobile:

- Collapsed navigation
- Single-column cards
- Responsive tables menjadi cards bila diperlukan
- Touch-friendly buttons
- Mobile-friendly forms

## Sidebar

Logo di bagian atas.

Navigation:

### MAIN
- Dashboard
- Projects
- Warehouses
- Inventory

### OPERATIONS
- Requests
- Distribution
- Shipments
- Receiving & Returns
- Tools
- Stock Opname

### REPORTING
- Reports
- Notifications
- Audit Log

### SYSTEM
- Users
- Roles & Permissions
- Settings

Menu aktif:
- Orange indicator
- Subtle orange background

## Topbar

- Global Search
- Notification
- Current Workspace
- User Avatar
- User Name
- Role
- Profile menu

## Common Components

Gunakan komponen konsisten:

- Cards
- Tables
- Forms
- Inputs
- Select
- Dropdown
- Tabs
- Modal
- Confirmation Dialog
- Toast
- Badge
- Breadcrumb
- Pagination
- Search
- Filter
- Progress Bar
- Timeline
- Empty State
- Loading Skeleton
- Error State

## Interaction

Gunakan micro-interactions yang halus:

- Hover
- Focus
- Active
- Dropdown transition
- Modal transition
- Toast
- Loading skeleton
- Button loading
- Table row hover
- Smooth page transition

Jangan menggunakan animasi berlebihan.

## Listing Page Standard

Semua listing page yang relevan harus memiliki:

- Search
- Filter
- Sort
- Pagination
- Status Badge
- Empty State
- Loading State
- Error State
- Confirmation
- Toast

---

# 2. PAGE 01 — LOGIN

## Objective

Membuat halaman login premium untuk sistem Construction Warehouse
Management System PT ARSIKON CIPTA KARYA.

## Layout

Gunakan split-screen desktop sekitar 50/50.

### Left Side

Visual construction/warehouse:

- Modern construction project
- Warehouse racks
- Construction materials
- Professional equipment
- Industrial environment
- Corporate photography
- Subtle blue/orange lighting
- Clean composition

Gunakan subtle dark-blue overlay.

Logo dan headline:

**Construction Management, Simplified.**

Supporting text:

**Manage projects, warehouses, inventory, tools and material distribution in one centralized system.**

### Right Side

White authentication panel.

Elements:

- Company logo
- Heading: `Welcome Back`
- Subtitle: `Sign in to access your warehouse management system.`
- Email input
- Password input
- Show/hide password
- Remember me
- Forgot password
- Primary `Sign In` button
- Error message
- Loading state

Button utama menggunakan orange brand color.

Input focus menggunakan subtle blue accent.

Footer text:

`Secure access for authorized users.`

## UX

Login harus terasa:

- Secure
- Corporate
- Professional
- Simple
- Fast

---

# 3. PAGE 02 — DASHBOARD

## Objective

Dashboard utama untuk monitoring operasional.

Header:

**Good Morning, Admin**

Subtitle:

**Monitor construction operations and warehouse inventory.**

## Metric Cards

- Total Inventory
- Pending Requests
- Active Shipments
- Pending Returns

Setiap card:

- Large number
- Label
- Icon
- Trend
- Percentage change bila tersedia

## Main Chart

Title:

**Inventory Overview**

Tampilkan:

- Incoming
- Outgoing
- Adjustments
- Stock movement over time

Gunakan orange + blue sebagai chart accent.

## Warehouse Overview

Warehouse cards berisi:

- Warehouse name
- Location
- Capacity
- Current stock
- Utilization
- Status

Gunakan progress bar.

## Recent Requests

Table:

- Request ID
- Project
- Requester
- Date
- Items
- Priority
- Status
- Action

## Recent Activities

Timeline:

- Request Created
- Request Approved
- Shipment Prepared
- Shipment Shipped
- Goods Received
- Tool Returned

Dashboard harus membantu admin memahami kondisi sistem dengan cepat.

---

# 4. PAGE 03 — PROJECTS

## Header

**Projects**

Subtitle:

**Manage construction projects and their warehouse requirements.**

CTA:

`+ Add Project`

## Toolbar

- Search Projects
- Status
- Location
- Warehouse
- Project Manager
- Sort
- Export

## Project Cards

Setiap card:

- Construction thumbnail
- Project name
- Project code
- Location
- Project manager
- Assigned warehouse
- Inventory usage
- Progress
- Status

Status:

- Planning
- Active
- Completed
- Archived

## Project Detail Tabs

- Overview
- Inventory
- Requests
- Distribution
- Shipments
- Receiving
- Tools
- Activity

Gunakan construction imagery secara subtle.

---

# 5. PAGE 04 — WAREHOUSES

## Header

**Warehouses**

Subtitle:

**Monitor warehouse capacity, inventory and construction operations.**

CTA:

`+ Add Warehouse`

## Filters

- Search
- Warehouse Type
- Location
- Status
- Capacity
- Sort

## Warehouse Cards

Tampilkan:

- Warehouse name
- Warehouse code
- Location
- Warehouse type
- Capacity
- Current utilization
- Available inventory
- Assigned projects
- Status

Contoh:

Central Warehouse
WH-CEN-001
Yogyakarta

Capacity:
78%

Inventory:
1,248 items

Assigned Projects:
8

Status:
ACTIVE

## Detail Tabs

- Overview
- Inventory
- Stock Movement
- Projects
- Transactions
- Activity

Gunakan progress bar yang jelas untuk utilization.

---

# 6. PAGE 05 — INVENTORY

## Header

**Inventory**

Subtitle:

**Monitor stock levels across warehouses and construction projects.**

## Summary

- Total Items
- Available Stock
- Reserved
- Low Stock
- Out of Stock
- Damaged

## Toolbar

- Search SKU or Item
- Category
- Warehouse
- Stock Status
- Unit
- Sort
- Export

## Main Table

Columns:

- Item
- SKU
- Category
- Warehouse
- Available
- Reserved
- Damaged
- Unit
- Status
- Actions

Status:

- AVAILABLE
- LOW STOCK
- OUT OF STOCK
- DAMAGED
- RESERVED

Color semantics:

- Green = available
- Orange = low stock
- Red = critical/damaged
- Blue = information

## Inventory Detail

- Item image/icon
- Item Name
- SKU
- Category
- Unit
- Available
- Reserved
- Damaged
- Warehouse Distribution
- Stock Movement Chart
- Transaction History

Stock harus terlihat sebagai hasil transaksi/movement,
bukan sekadar angka yang bebas diedit.

---

# 7. PAGE 06 — REQUESTS

## Header

**Requests**

Subtitle:

**Manage material and equipment requests from construction projects.**

CTA:

`+ Create Request`

## Summary

- Draft
- Pending Approval
- Approved
- Processing
- Distributed
- Completed
- Rejected

## Toolbar

- Request ID
- Project
- Requester
- Status
- Priority
- Date Range

## Table

- Request ID
- Project
- Requester
- Created
- Priority
- Items
- Requested Quantity
- Approved Quantity
- Fulfillment
- Status
- Action

## Workflow

DRAFT
→ SUBMITTED
→ APPROVED
→ PROCESSING
→ DISTRIBUTED
→ COMPLETED

## Detail

- Request ID
- Project
- Requester
- Date
- Priority
- Status
- Items
- Requested
- Approved
- Distributed
- Remaining
- Approval Timeline

Actions sesuai authorization:

- Approve
- Reject
- Process
- Distribute

Request harus selalu berada dalam scope workspace/project yang diizinkan.

---

# 8. PAGE 07 — DISTRIBUTION

## Header

**Distribution**

Subtitle:

**Allocate approved inventory from warehouse to construction projects.**

## Summary

- Pending Distribution
- Preparing
- Ready to Ship
- Completed

## Workflow

APPROVED REQUEST
↓
DISTRIBUTION PLAN
↓
ALLOCATION
↓
PREPARE
↓
READY TO SHIP

## Table

- Distribution ID
- Request ID
- Project
- Warehouse
- Items
- Requested
- Allocated
- Remaining
- Status
- Date
- Actions

## Detail

- Source Warehouse
- Destination Project
- Request
- Items
- Requested Quantity
- Allocated Quantity
- Remaining Quantity
- Reservation Status

Contoh:

Requested: 500
Allocated: 350
Remaining: 150

Gunakan progress visualization.

Harus jelas bahwa Distribution/Allocation berbeda dari physical Shipment.

---

# 9. PAGE 08 — SHIPMENTS

## Header

**Shipments**

Subtitle:

**Track inventory movement from warehouse to construction projects.**

## Summary

- Prepared
- Ready to Ship
- In Transit
- Delivered
- Received

## Table

- Shipment ID
- Source Warehouse
- Destination Project
- Distribution
- Shipment Date
- Expected Arrival
- Vehicle
- Status
- Actions

## Detail

Shipment ID:

`SHP-2026-001`

Timeline:

PREPARED
→ SHIPPED
→ IN TRANSIT
→ DELIVERED
→ RECEIVED

Items:

- Item
- Approved
- Shipped
- Remaining

Contoh:

Requested: 500
Shipped: 300
Remaining: 200

IMPORTANT:
`SHIPPED` tidak boleh terlihat sama dengan `RECEIVED`.

Shipment adalah event fisik pengiriman.
Receiving adalah konfirmasi penerimaan.

---

# 10. PAGE 09 — RECEIVING & RETURNS

## Objective

Halaman operasional untuk physical receiving dan tool return.

Buat dua tab utama:

1. GOODS RECEIVING
2. TOOL RETURN

---

## TAB 1 — GOODS RECEIVING

Header:

**Receiving**

Subtitle:

**Confirm physical deliveries to construction projects.**

Table:

- Receiving ID
- Shipment ID
- Project
- Received Date
- Received By
- Items
- Discrepancy
- Status
- Actions

Detail:

Shipment Quantity:
300

Received Quantity:
280

Variance:
-20

Workflow:

SHIPMENT
↓
IN TRANSIT
↓
PROJECT RECEIVING
↓
PROJECT STOCK

Discrepancy harus terlihat jelas.

---

## TAB 2 — TOOL RETURN

Header:

**Tool Return**

Subtitle:

**Record returned tools and inspect their condition.**

Form:

- Project
- Shipment / Assignment
- Return Date
- Received By

Table:

- Tool
- Sent
- Returned
- Damaged
- Lost
- Condition
- Notes

Condition:

- GOOD
- MINOR DAMAGE
- MAJOR DAMAGE
- LOST

Workflow:

IN USE
↓
PHYSICAL RETURN
↓
RETURN INPUT
↓
INSPECTION
↓
AVAILABLE / MAINTENANCE / DAMAGED / LOST

### Critical UX Rule

Jangan menganggap alat otomatis kembali hanya karena tanggal
pengembalian telah lewat.

Petugas harus melakukan physical return confirmation.

Confirmation modal:

**Confirm Tool Return?**

`Please verify returned quantities and condition before confirming.`

Setelah confirm:

**Return Confirmed**

`Inventory has been updated successfully.`

Returned quantity masuk ke proses inventory sesuai transaksi.
Tool yang membutuhkan inspection belum boleh langsung dianggap AVAILABLE.

---

# 11. PAGE 10 — TOOLS

## Header

**Tools**

Subtitle:

**Track construction equipment, assignments, returns and maintenance.**

## Summary

- Total Tools
- Available
- In Use
- Maintenance
- Damaged
- Lost
- Overdue

## Toolbar

- Search Tool
- Category
- Warehouse
- Project
- Status
- Sort

## Tool Cards

- Tool image
- Tool name
- Tool code
- Category
- Current location
- Assigned project
- Status

Status:

- AVAILABLE
- DISTRIBUTED
- IN USE
- RETURNED
- INSPECTION
- MAINTENANCE
- DAMAGED
- LOST
- OVERDUE

## Tool Detail

- Tool Profile
- Current Location
- Current Assignment
- Project
- Usage History
- Return History
- Inspection History
- Maintenance History

## Lifecycle

AVAILABLE
↓
DISTRIBUTED
↓
IN USE
↓
RETURNED
↓
INSPECTION
↙       ↓       ↘
AVAILABLE  MAINTENANCE  DAMAGED / LOST

Normal lifecycle = blue.
Active operation = orange.
Critical state = red.

Maintenance/lost harus terlihat tidak tersedia untuk distribusi.

---

# 12. PAGE 11 — STOCK OPNAME

## Header

**Stock Opname**

Subtitle:

**Compare system inventory with physical warehouse counts.**

CTA:

`+ Create Stock Opname`

## Summary

- Open
- Counting
- Waiting Review
- Approved
- Adjustment Required

## Workflow

CREATE
↓
SNAPSHOT
↓
COUNTING
↓
SUBMIT
↓
REVIEW
↓
APPROVE
↓
POST ADJUSTMENT

## Table

- Opname ID
- Warehouse
- Date
- Officer
- Total Items
- Variance
- Status
- Actions

## Counting Interface

Columns:

- Item
- System Quantity
- Physical Quantity
- Variance
- Notes

Contoh:

Drill
System: 100
Physical: 97
Variance: -3

Visual:

Negative variance = red
Positive variance = green
Zero variance = neutral/blue

## Critical Rule

Stock TIDAK berubah selama counting.

Stock baru berubah setelah approval.

Flow:

VARIANCE
↓
STOCK ADJUSTMENT
↓
STOCK UPDATE
↓
AUDIT LOG

---

# 13. PAGE 12 — NOTIFICATIONS

## Header

**Notifications**

Subtitle:

**Stay informed about warehouse and construction operations.**

## Categories

- New Request
- Approved
- Rejected
- Ready to Ship
- Shipped
- Received
- Discrepancy
- Low Stock
- Tool Due
- Tool Overdue
- Maintenance

## Notification Card

Setiap notification:

- Icon
- Title
- Description
- Related module
- Timestamp
- Read/Unread
- Priority

Contoh:

**LOW STOCK**

`Construction Drill is below minimum stock level.`

`2 minutes ago`

Priority:

- CRITICAL
- WARNING
- INFO
- SUCCESS

Controls:

- Mark All as Read
- Filter
- View All

Orange = warning/action.
Blue = information.

---

# 14. PAGE 13 — AUDIT LOG

## Header

**Audit Log**

Subtitle:

**Track important system activities and inventory transactions.**

## Filters

- Search
- User
- Module
- Action
- Record
- Date Range

## Table

- Timestamp
- User
- Action
- Module
- Record
- Description
- Metadata

Actions:

- LOGIN
- LOGOUT
- CREATE
- UPDATE
- DELETE / ARCHIVE
- APPROVE
- REJECT
- SHIP
- RECEIVE
- TRANSFER
- RETURN
- ADJUSTMENT

## Expandable Detail

Ketika row dibuka:

- User
- Timestamp
- Action
- Module
- Record ID
- Description
- Before Data
- After Data
- Metadata

Audit history harus terlihat immutable.

Normal user tidak boleh memiliki destructive controls pada audit log.

---

# 15. PAGE 14 — REPORTS

## Header

**Reports**

Subtitle:

**Analyze inventory, warehouse and construction operations.**

## Filters

- Date Range
- Warehouse
- Project
- Category
- Item
- Tool
- Status

Quick range:

- Today
- This Week
- This Month
- This Year
- Custom Range

## Report Cards

1. Stock Report
2. Stock Movement Report
3. Request Report
4. Distribution Report
5. Receiving / Discrepancy Report
6. Material Usage Report
7. Tool Report
8. Maintenance Report
9. Project Report

Setiap card:

- Icon
- Report title
- Description
- Mini visualization
- View Report

## Analytics

### Inventory Movement

- Incoming
- Outgoing
- Adjustment

### Project Material Usage

Perbandingan material usage antar project.

### Warehouse Utilization

Contoh:

Central Warehouse
78%

Project Warehouse A
62%

Project Warehouse B
48%

### Request Performance

- Submitted
- Approved
- Rejected
- Completed

### Receiving Performance

- Fully Received
- Partially Received
- Discrepancy

### Tool Status

- Available
- In Use
- Maintenance
- Damaged
- Lost

## Export

- Export PDF
- Export Excel
- Print

Reports harus mengikuti authorization scope user.

---

# 16. PAGE COMPLETION CHECKLIST

Sebelum menyatakan satu halaman selesai, periksa:

## Visual

- [ ] Logo benar
- [ ] Orange + blue brand identity konsisten
- [ ] Typography konsisten
- [ ] Spacing konsisten
- [ ] Sidebar konsisten
- [ ] Topbar konsisten
- [ ] Card konsisten
- [ ] Button konsisten
- [ ] Status badge konsisten

## UX

- [ ] Search bila relevan
- [ ] Filter bila relevan
- [ ] Sort bila relevan
- [ ] Pagination bila relevan
- [ ] Empty state
- [ ] Loading state
- [ ] Error state
- [ ] Confirmation
- [ ] Toast
- [ ] Responsive

## Functional UI

- [ ] Form validation state
- [ ] Disabled state
- [ ] Loading button
- [ ] Modal state
- [ ] Success state
- [ ] Error state

## Responsive

- [ ] Desktop
- [ ] Tablet
- [ ] Mobile

---

# 17. SEQUENTIAL EXECUTION PROTOCOL

AI/developer harus menggunakan protokol:

READ
↓
UNDERSTAND CURRENT PAGE
↓
PLAN
↓
IMPLEMENT CURRENT PAGE ONLY
↓
TEST
↓
SECURITY / AUTHORIZATION CHECK
↓
RESPONSIVE CHECK
↓
REPORT
↓
STOP

## STOP RULE

Setelah satu halaman selesai, JANGAN otomatis mengerjakan halaman berikutnya.

Contoh:

Jika user meminta:

`Jalankan Page 01`

Maka hanya:

`01 Login`

yang dikerjakan.

Setelah selesai:

```text
PAGE 01 — LOGIN
STATUS: PASS

Implemented:
- Login layout
- Brand identity
- Form
- Loading state
- Error state
- Responsive layout

Next:
PAGE 02 — DASHBOARD

Waiting for instruction.
```

Kemudian STOP.

Jika user berkata:

`Lanjut`

baru kerjakan:

`02 Dashboard`

Kemudian STOP lagi.

---

# 18. IMPORTANT ARCHITECTURE RULE

UI hanya presentation layer.

Jangan menaruh business logic berat di Blade/frontend.

Arsitektur:

Blade
↓
Controller
↓
Policy / Middleware
↓
Service
↓
Model
↓
MySQL

Controller tetap tipis.
Business logic berada di Service.
Authorization harus dilakukan server-side.

Untuk operasi stock gunakan transaction dan mekanisme locking
yang sesuai agar stock accuracy tetap terjaga.

Prioritas:

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

---

# 19. FINAL INSTRUCTION

Jangan melompati halaman.

Jangan mengimplementasikan seluruh halaman sekaligus.

Jangan mengubah brand identity.

Jangan mengarang workflow baru.

Jangan membuat shipment dianggap receiving.

Jangan membuat request dianggap selesai hanya karena shipment dibuat.

Jangan membuat alat dianggap returned sebelum proses return/inspection yang sesuai.

Jangan membuat stock berubah tanpa transaksi domain yang benar.

Gunakan DESIGN.md ini sebagai pedoman UI/UX utama.

**ONE PAGE AT A TIME.**

**IMPLEMENT → TEST → REPORT → STOP.**
