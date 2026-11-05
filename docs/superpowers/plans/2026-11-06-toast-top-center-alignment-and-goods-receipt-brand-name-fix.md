# SweetAlert2 Toast Top-Center Alignment & Goods Receipt Brand Decoupling Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Center the SweetAlert2 pastel pill toast notification horizontally at the top of the viewport (`top` center) with horizontal capsule layout `[Icon] [Title / Message] [X]`, and decouple the "Merek" (brand) input in the Goods Receipt creation table so typing a brand never modifies the "Nama Barang" (item name) input.

**Architecture:** 
1. Reconfigure SweetAlert2 toast position from `'top-end'` to `'top'` in `public/js/sweetalert-notifications.js` and apply CSS Flexbox ordering (`order: 1` for HTML content, `order: 2` for close button) with horizontal flex-row alignment in `public/css/sweetalert-pastel-toast.css`.
2. Remove `oninput="syncAutoItemName(idx)"` from the `#item-brand-{idx}` table input in `public/js/goods-receipt-create.js`, and remove brand concatenation logic from `syncAutoItemName(idx)` and `onCustomNameInput(idx)` so the brand field remains independent and never alters the item name.

**Tech Stack:** 
- Laravel 11.x, Blade Templates, PHPUnit
- SweetAlert2 11.x, Modern CSS Flexbox
- Vanilla JavaScript DOM scripting for Goods Receipt dynamic tables

---

## Global Constraints
- Preserve all existing goods receipt functionality: custom item registration, direct entry without PO, discrepancy handling, category and unit dropdowns.
- Preserve all 5 semantic pastel color variants (`neutral`, `blue`/`info`, `success`, `warning`, `error`) and Web Audio chimes.
- Do NOT alter database migrations or table schemas.
- Ensure all automated feature tests pass cleanly.

---

## Review Focus
1. **Toast Viewport Alignment:** Toast must be centered horizontally at the top (`left: 50%; transform: translateX(-50%)`), not docked at the right edge (`top-end`).
2. **Pill Flexbox Ordering:** Visual hierarchy must strictly follow `[ Icon ] -> [ Title + Subtitle ] -> [ Close X ]`, preventing `X` from rendering on the left or the icon from stacking vertically.
3. **Merek (Brand) Independence:** Typing into the "Merek" input in the goods receipt table must retain the user's input without touching `items[idx][custom_name]` or changing the text in the "Nama Barang" input.
4. **Custom Item Persistence:** Submitting a goods receipt with a custom brand still correctly records `brand` in `goods_receipt_items` and registers the brand into `materials` / `tools`.
5. **Session Flash & Reverb Parity:** Both session flash messages (`session('success')`) and realtime Reverb / Web Push events must render via the centered pastel toast pill without glitches.

---

## Task Decomposition

### Task 1: Reconfigure SweetAlert2 Toast Position & CSS Flexbox Capsule Layout

**Files:**
- Modify: `public/js/sweetalert-notifications.js:72-84`
- Modify: `public/css/sweetalert-pastel-toast.css:7-105`
- Test: `tests/Feature/NotificationSweetAlertViewTest.php`
- Test: `tests/Feature/NotificationDeduplicationTest.php`

**Steps:**
- [ ] In `public/js/sweetalert-notifications.js`, update `Swal.fire({ toast: true, position: 'top', ... })` from `'top-end'` to `'top'`.
- [ ] In `public/css/sweetalert-pastel-toast.css`:
  - Add container rules for `.swal2-container.swal2-top` with `top: 20px !important; left: 50% !important; right: auto !important; transform: translateX(-50%) !important; width: auto !important; pointer-events: none !important;`.
  - Update `.swal2-popup.swal2-pastel-toast` with `display: flex !important; flex-direction: row !important; align-items: center !important; justify-content: space-between !important; border-radius: 16px !important; min-width: 340px !important; max-width: 540px !important;`.
  - Style `.swal2-popup.swal2-pastel-toast .swal2-html-container` with `order: 1 !important; display: flex !important; flex-direction: row !important; align-items: center !important; gap: 14px !important; margin: 0 !important; padding: 0 !important; flex: 1 1 auto !important; text-align: left !important;`.
  - Style `.swal2-pastel-icon` with `flex: 0 0 auto !important; width: 24px !important; height: 24px !important;`.
  - Style `.swal2-pastel-content` with `flex: 1 1 auto !important; display: flex !important; flex-direction: column !important; gap: 2px !important; text-align: left !important;`.
  - Style `.swal2-popup.swal2-pastel-toast .swal2-close` with `order: 2 !important; position: static !important; margin-left: 14px !important; flex: 0 0 auto !important; width: 28px !important; height: 28px !important; border-radius: 8px !important;`.
- [ ] Update tests in `tests/Feature/NotificationSweetAlertViewTest.php` and `tests/Feature/NotificationDeduplicationTest.php` to assert `position: 'top'` and centered toast container styling.
- [ ] Run `php artisan test tests/Feature/NotificationSweetAlertViewTest.php tests/Feature/NotificationDeduplicationTest.php` to verify all pass.

---

### Task 2: Decouple Brand Input from Item Name in Goods Receipt Creation Table

**Files:**
- Modify: `public/js/goods-receipt-create.js:380-475` (syncAutoItemName and onCustomNameInput)
- Modify: `public/js/goods-receipt-create.js:980-988` (renderCustomItemInputs brand field)
- Modify: `public/js/goods-receipt-create.js:1230-1236` (addRow brand field)
- Test: `tests/Feature/GoodsReceiptStandaloneControllerTest.php`

**Steps:**
- [ ] In `public/js/goods-receipt-create.js`:
  - Line ~985: Remove `oninput="syncAutoItemName(' + idx + ')"` from the brand input cell in `renderCustomItemInputs`.
  - Line ~1233: Remove `oninput="syncAutoItemName(' + idx + ')"` from the brand input cell in `addRow`.
  - Lines ~403-409: Remove the brand concatenation block from `syncAutoItemName(idx)`:
    ```javascript
    // REMOVE THIS:
    if (brand) {
        const joinedSoFar = parts.join(' ').toLowerCase();
        const lowerBrand = brand.toLowerCase();
        if (!joinedSoFar.split(/\s+/).includes(lowerBrand) && !joinedSoFar.endsWith(lowerBrand)) {
            parts.push(brand);
        }
    }
    ```
  - Lines ~452-459: Remove brand from the suffix stripping array in `onCustomNameInput(idx)`.
- [ ] Add an automated test in `tests/Feature/GoodsReceiptStandaloneControllerTest.php` verifying that a custom item with distinct name and brand persists the exact name without the brand appended, and persists the brand in the brand column.
- [ ] Run `php artisan test tests/Feature/GoodsReceiptStandaloneControllerTest.php` to verify test passes.

---

### Task 3: Full Verification & Visual Inspection

**Files:**
- Verify: `public/js/sweetalert-notifications.js`
- Verify: `public/css/sweetalert-pastel-toast.css`
- Verify: `public/js/goods-receipt-create.js`

**Steps:**
- [ ] Run full test suite for notifications and goods receipts:
  - `php artisan test tests/Feature/NotificationDeduplicationTest.php`
  - `php artisan test tests/Feature/NotificationSweetAlertViewTest.php`
  - `php artisan test tests/Feature/GoodsReceiptStandaloneControllerTest.php`
- [ ] Create walkthrough artifact documenting:
  - Before/after layout comparison of the SweetAlert2 toast notification.
  - Visual verification of top-center position and `[Icon] [Title / Message] [X]` flex ordering.
  - Verification of goods receipt brand input typing without mutating item name.

