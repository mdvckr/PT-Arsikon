let rowCount = 0;
let currentScheduledData = [];
let currentTabFilter = 'all';
// ============================================================
// WAREHOUSE AUTO-LOCK & SELECTION
// Sesuai aturan:
// - Jika ada item dengan tahapan kedatangan -> Gudang DIKUNCI (tidak bisa diubah).
// - Jika belum ada tahapan -> Gudang BISA DIUBAH secara bebas.
// ============================================================

function onUserWarehouseChange(sel) {
    const hint = document.getElementById('warehouseAutoHint');
    if (hint) {
        if (sel.value) {
            const opt = sel.options[sel.selectedIndex];
            hint.style.display = 'block';
            hint.style.cssText = 'font-size:11px;color:#0369a1;margin-top:5px;background:#f0f9ff;padding:4px 8px;border-radius:6px;border:1px solid #bae6fd;';
            hint.innerHTML = '<i class="fas fa-hand-pointer me-1"></i> Gudang tujuan dipilih: <strong>' + escHtml(opt.text) + '</strong> (Belum ada tahapan yang mengunci, dapat diubah bebas)';
        } else {
            hint.style.display = 'none';
        }
    }
}

function checkAndUpdateWarehouseLock() {
    const rows = document.querySelectorAll('#itemsBody tr.item-row');
    let lockedWhId = null;
    let lockedWhName = null;
    let lockedItemName = null;
    let lockedStageName = null;
    let hasAnyItemWithStage = false;

    rows.forEach(function(row) {
        const rowType = row.getAttribute('data-type');
        const isTool = (rowType === 'tool');
        const sel = row.querySelector('.item-select');
        const stageInput = row.querySelector('[id^="stage-input-"]');
        const stageVal = stageInput ? stageInput.value.trim() : '';

        if (sel && sel.value) {
            const list = isTool ? grTools : grMaterials;
            const item = list.find(function(i) { return i.id == sel.value; });
            if (item) {
                // Barang dianggap punya tahapan jika:
                // 1. Memiliki planned_stages atau has_stages
                // 2. Atau stage_reference terisi
                const hasStages = (item.has_stages || (item.planned_stages && item.planned_stages.length > 0) || stageVal !== '');
                if (hasStages && item.warehouse_id) {
                    hasAnyItemWithStage = true;
                    if (!lockedWhId) {
                        lockedWhId = item.warehouse_id;
                        lockedWhName = item.warehouse_name;
                        lockedItemName = item.name;
                        lockedStageName = stageVal || (item.planned_stages && item.planned_stages[0] ? item.planned_stages[0].stage : 'Jadwal Rencana');
                    }
                }
            }
        }
    });

    const whSelect = document.getElementById('warehouseSelect');
    const lockBadge = document.getElementById('warehouseLockBadge');
    const hint = document.getElementById('warehouseAutoHint');
    const icon = document.getElementById('warehouseInputIcon');

    if (!whSelect) return;

    if (hasAnyItemWithStage && lockedWhId) {
        // KUNCI GUDANG: Sesuai aturan, barang memiliki tahapan maka TIDAK BISA DIUBAH
        let found = false;
        for (let i = 0; i < whSelect.options.length; i++) {
            if (whSelect.options[i].value == lockedWhId) {
                whSelect.selectedIndex = i;
                found = true;
                break;
            }
        }
        if (found) flashEl(whSelect, '#fef3c7');

        whSelect.style.pointerEvents = 'none';
        whSelect.style.backgroundColor = '#f1f5f9';
        whSelect.style.color = '#334155';
        whSelect.style.fontWeight = '700';
        whSelect.tabIndex = -1;

        if (lockBadge) lockBadge.style.display = 'inline-flex';
        if (icon) icon.className = 'fas fa-lock input-icon text-warning';

        if (hint) {
            hint.style.display = 'block';
            hint.style.cssText = 'font-size:11.5px;color:#92400e;margin-top:5px;background:#fef3c7;padding:6px 10px;border-radius:6px;border:1px solid #fde68a;display:flex;align-items:center;gap:6px;line-height:1.4;';
            hint.innerHTML = '<i class="fas fa-lock" style="color:#d97706;font-size:13px;flex-shrink:0;"></i>'
                + '<div>Gudang tujuan <strong>terkunci otomatis</strong> ke: <strong>' + escHtml(lockedWhName || ('Gudang #' + lockedWhId))
                + '</strong><div style="font-size:10.5px;color:#b45309;margin-top:1px;">(Sesuai tahapan ' + escHtml(lockedStageName) + ' pada barang <em>' + escHtml(lockedItemName) + '</em> — tidak dapat diubah).</div></div>';
        }
    } else {
        // BELUM ADA TAHAPAN: Buka kunci, pengguna bebas mengubah / memilih gudang
        whSelect.style.pointerEvents = 'auto';
        whSelect.style.backgroundColor = '#ffffff';
        whSelect.style.color = '#0f172a';
        whSelect.style.fontWeight = '400';
        whSelect.tabIndex = 0;

        if (lockBadge) lockBadge.style.display = 'none';
        if (icon) icon.className = 'fas fa-warehouse input-icon';

        if (hint) {
            hint.style.display = 'none';
            hint.innerHTML = '';
        }
    }
}

// ============================================================
// ROW MANAGEMENT & EMPTY STATE
// ============================================================

function focusAndHighlightField(fieldId) {
    const el = document.getElementById(fieldId);
    if (el) {
        el.scrollIntoView({ behavior: 'smooth', block: 'center' });
        setTimeout(function() {
            el.focus();
            flashEl(el, '#bfdbfe');
        }, 250);
    }
}

function checkEmptyState(customMessage) {
    const tbody = document.getElementById('itemsBody');
    if (!tbody) return;
    const rows = tbody.querySelectorAll('tr.item-row');
    const emptyRow = document.getElementById('emptyItemsRow');
    if (rows.length === 0) {
        if (!emptyRow) {
            const desc = customMessage || 'Pilih <strong>PO Lapangan</strong> atau <strong>Purchase Order (PO Vendor)</strong> pada formulir di bawah untuk memuat barang otomatis, atau tambahkan item manual.';
            const html = '<tr id="emptyItemsRow" class="empty-receipt-row">'
                + '<td colspan="8" class="empty-receipt-canvas">'
                + '<div class="empty-receipt-card">'
                + '<div class="empty-icon-circle">'
                + '<i class="fas fa-boxes-stacked"></i>'
                + '</div>'
                + '<h6 class="empty-title">Belum Ada Barang yang Dimuat</h6>'
                + '<p class="empty-desc">' + desc + '</p>'
                + '<div class="empty-actions-primary">'
                + '<button type="button" class="btn btn-sm btn-primary" style="font-size:12px;font-weight:600;border-radius:8px;padding:7px 16px;display:inline-flex;align-items:center;gap:6px;box-shadow:0 1px 3px rgba(37,99,235,0.2);" onclick="addRow(\'material\')">'
                + '<i class="fas fa-plus"></i> Tambah Material'
                + '</button>'
                + '<button type="button" class="btn btn-sm btn-warning" style="font-size:12px;font-weight:600;border-radius:8px;padding:7px 16px;color:#ffffff;background:#d97706;border-color:#b45309;display:inline-flex;align-items:center;gap:6px;box-shadow:0 1px 3px rgba(217,119,6,0.2);" onclick="addRow(\'tool\')">'
                + '<i class="fas fa-plus"></i> Tambah Alat'
                + '</button>'
                + '</div>'
                + '<div class="empty-actions-secondary">'
                + '<span style="font-size:11px;color:#94a3b8;font-weight:600;text-transform:uppercase;letter-spacing:0.4px;margin-right:2px;">Atau Impor PO:</span>'
                + '<button type="button" class="btn btn-sm btn-light" style="font-size:11.5px;font-weight:600;border-radius:7px;padding:5px 12px;border:1px solid #cbd5e1;background:#ffffff;color:#334155;display:inline-flex;align-items:center;gap:5px;cursor:pointer;" onclick="focusAndHighlightField(\'mrSelect\')">'
                + '<i class="fas fa-clipboard-check text-success"></i> PO Lapangan'
                + '</button>'
                + '<button type="button" class="btn btn-sm btn-light" style="font-size:11.5px;font-weight:600;border-radius:7px;padding:5px 12px;border:1px solid #cbd5e1;background:#ffffff;color:#334155;display:inline-flex;align-items:center;gap:5px;cursor:pointer;" onclick="focusAndHighlightField(\'poSelect\')">'
                + '<i class="fas fa-file-contract text-primary"></i> PO Vendor'
                + '</button>'
                + '<button type="button" class="btn btn-sm btn-light" style="font-size:11.5px;font-weight:600;border-radius:7px;padding:5px 12px;border:1px solid #cbd5e1;background:#ffffff;color:#334155;display:inline-flex;align-items:center;gap:5px;cursor:pointer;" onclick="openScheduledModal()">'
                + '<i class="fas fa-calendar-check text-info"></i> Jadwal Kedatangan'
                + '</button>'
                + '</div>'
                + '</div>'
                + '</td></tr>';
            tbody.innerHTML = html;
        }
    } else {
        if (emptyRow) emptyRow.remove();
    }
    updateGrandTotal();
}

function addRow(type, opts) {
    type = type || 'material';
    opts = opts || {};

    const emptyRow = document.getElementById('emptyItemsRow');
    if (emptyRow) emptyRow.remove();

    const idx = rowCount++;
    const isTool = (type === 'tool' || opts.item_type === 'tool');
    const itemsList = isTool ? grTools : grMaterials;

    // Group by category
    const categories = {};
    itemsList.forEach(function(item) {
        const cat = item.category_name || (isTool ? 'Alat / Mesin' : 'Lainnya');
        if (!categories[cat]) categories[cat] = [];
        categories[cat].push(item);
    });

    let targetId = isTool ? opts.tool_id : opts.material_id;
    let customOptionHtml = '';
    let isItemFoundInMaster = false;

    if (targetId) {
        isItemFoundInMaster = itemsList.some(function(i) { return i.id == targetId; });
    }

    // Jika item berasal dari PO / MR khusus proyek dan belum ada di list master
    const customName = opts.material_name || opts.name || opts.custom_item_name || '';
    if (!isItemFoundInMaster && customName) {
        customOptionHtml = '<optgroup label="Khusus PO / Proyek">'
            + '<option value="' + (targetId || '') + '" selected data-custom="1">'
            + escHtml(customName) + ' (Item Khusus PO)'
            + '</option></optgroup>';
    }

    let optionsHtml = customOptionHtml;
    for (const catName in categories) {
        optionsHtml += '<optgroup label="' + escHtml(catName) + '">';
        categories[catName].forEach(function(item) {
            const isSelected = !customOptionHtml && (isTool ? (opts.tool_id == item.id) : (opts.material_id == item.id));
            const stageBadge = (item.planned_stages && item.planned_stages.length > 0)
                ? ' • [📅 ' + item.planned_stages.length + ' Tahap Jadwal]' : '';
            const brandText = item.brand ? ' · ' + item.brand : '';
            const sizeText = item.size ? ' (' + item.size + ')' : '';
            const codeText = (item.sku || item.code) ? ' [' + (item.sku || item.code) + ']' : '';
            optionsHtml += '<option value="' + item.id + '"' + (isSelected ? ' selected' : '') + '>'
                + escHtml(item.name) + escHtml(brandText) + escHtml(sizeText) + escHtml(codeText) + stageBadge
                + '</option>';
        });
        optionsHtml += '</optgroup>';
    }

    const typeBadge = isTool
        ? '<span class="badge" style="background:#fffbeb;color:#b45309;border:1px solid #fde68a;font-weight:700;font-size:11px;padding:3px 8px;border-radius:6px;display:inline-flex;align-items:center;gap:4px;"><i class="fas fa-helmet-safety"></i> Alat</span>'
        : '<span class="badge" style="background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;font-weight:700;font-size:11px;padding:3px 8px;border-radius:6px;display:inline-flex;align-items:center;gap:4px;"><i class="fas fa-box"></i> Material</span>';

    const isRequired = (!customName && !targetId) ? 'required' : '';

    const poItemInput = opts.purchase_order_item_id
        ? '<input type="hidden" name="items[' + idx + '][purchase_order_item_id]" value="' + opts.purchase_order_item_id + '">' : '';

    const mrItemInput = opts.material_request_item_id
        ? '<input type="hidden" name="items[' + idx + '][material_request_item_id]" value="' + opts.material_request_item_id + '">' : '';

    const schedInput = opts.po_delivery_schedule_id
        ? '<input type="hidden" name="items[' + idx + '][po_delivery_schedule_id]" value="' + opts.po_delivery_schedule_id + '">' : '';

    const poNum = opts.purchase_order_number || opts.po_number || '';
    let refText = '';
    if (poNum) {
        refText = '<span style="display:block;font-size:10px;color:#64748b;font-family:monospace;margin-top:2px;" title="Ref PO: ' + escHtml(poNum) + '">#' + escHtml(poNum) + '</span>';
    } else if (opts.mr_number) {
        refText = '<span style="display:block;font-size:10px;color:#64748b;font-family:monospace;margin-top:2px;" title="Ref PO Lapangan: ' + escHtml(opts.mr_number) + '">#' + escHtml(opts.mr_number) + '</span>';
    }

    const customNameInput = customName
        ? '<input type="hidden" name="items[' + idx + '][custom_item_name]" value="' + escHtml(customName) + '">' : '';
    const customUnitInput = (opts.unit_abbr || opts.unit || opts.abbr)
        ? '<input type="hidden" name="items[' + idx + '][custom_item_unit]" value="' + escHtml(opts.unit_abbr || opts.unit || opts.abbr) + '">' : '';
    const customTypeInput = (opts.custom_item_type || opts.type)
        ? '<input type="hidden" name="items[' + idx + '][type]" value="' + escHtml(opts.custom_item_type || opts.type) + '">' : '';

    const typeInput = '<input type="hidden" name="items[' + idx + '][item_type]" value="' + (isTool ? 'tool' : 'material') + '">';
    const selectName = isTool ? ('items[' + idx + '][tool_id]') : ('items[' + idx + '][material_id]');
    const defaultPrompt = isTool ? '-- Pilih Alat --' : '-- Pilih Material --';

    // QTY TARGET / PO
    let qtyShipped = (opts.qty_shipped != null) ? opts.qty_shipped : ((opts.qty_ordered != null) ? opts.qty_ordered : ((opts.qty != null) ? opts.qty : ''));
    if (isTool && qtyShipped !== '' && qtyShipped !== null) {
        const numShipped = parseFloat(qtyShipped);
        qtyShipped = (isNaN(numShipped) || numShipped < 1) ? 1 : Math.round(numShipped);
    }

    // QTY MASUK (STOK)
    let qtyMasuk = (opts.quantity != null) ? opts.quantity : ((opts.qty_received != null) ? opts.qty_received : ((opts.qty != null) ? opts.qty : qtyShipped));
    if (isTool && qtyMasuk !== '' && qtyMasuk !== null) {
        const numQty = parseFloat(qtyMasuk);
        qtyMasuk = (isNaN(numQty) || numQty < 1) ? 1 : Math.round(numQty);
    }

    // SELISIH (KURANG)
    let qtyMissing = (opts.qty_missing != null) ? opts.qty_missing : 0;
    if (qtyShipped && qtyMasuk && !opts.qty_missing && parseFloat(qtyShipped) > parseFloat(qtyMasuk)) {
        qtyMissing = Math.max(0, parseFloat(qtyShipped) - parseFloat(qtyMasuk));
    }

    const itemNotes = opts.notes || '';
    const stageRef = opts.stage_reference || '';
    const unitAbbr = opts.unit_abbr || opts.unit || opts.abbr || (isTool ? 'Unit' : '--');

    const row = '<tr id="row-' + idx + '" class="item-row" data-type="' + (isTool ? 'tool' : 'material') + '">'
        + '<td class="col-type" style="vertical-align:top;padding-top:10px;text-align:center;">'
        + typeInput + poItemInput + mrItemInput + schedInput + customNameInput + customUnitInput + customTypeInput
        + '<input type="hidden" name="items[' + idx + '][stage_reference]" id="stage-input-' + idx + '" value="' + escHtml(stageRef) + '">'
        + '<input type="hidden" name="items[' + idx + '][condition]" value="good">'
        + '<input type="hidden" name="items[' + idx + '][unit_price]" value="' + (opts.unit_price || 0) + '">'
        + '<div style="display:inline-flex;flex-direction:column;align-items:center;gap:1px;">' + typeBadge + refText + '</div></td>'

        + '<td class="col-item" style="vertical-align:top;padding-top:10px;">'
        + '<select name="' + selectName + '" class="form-control item-select" ' + isRequired + ' '
        + 'onchange="onItemChange(this,' + idx + ',' + (isTool ? 'true' : 'false') + ')" '
        + 'style="font-weight:600;font-size:12.5px;">'
        + '<option value="">' + defaultPrompt + '</option>' + optionsHtml
        + '</select>'
        + '<div id="item-meta-' + idx + '" class="item-meta-container"></div>'
        + '<div id="discrepancy-badge-' + idx + '" class="mt-1"></div></td>'

        // Qty Target PO
        + '<td class="col-shipped" style="vertical-align:top;padding-top:10px;">'
        + '<div class="mobile-label-tag">Qty Target PO</div>'
        + '<input type="number" name="items[' + idx + '][qty_shipped]" class="form-control qty-shipped-input text-center" '
        + 'value="' + qtyShipped + '" min="0" step="' + (isTool ? '1' : 'any') + '" '
        + 'placeholder="0" data-idx="' + idx + '" '
        + 'oninput="recalcDiscrepancy(' + idx + ', \'shipped\')" '
        + 'style="font-weight:700;font-size:13px;background:#f8fafc;border:1.5px solid #e2e8f0;color:#334155;border-radius:7px;" title="Kuantitas yang dipesan di PO (Target Referensi)">'
        + '</td>'

        // Qty Masuk (Stok Terupdate)
        + '<td class="col-qty" style="vertical-align:top;padding-top:10px;">'
        + '<div class="mobile-label-tag">Qty Masuk (Stok) *</div>'
        + '<input type="number" name="items[' + idx + '][quantity]" class="form-control qty-input text-center" '
        + 'value="' + qtyMasuk + '" min="' + (isTool ? '1' : '0.01') + '" step="' + (isTool ? '1' : 'any') + '" '
        + 'placeholder="' + (isTool ? '1' : '0') + '" required data-idx="' + idx + '" '
        + 'oninput="recalcDiscrepancy(' + idx + ', \'masuk\')" onchange="validateQtyInput(this, ' + (isTool ? 'true' : 'false') + ')" '
        + 'style="font-weight:800;font-size:13.5px;color:#1e3a8a;border:2px solid #3b82f6;border-radius:7px;box-shadow:0 1px 3px rgba(37,99,235,0.08);" title="Kuantitas fisik diterima baik yang masuk ke stok gudang">'
        + '</td>'

        // Selisih (Kurang)
        + '<td class="col-missing" style="vertical-align:top;padding-top:10px;">'
        + '<div class="mobile-label-tag">Selisih</div>'
        + '<input type="number" name="items[' + idx + '][qty_missing]" class="form-control qty-missing-input text-center" '
        + 'value="' + qtyMissing + '" min="0" step="' + (isTool ? '1' : 'any') + '" '
        + 'placeholder="0" data-idx="' + idx + '" '
        + 'oninput="recalcDiscrepancy(' + idx + ', \'selisih\')" '
        + 'style="font-weight:700;font-size:13px;border-radius:7px;border:1.5px solid #cbd5e1;' + (qtyMissing > 0 ? 'background:#fffbeb;border-color:#d97706;color:#b45309;' : '') + '" title="Selisih barang tidak sesuai PO">'
        + '</td>'

        // Satuan
        + '<td class="col-unit" style="text-align:center;vertical-align:top;padding-top:14px;">'
        + '<div class="mobile-label-tag">Satuan</div>'
        + '<span class="badge" id="unit-' + idx + '" '
        + 'style="background:#f1f5f9;color:#334155;border:1px solid #cbd5e1;font-size:11px;padding:4px 8px;border-radius:6px;font-weight:700;display:inline-block;">'
        + unitAbbr + '</span></td>'

        // Catatan / Keterangan Selisih
        + '<td class="col-notes" style="vertical-align:top;padding-top:10px;">'
        + '<div class="mobile-label-tag">Catatan Selisih</div>'
        + '<input type="text" name="items[' + idx + '][notes]" class="form-control item-notes-input" '
        + 'value="' + escHtml(itemNotes) + '" placeholder="Keterangan / alasan..." '
        + 'style="font-size:12px;padding:6px 10px;border-radius:7px;border:1px solid #cbd5e1;"></td>'

        // Aksi Hapus
        + '<td class="col-action" style="text-align:center;vertical-align:top;padding-top:10px;">'
        + '<button type="button" class="btn-delete-row" onclick="removeRow(' + idx + ')" title="Hapus Baris">'
        + '<i class="fas fa-trash-can"></i></button></td></tr>';

    document.getElementById('itemsBody').insertAdjacentHTML('beforeend', row);

    const sel = document.querySelector('[name="' + selectName + '"]');
    if (sel) {
        if (targetId) sel.value = targetId;
        onItemChange(sel, idx, isTool);
    }

    const itemType = isTool ? 'tool' : 'material';
    if (currentTabFilter !== 'all' && currentTabFilter !== itemType) {
        filterItemsTab(itemType);
    }
    recalcDiscrepancy(idx, 'init');
}

function removeRow(idx) {
    const row = document.getElementById('row-' + idx);
    if (row) row.remove();
    updateGrandTotal();
    checkEmptyState();
    checkAndUpdateWarehouseLock();
}

// ============================================================
// ITEM CHANGE - auto-fill supplier, qty, stages
// ============================================================

function onItemChange(sel, idx, isTool) {
    const opt = sel.options[sel.selectedIndex];
    const metaContainer = document.getElementById('item-meta-' + idx);

    if (!opt || (!opt.value && opt.getAttribute('data-custom') !== '1')) {
        const unitEl = document.getElementById('unit-' + idx);
        if (unitEl) unitEl.textContent = isTool ? 'Unit' : '-';
        if (metaContainer) metaContainer.innerHTML = '';
        checkAndUpdateWarehouseLock();
        return;
    }

    if (opt && opt.getAttribute('data-custom') === '1') {
        if (metaContainer) {
            metaContainer.innerHTML = '<div class="item-meta-tags mt-1"><span class="badge" style="background:#eff6ff;color:#1e40af;font-size:10px;border:1px solid #bfdbfe;"><i class="fas fa-certificate me-1"></i>Item Khusus PO / Proyek</span></div>';
        }
        checkAndUpdateWarehouseLock();
        return;
    }

    const itemsList = isTool ? grTools : grMaterials;
    const item = itemsList.find(function(i) { return i.id == opt.value; });
    if (!item) return;

    const abbr = item.abbr || (isTool ? 'Unit' : '-');
    const unitEl = document.getElementById('unit-' + idx);
    if (unitEl) unitEl.textContent = abbr;

    // AUTO-FILL SUPPLIER (hanya jika belum terisi)
    if (item.supplier_id && item.supplier_name) {
        const supplierInput = document.getElementById('supplierNameInput');
        if (supplierInput && !supplierInput.value.trim()) {
            const supplierHidden = document.getElementById('supplierIdHidden');
            supplierInput.value = item.supplier_name;
            if (supplierHidden) supplierHidden.value = item.supplier_id;
            onSupplierInput(supplierInput);
        }
    }

    // AUTO-FILL QTY dari tahap pertama yang planned
    const firstPlanned = (item.planned_stages || [])[0];
    if (firstPlanned) {
        const qtyInput = document.querySelector('[name="items[' + idx + '][quantity]"]');
        if (qtyInput && (!qtyInput.value || parseFloat(qtyInput.value) <= 0)) {
            const pQty = parseFloat(firstPlanned.qty);
            if (!isNaN(pQty) && pQty > 0) {
                qtyInput.value = isTool ? Math.max(1, Math.round(pQty)) : pQty;
            } else {
                qtyInput.value = isTool ? 1 : '';
            }
        }
        // AUTO-FILL tanggal dari tahap pertama
        if (firstPlanned.date) {
            const dateInput = document.getElementById('receiptDateInput');
            if (dateInput) {
                dateInput.value = firstPlanned.date;
                flashEl(dateInput, '#eff6ff');
            }
        }
    }

    // Render clean subtle meta line (Senior UI/UX: no noisy rainbow badges)
    const metaParts = [];
    if (item.sku || item.code) metaParts.push('<span>SKU: <strong style="font-family:monospace;color:#475569;">' + escHtml(item.sku || item.code) + '</strong></span>');
    if (item.brand) metaParts.push('<span>Merek: <strong style="color:#475569;">' + escHtml(item.brand) + '</strong></span>');
    if (item.size) metaParts.push('<span>Ukuran: <strong style="color:#475569;">' + escHtml(item.size) + '</strong></span>');
    if (item.type && item.type !== 'tool' && item.type !== 'material') metaParts.push('<span>' + escHtml(item.type) + '</span>');

    let metaHtml = '';
    if (metaParts.length > 0) {
        metaHtml = '<div style="font-size:11px;color:#64748b;margin-top:4px;display:flex;align-items:center;gap:6px;flex-wrap:wrap;">'
            + metaParts.join('<span style="color:#cbd5e1;">•</span>')
            + '</div>';
    }

    // Render tahapan hanya jika ada rencana yang belum masuk (hindari kotak kuning kosong jika 0 rencana)
    const plannedStages = (item.planned_stages || []).filter(function(stg) { return stg.status === 'planned'; });
    if (plannedStages.length > 0) {
        const stageInputEl = document.getElementById('stage-input-' + idx);
        const currentStageLower = stageInputEl ? stageInputEl.value.trim().toLowerCase() : '';

        metaHtml += '<div style="margin-top:6px;display:flex;align-items:center;gap:6px;flex-wrap:wrap;">'
            + '<span style="font-size:11px;font-weight:600;color:#92400e;display:inline-flex;align-items:center;gap:3px;"><i class="fas fa-clock text-warning"></i> Pilih Tahap:</span>';

        plannedStages.forEach(function(stg, sIdx) {
            const stageDate = stg.date ? formatDmy(stg.date) : '-';
            const safeStage = escHtml(stg.stage || ('Tahap ' + (sIdx + 1)));
            const isSelected = currentStageLower && (currentStageLower === safeStage.toLowerCase());
            const isAutoFirst = (sIdx === 0 && !currentStageLower);
            const activeClass = (isSelected || isAutoFirst) ? ' active' : '';

            const supId = JSON.stringify(item.supplier_id || null);
            const supName = JSON.stringify(item.supplier_name || null);
            const rawQty = stg.qty ? stg.qty : 0;
            const safeQty = isTool ? Math.max(1, Math.round(parseFloat(rawQty) || 1)) : rawQty;
            const whId = JSON.stringify(stg.warehouse_id || item.warehouse_id || null);
            const whName = JSON.stringify(stg.warehouse_name || item.warehouse_name || null);

            metaHtml += '<button type="button" class="btn-stage-pill' + activeClass + '" '
                + 'id="stage-pill-' + idx + '-' + sIdx + '" '
                + 'onclick="selectStageForItem(' + idx + ',\'' + safeStage + '\',' + safeQty + ',\'' + (stg.date || '') + '\',' + sIdx + ',' + supId + ',' + supName + ',' + whId + ',' + whName + ')" '
                + 'style="font-size:11px;padding:2px 8px;border-radius:5px;" '
                + 'title="Pilih: ' + safeStage + ' - ' + safeQty + ' ' + item.abbr + ' (' + stageDate + ')">'
                + safeStage + ' (' + safeQty + ' ' + item.abbr + ')'
                + '</button>';

            if (isSelected || isAutoFirst) {
                (function(s, sI, sQty, sWhId, sWhName) {
                    setTimeout(function() {
                        selectStageForItem(idx, s.stage || ('T' + (sI + 1)), sQty, s.date || '', sI, item.supplier_id, item.supplier_name, sWhId, sWhName);
                    }, 0);
                })(stg, sIdx, safeQty, (stg.warehouse_id || item.warehouse_id || null), (stg.warehouse_name || item.warehouse_name || null));
            }
        });

        metaHtml += '</div>';
    }

    if (metaContainer) metaContainer.innerHTML = metaHtml;
    recalcRow(idx);
    checkAndUpdateWarehouseLock();
}

// ============================================================
// SELECT STAGE - auto-fill qty, tanggal, supplier, gudang
// ============================================================

function selectStageForItem(idx, stageName, qty, date, sIdx, supplierId, supplierName, warehouseId, warehouseName) {
    // 1. Stage reference
    const stageInput = document.getElementById('stage-input-' + idx);
    if (stageInput) stageInput.value = stageName;

    // 2. Auto-fill qty
    const qtyInput = document.querySelector('[name="items[' + idx + '][quantity]"]');
    if (qtyInput) {
        const rowEl = document.getElementById('row-' + idx);
        const isToolRow = rowEl && rowEl.getAttribute('data-type') === 'tool';
        const numQty = parseFloat(qty);
        if (!isNaN(numQty) && numQty > 0) {
            qtyInput.value = isToolRow ? Math.max(1, Math.round(numQty)) : numQty;
        } else if (!qtyInput.value || parseFloat(qtyInput.value) <= 0) {
            qtyInput.value = 1;
        }
        flashEl(qtyInput, '#ecfdf5');
    }

    // 3. Auto-fill tanggal dari jadwal
    if (date) {
        const dateInput = document.getElementById('receiptDateInput');
        if (dateInput) {
            dateInput.value = date;
            flashEl(dateInput, '#eff6ff');
        }
    }

    // 4. Auto-fill supplier (hanya jika belum terisi)
    if (supplierId && supplierName) {
        const supplierInput = document.getElementById('supplierNameInput');
        if (supplierInput && !supplierInput.value.trim()) {
            setSupplierByName(supplierName, supplierId);
        }
    }

    // 5. Kunci gudang tujuan otomatis sesuai tahapan
    checkAndUpdateWarehouseLock();

    // 6. Highlight pill aktif
    const allPills = document.querySelectorAll('[id^="stage-pill-' + idx + '-"]');
    allPills.forEach(function(p) { p.classList.remove('active'); });
    const activePill = document.getElementById('stage-pill-' + idx + '-' + sIdx);
    if (activePill) activePill.classList.add('active');

    recalcRow(idx);
}

// ============================================================
// SUPPLIER INPUT
// ============================================================

function onSupplierInput(input) {
    const val = input.value.trim().toLowerCase();
    const hiddenId = document.getElementById('supplierIdHidden');
    const hint = document.getElementById('supplierHint');
    const found = grSuppliers.find(function(s) { return s.name.toLowerCase() === val; });
    if (found) {
        if (hiddenId) hiddenId.value = found.id;
        if (hint) hint.innerHTML = '<i class="fas fa-check-circle me-1" style="color:#16a34a;"></i>'
            + '<span style="color:#166534;font-weight:600;">Supplier ditemukan: ' + escHtml(found.name) + '</span>';
    } else {
        if (hiddenId) hiddenId.value = '';
        if (hint && val.length > 0) {
            hint.innerHTML = '<i class="fas fa-plus-circle me-1" style="color:#d97706;"></i>'
                + '<span style="color:#92400e;font-weight:600;">Supplier baru "' + escHtml(input.value.trim()) + '" akan otomatis didaftarkan</span>';
        } else if (hint) {
            hint.innerHTML = '<i class="fas fa-info-circle me-1"></i> Pilih dari daftar atau ketik nama supplier baru - akan otomatis terdaftar';
        }
    }
}

function setSupplierByName(name, id) {
    const input = document.getElementById('supplierNameInput');
    const hidden = document.getElementById('supplierIdHidden');
    const hint = document.getElementById('supplierHint');
    if (input) input.value = name || '';
    if (hidden) hidden.value = id || '';
    if (hint && name) {
        hint.innerHTML = '<i class="fas fa-check-circle me-1" style="color:#16a34a;"></i>'
            + '<span style="color:#166534;font-weight:600;">Supplier PO: ' + escHtml(name) + '</span>';
    }
}

// ============================================================
// DISCREPANCY (SELISIH) CALCULATION & SYNC
// ============================================================

function recalcDiscrepancy(idx, changedField) {
    const row = document.getElementById('row-' + idx);
    if (!row) return;

    const shippedInput = row.querySelector('.qty-shipped-input');
    const masukInput = row.querySelector('.qty-input');
    const missingInput = row.querySelector('.qty-missing-input');
    const badgeContainer = document.getElementById('discrepancy-badge-' + idx);

    let shipped = parseFloat(shippedInput ? shippedInput.value : 0);
    let masuk = parseFloat(masukInput ? masukInput.value : 0);
    let missing = parseFloat(missingInput ? missingInput.value : 0);

    if (isNaN(shipped) || shipped < 0) shipped = 0;
    if (isNaN(masuk) || masuk < 0) masuk = 0;
    if (isNaN(missing) || missing < 0) missing = 0;

    const isTool = row.getAttribute('data-type') === 'tool';

    if (changedField === 'selisih') {
        // User mengetik Selisih (misal PO 100, selisih 5 -> Masuk menjadi 95)
        if (shipped > 0) {
            masuk = Math.max(0, shipped - missing);
            if (isTool) masuk = Math.round(masuk);
            if (masukInput) masukInput.value = masuk;
        }
    } else if (changedField === 'masuk') {
        // User mengetik Qty Masuk (misal PO 100, masuk 95 -> Selisih menjadi 5)
        if (shipped > 0) {
            missing = Math.max(0, shipped - masuk);
            if (isTool) missing = Math.round(missing);
            if (missingInput) missingInput.value = missing;
        }
    } else if (changedField === 'shipped') {
        // User mengubah Qty Target/PO
        if (masukInput && (!masukInput.value || parseFloat(masukInput.value) <= 0)) {
            masukInput.value = shipped;
            masuk = shipped;
            missing = 0;
            if (missingInput) missingInput.value = 0;
        } else {
            missing = Math.max(0, shipped - masuk);
            if (missingInput) missingInput.value = missing;
        }
    }

    // Update Visual Badge & Highlight (Clean senior UI: only show warning when there's an actual shortfall)
    if (badgeContainer) {
        if (shipped > 0 && missing > 0) {
            badgeContainer.innerHTML = '<span class="badge" style="background:#fef3c7;color:#b45309;border:1px solid #fde68a;font-size:10px;padding:2px 6px;font-weight:700;"><i class="fas fa-triangle-exclamation me-1"></i>Kurang ' + missing + '</span>';
            if (missingInput) {
                missingInput.style.borderColor = '#d97706';
                missingInput.style.backgroundColor = '#fffbeb';
                missingInput.style.color = '#b45309';
            }
        } else {
            // Bersih total: tidak perlu badge hijau jika Qty sesuai PO
            badgeContainer.innerHTML = '';
            if (missingInput) {
                missingInput.style.borderColor = '#cbd5e1';
                missingInput.style.backgroundColor = '#ffffff';
                missingInput.style.color = '';
            }
        }
    }

    updateGrandTotal();
}

// ============================================================
// LOAD PO ITEMS via AJAX
// ============================================================

function loadPoItems(poId) {
    const banner = document.getElementById('poInfoBanner');
    const bannerText = document.getElementById('poBannerText');
    const mrSelect = document.getElementById('mrSelect');
    if (!poId) {
        if (banner) banner.style.display = 'none';
        return;
    }

    if (banner) banner.style.display = 'block';
    if (bannerText) bannerText.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Mengambil data item PO...';

    fetch(grConfig.poItemsUrl + '/' + poId)
        .then(function(r) {
            if (!r.ok) throw new Error('HTTP error ' + r.status);
            return r.json();
        })
        .then(function(data) {
            // Auto-fill supplier dari PO
            if (data.supplier_id && data.supplier_name) {
                setSupplierByName(data.supplier_name, data.supplier_id);
            } else if (data.supplier_id) {
                const sup = grSuppliers.find(function(s) { return s.id == data.supplier_id; });
                if (sup) setSupplierByName(sup.name, sup.id);
            } else if (data.supplier_name) {
                setSupplierByName(data.supplier_name, '');
            }

            if (data.warehouse_id) {
                const whSelect = document.getElementById('warehouseSelect');
                if (whSelect) {
                    whSelect.value = data.warehouse_id;
                    onUserWarehouseChange(whSelect);
                }
            }

            // Auto-sync mrSelect jika PO ini terkait dengan PO Lapangan
            if (data.material_request_id && mrSelect) {
                mrSelect.value = data.material_request_id;
                const mrBanner = document.getElementById('mrInfoBanner');
                const mrBannerText = document.getElementById('mrBannerText');
                if (mrBanner && mrBannerText && data.mr_number) {
                    mrBanner.style.display = 'block';
                    mrBanner.style.background = '#f0fdf4';
                    mrBanner.style.border = '1px solid #bbf7d0';
                    mrBanner.style.color = '#166534';
                    mrBannerText.innerHTML = '<i class="fas fa-check-circle me-1"></i> Terhubung dengan PO Lapangan <strong>#' + escHtml(data.mr_number) + '</strong> (' + escHtml(data.project_name || '-') + ').';
                }
            }

            document.getElementById('itemsBody').innerHTML = '';
            rowCount = 0;

            if (data.items && data.items.length > 0) {
                data.items.forEach(function(item) {
                    const targetQty = (item.qty_remaining != null) ? item.qty_remaining : item.qty_ordered;
                    addRow(item.item_type || 'material', {
                        material_id: item.material_id,
                        tool_id: item.tool_id,
                        material_name: item.material_name,
                        custom_item_name: item.custom_item_name,
                        custom_item_unit: item.custom_item_unit,
                        custom_item_type: item.custom_item_type,
                        unit_abbr: item.unit_abbr,
                        purchase_order_item_id: item.purchase_order_item_id,
                        purchase_order_number: data.po_number,
                        supplier_name: data.supplier_name,
                        material_request_item_id: item.material_request_item_id,
                        po_delivery_schedule_id: item.po_delivery_schedule_id,
                        stage_reference: item.stage_reference,
                        qty_shipped: targetQty,
                        quantity: targetQty,
                        qty_missing: 0,
                        notes: item.notes || '',
                        unit_price: item.unit_price,
                        mr_number: item.mr_number || data.mr_number,
                    });
                });
                if (banner) banner.style.display = 'block';
                let txt = 'PO #' + escHtml(data.po_number) + ': ' + data.items.length + ' item dari ' + (data.supplier_name || '-') + '.';
                if (data.mr_number) {
                    txt += ' (Pemenuhan PO Lapangan #' + data.mr_number + ')';
                }
                if (bannerText) bannerText.textContent = txt + ' Target PO dan Qty Masuk telah dimuat. Jika ada selisih, isi kolom Selisih agar stok terupdate sesuai fisik.';
            } else {
                if (banner) banner.style.display = 'block';
                if (bannerText) bannerText.innerHTML = '<i class="fas fa-check-circle" style="color:#16a34a;"></i> Seluruh item pada PO ini telah diterima secara penuh atau belum memiliki rincian item.';
                checkEmptyState('Seluruh item pada PO ini telah diterima secara penuh atau belum memiliki rincian item.');
            }
        })
        .catch(function(err) {
            console.error('Error loadPoItems:', err);
            alert('Gagal memuat rincian item PO: ' + (err.message || err));
            if (document.querySelectorAll('#itemsBody tr.item-row').length === 0) {
                checkEmptyState();
            }
        });
}

// ============================================================
// PO LAPANGAN (MATERIAL REQUEST) & PO VENDOR HANDLERS
// ============================================================

function onMrSelectChange(mrId) {
    const banner = document.getElementById('mrInfoBanner');
    const bannerText = document.getElementById('mrBannerText');
    const whSelect = document.getElementById('warehouseSelect');
    const poSelect = document.getElementById('poSelect');

    if (!mrId) {
        if (banner) banner.style.display = 'none';
        if (poSelect && window.initialPoSelectOptions) {
            poSelect.innerHTML = window.initialPoSelectOptions;
            poSelect.value = '';
        }
        setSupplierByName('', '');
        document.getElementById('itemsBody').innerHTML = '';
        rowCount = 0;
        checkEmptyState();
        checkAndUpdateWarehouseLock();
        return;
    }

    const mr = (typeof grMaterialRequests !== 'undefined' ? grMaterialRequests : []).find(function(m) { return m.id == mrId; });

    // 1. Pilih gudang tujuan sesuai gudang proyek pemohon
    if (mr && mr.warehouse_id && whSelect) {
        whSelect.value = mr.warehouse_id;
        onUserWarehouseChange(whSelect);
    }

    // 2. Muat item MR (reset filter PO agar user memilih vendor yang tiba secara kondusif)
    loadMrItems(mrId, null);
}

function onPoSelectChange(poId) {
    const mrSelect = document.getElementById('mrSelect');
    const mrId = mrSelect ? mrSelect.value : null;

    if (poId) {
        if (mrId) {
            loadMrItems(mrId, poId);
        } else {
            loadPoItems(poId);
        }
    } else if (mrId) {
        loadMrItems(mrId, null);
    } else {
        const banner = document.getElementById('poInfoBanner');
        if (banner) banner.style.display = 'none';
        setSupplierByName('', '');
        document.getElementById('itemsBody').innerHTML = '';
        rowCount = 0;
        checkEmptyState();
        checkAndUpdateWarehouseLock();
    }
}

function loadMrItems(mrId, filterPoId) {
    if (!grConfig.mrItemsUrl || !mrId) return;

    const banner = document.getElementById('mrInfoBanner');
    const bannerText = document.getElementById('mrBannerText');
    if (banner && bannerText) {
        banner.style.display = 'block';
        banner.style.background = '#f0fdf4';
        banner.style.border = '1px solid #bbf7d0';
        banner.style.color = '#166534';
        bannerText.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Mengambil data barang PO Lapangan...';
    }

    const fetchUrl = grConfig.mrItemsUrl + '/' + mrId + (filterPoId ? '?purchase_order_id=' + encodeURIComponent(filterPoId) : '');

    fetch(fetchUrl)
        .then(function(r) {
            if (!r.ok) throw new Error('HTTP error ' + r.status);
            return r.json();
        })
        .then(function(data) {
            if (data.warehouse_id) {
                const whSelect = document.getElementById('warehouseSelect');
                if (whSelect) {
                    whSelect.value = data.warehouse_id;
                    onUserWarehouseChange(whSelect);
                }
            }

            // Update pilihan PO pada dropdown poSelect sesuai daftar PO yang terhubung dengan PO Lapangan ini
            const poSelect = document.getElementById('poSelect');
            if (poSelect) {
                if (data.purchase_orders && data.purchase_orders.length > 0) {
                    // Jika hanya ada 1 PO vendor dan belum ada filter, langsung pilihkan otomatis
                    if (data.purchase_orders.length === 1 && !filterPoId) {
                        loadMrItems(mrId, data.purchase_orders[0].id);
                        return;
                    }

                    let poOptionsHtml = '';
                    if (data.purchase_orders.length > 1) {
                        poOptionsHtml += '<option value="">-- Pilih PO Vendor yang Sedang Datang (' + data.purchase_orders.length + ' Vendor Terhubung) --</option>';
                    }
                    data.purchase_orders.forEach(function(po) {
                        const isSelected = (filterPoId && filterPoId == po.id) ? ' selected' : '';
                        let statusLabel = '';
                        if (po.unreceived_count != null && po.unreceived_count > 0) {
                            statusLabel = ' [' + po.unreceived_count + ' item belum masuk]';
                        } else if (po.status === 'received') {
                            statusLabel = ' [Sudah Lengkap Diterima]';
                        }
                        poOptionsHtml += '<option value="' + po.id + '" data-mr-id="' + data.material_request_id + '"' + isSelected + '>'
                            + '#' + escHtml(po.po_number) + ' • ' + escHtml(po.supplier_name || 'Vendor') 
                            + statusLabel
                            + (po.items_summary ? ' (' + escHtml(po.items_summary) + ')' : '')
                            + '</option>';
                    });
                    poSelect.innerHTML = poOptionsHtml;
                    if (filterPoId) {
                        poSelect.value = filterPoId;
                    }
                } else {
                    // Jika tidak ada PO Vendor yang perlu diterima lagi (Semua selesai diterima)
                    poSelect.innerHTML = '<option value="">-- Semua PO Vendor untuk PO Lapangan ini Sudah Selesai Diterima --</option>';
                }
            }

            // Jika seluruh PO vendor untuk MR ini sudah selesai diterima:
            if (!data.purchase_orders || data.purchase_orders.length === 0) {
                setSupplierByName('', '');

                if (banner && bannerText) {
                    banner.style.display = 'block';
                    banner.style.background = '#f0fdf4';
                    banner.style.border = '1px solid #bbf7d0';
                    banner.style.color = '#166534';
                    bannerText.innerHTML = '<i class="fas fa-check-circle me-1" style="color:#16a34a;"></i> Seluruh PO Vendor untuk PO Lapangan <strong>#' + escHtml(data.mr_number) + '</strong> telah selesai diterima secara penuh!';
                }

                document.getElementById('itemsBody').innerHTML = '';
                rowCount = 0;
                checkEmptyState('Seluruh barang untuk PO Lapangan #' + escHtml(data.mr_number) + ' telah selesai diterima secara penuh.');
                return;
            }

            // Jika MR dipecah ke banyak PO Vendor dan user belum memilih PO mana yang tiba:
            // Jangan campur aduk barang dari berbagai vendor ke dalam satu formulir Goods Receipt!
            if (data.purchase_orders && data.purchase_orders.length > 1 && !filterPoId) {
                setSupplierByName('', '');

                if (banner && bannerText) {
                    banner.style.display = 'block';
                    banner.style.background = '#fffbeb';
                    banner.style.border = '1px solid #fde68a';
                    banner.style.color = '#92400e';
                    bannerText.innerHTML = '<i class="fas fa-truck-ramp-box me-1" style="color:#d97706;"></i> PO Lapangan <strong>#' + escHtml(data.mr_number) + '</strong> memiliki <strong>' + data.purchase_orders.length + ' PO Vendor berbeda</strong>. Silakan pilih <strong>PO Vendor</strong> pada pilihan di samping untuk memuat barang yang sedang tiba.';
                }

                document.getElementById('itemsBody').innerHTML = '';
                rowCount = 0;
                checkEmptyState('PO Lapangan #' + escHtml(data.mr_number) + ' terbagi menjadi ' + data.purchase_orders.length + ' PO Vendor. Silakan pilih PO Vendor di atas untuk memuat barang yang tiba.');
                return;
            }

            let displayItems = data.items || [];

            // Auto-fill supplier jika ada di respons PO spesifik atau jika hanya 1 PO
            if (data.supplier_name) {
                setSupplierByName(data.supplier_name, data.supplier_id || '');
            } else if (!filterPoId) {
                const supInput = document.getElementById('supplierNameInput');
                const supHidden = document.getElementById('supplierIdHidden');
                if (supInput) supInput.value = '';
                if (supHidden) supHidden.value = '';
            }

            // Tampilkan Banner Info PO Lapangan
            if (banner && bannerText) {
                banner.style.display = 'block';
                banner.style.background = '#f0fdf4';
                banner.style.border = '1px solid #bbf7d0';
                banner.style.color = '#166534';
                let msg = '<i class="fas fa-check-circle me-1"></i> Terhubung dengan PO Lapangan <strong>#' + escHtml(data.mr_number) + '</strong> (' + escHtml(data.warehouse_name || '-') + ').';
                if (filterPoId && data.supplier_name) {
                    msg += ' <span class="badge" style="background:#eff6ff;color:#1e40af;margin-left:4px;">PO Vendor: ' + escHtml(data.supplier_name) + '</span>';
                } else if (data.purchase_orders && data.purchase_orders.length > 1) {
                    msg += ' <span class="badge" style="background:#fef3c7;color:#92400e;margin-left:4px;">' + data.purchase_orders.length + ' PO Vendor Terhubung</span>';
                }
                msg += ' Menampilkan ' + displayItems.length + ' jenis barang.';
                bannerText.innerHTML = msg;
            }

            document.getElementById('itemsBody').innerHTML = '';
            rowCount = 0;

            if (displayItems.length > 0) {
                displayItems.forEach(function(item) {
                    const targetQty = (item.qty_remaining != null) ? item.qty_remaining : item.qty_ordered;
                    addRow(item.item_type || 'material', {
                        material_id: item.material_id,
                        tool_id: item.tool_id,
                        material_name: item.material_name,
                        custom_item_name: item.custom_item_name,
                        custom_item_unit: item.custom_item_unit,
                        custom_item_type: item.custom_item_type,
                        unit_abbr: item.unit_abbr,
                        material_request_item_id: item.material_request_item_id,
                        purchase_order_item_id: item.purchase_order_item_id,
                        purchase_order_number: item.purchase_order_number,
                        supplier_name: item.supplier_name,
                        po_delivery_schedule_id: item.po_delivery_schedule_id,
                        stage_reference: item.stage_reference,
                        qty_shipped: targetQty,
                        quantity: targetQty,
                        qty_missing: 0,
                        notes: item.notes || '',
                        unit_price: item.unit_price,
                        mr_number: data.mr_number,
                    });
                });
            } else {
                checkEmptyState('Seluruh item pada PO ini sudah diterima lengkap!');
                if (banner && bannerText) {
                    bannerText.innerHTML = '<i class="fas fa-check-circle" style="color:#16a34a;"></i> Seluruh item pada PO ini sudah diterima lengkap!';
                }
            }
        })
        .catch(function(err) {
            console.error('Gagal memuat item MR:', err);
            alert('Gagal memuat rincian item PO Lapangan: ' + (err.message || err));
            if (document.querySelectorAll('#itemsBody tr.item-row').length === 0) {
                checkEmptyState();
            }
        });
}

// ============================================================
// MODAL & OTOMATISASI JADWAL KEDATANGAN
// ============================================================

let currentModalFilter = 'all';

function getAllPlannedStages() {
    const list = [];
    (grMaterials || []).forEach(function(m) {
        (m.planned_stages || []).forEach(function(stg, sIdx) {
            list.push({
                item_type: 'material',
                material_id: m.id,
                tool_id: null,
                code: m.code || m.sku || '-',
                name: m.name,
                category_name: m.category_name || 'Material',
                unit: m.abbr || 'Unit',
                stage_reference: stg.stage || ('Tahap ' + (sIdx + 1)),
                scheduled_date: stg.date || '-',
                quantity: parseFloat(stg.qty || 1),
                notes: stg.notes || '',
                supplier_id: m.supplier_id || null,
                supplier_name: m.supplier_name || null,
            });
        });
    });
    (grTools || []).forEach(function(t) {
        (t.planned_stages || []).forEach(function(stg, sIdx) {
            list.push({
                item_type: 'tool',
                material_id: null,
                tool_id: t.id,
                code: t.code || '-',
                name: t.name,
                category_name: t.category_name || 'Alat / Mesin',
                unit: 'Unit',
                stage_reference: stg.stage || ('Tahap ' + (sIdx + 1)),
                scheduled_date: stg.date || '-',
                quantity: parseFloat(stg.qty || 1),
                notes: stg.notes || '',
                supplier_id: null,
                supplier_name: null,
            });
        });
    });
    list.sort(function(a, b) {
        return (a.scheduled_date || '').localeCompare(b.scheduled_date || '');
    });
    return list;
}

function checkAndShowScheduledPrompt() {
    const planned = getAllPlannedStages();
    const count = planned.length;

    // Update badge tombol
    const badge = document.getElementById('badgeSchedCount');
    if (badge) {
        if (count > 0) {
            badge.textContent = count;
            badge.style.display = 'inline-block';
        } else {
            badge.style.display = 'none';
        }
    }

    // Update banner cepat
    const banner = document.getElementById('scheduledBanner');
    const bannerCount = document.getElementById('scheduledBannerCount');
    if (banner && bannerCount) {
        if (count > 0) {
            bannerCount.textContent = count;
            banner.style.display = 'flex';
        } else {
            banner.style.display = 'none';
        }
    }

    // Update counter di modal jika ada
    const countAllEl = document.getElementById('countSchedAll');
    if (countAllEl) countAllEl.textContent = count;

    const todayDate = grConfig.today || new Date().toISOString().split('T')[0];
    const todayCount = planned.filter(function(i) { return i.scheduled_date === todayDate; }).length;
    const countTodayEl = document.getElementById('countSchedToday');
    if (countTodayEl) countTodayEl.textContent = todayCount;
}

function autoImportAllScheduled() {
    const planned = getAllPlannedStages();
    if (planned.length === 0) {
        alert('Tidak ada barang dalam jadwal rencana kedatangan.');
        return;
    }

    // Hapus baris kosong default pertama jika belum diisi
    const rows = document.querySelectorAll('#itemsBody tr.item-row');
    if (rows.length === 1) {
        const firstRowSelect = rows[0].querySelector('.item-select');
        if (firstRowSelect && !firstRowSelect.value) rows[0].remove();
    }

    let firstSupplierId = null, firstSupplierName = null, firstScheduledDate = null;

    planned.forEach(function(item) {
        if (item.supplier_id && !firstSupplierId) {
            firstSupplierId = item.supplier_id;
            firstSupplierName = item.supplier_name || null;
        }
        if (item.scheduled_date && item.scheduled_date !== '-' && !firstScheduledDate) {
            firstScheduledDate = item.scheduled_date;
        }
        addRow(item.item_type, {
            item_type: item.item_type,
            material_id: item.material_id,
            tool_id: item.tool_id,
            qty: item.quantity,
            stage_reference: item.stage_reference,
            unit_abbr: item.unit,
            condition: 'good',
        });
    });

    // Auto-fill supplier
    if (firstSupplierId || firstSupplierName) {
        const existingInput = document.getElementById('supplierNameInput');
        if (existingInput && !existingInput.value.trim()) {
            const sup = firstSupplierId ? grSuppliers.find(function(s) { return s.id == firstSupplierId; }) : null;
            if (sup) setSupplierByName(sup.name, sup.id);
            else if (firstSupplierName) setSupplierByName(firstSupplierName, firstSupplierId || '');
        }
    }

    // Auto-fill tanggal
    if (firstScheduledDate) {
        const dateInput = document.getElementById('receiptDateInput');
        if (dateInput) {
            dateInput.value = firstScheduledDate;
            flashEl(dateInput, '#eff6ff');
        }
    }

    // Sembunyikan banner setelah diimpor
    const banner = document.getElementById('scheduledBanner');
    if (banner) banner.style.display = 'none';

    showToast(planned.length + ' item jadwal kedatangan berhasil ditarik otomatis!');
}

function openScheduledModal() {
    checkAndShowScheduledPrompt();
    const modal = document.getElementById('scheduledModal');
    if (modal) modal.classList.add('active');
    setModalFilter('all');
}

function closeScheduledModal() {
    const modal = document.getElementById('scheduledModal');
    if (modal) modal.classList.remove('active');
}

function onDateChanged() {
    const dateInput = document.getElementById('receiptDateInput');
    const modalDate = document.getElementById('modalScheduleDate');
    if (modalDate && dateInput) modalDate.value = dateInput.value;
}

function setModalFilter(filterType) {
    currentModalFilter = filterType;
    const btnAll = document.getElementById('btnSchedAll');
    const btnToday = document.getElementById('btnSchedToday');

    if (btnAll) {
        if (filterType === 'all') {
            btnAll.className = 'btn btn-sm btn-primary';
        } else {
            btnAll.className = 'btn btn-sm btn-light border';
        }
    }

    if (btnToday) {
        if (filterType === 'today') {
            btnToday.className = 'btn btn-sm btn-primary';
        } else {
            btnToday.className = 'btn btn-sm btn-light border';
        }
    }

    fetchScheduledItems(filterType);
}

function fetchScheduledItems(filterType) {
    filterType = filterType || currentModalFilter || 'all';
    const container = document.getElementById('scheduledItemsList');
    if (!container) return;

    container.innerHTML = '<div class="text-center p-4 text-muted" style="font-size:13px;">'
        + '<i class="fas fa-spinner fa-spin me-1"></i> Memuat data jadwal kedatangan...</div>';

    let dateParam = 'all';
    if (filterType === 'today') {
        dateParam = grConfig.today || new Date().toISOString().split('T')[0];
    } else if (filterType === 'date') {
        const modalDate = document.getElementById('modalScheduleDate');
        dateParam = modalDate ? modalDate.value : 'all';
    }

    const url = grConfig.scheduledUrl + '?date=' + encodeURIComponent(dateParam);

    fetch(url)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            currentScheduledData = data.items || [];
            if (currentScheduledData.length === 0) {
                const ket = (filterType === 'today') ? 'hari ini' : ((filterType === 'date') ? 'tanggal ini' : 'gudang ini');
                container.innerHTML = '<div class="text-center p-4 text-muted">'
                    + '<i class="fas fa-calendar-xmark text-muted" style="font-size:32px;margin-bottom:8px;"></i>'
                    + '<div class="fw-700" style="color:#334155;font-size:14px;">Tidak Ada Rencana Kedatangan (' + ket + ')</div>'
                    + '<p style="font-size:12px;margin-top:4px;">Klik tab <strong>"Semua Rencana"</strong> untuk melihat seluruh barang dengan jadwal kedatangan.</p>'
                    + '<button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="setModalFilter(\'all\')">Lihat Semua Rencana</button>'
                    + '</div>';
                return;
            }

            let html = '<div class="flex items-center justify-between mb-2" style="background:#f1f5f9;padding:6px 10px;border-radius:6px;">'
                + '<span style="font-size:12px;color:#334155;">Ditemukan <strong>' + currentScheduledData.length + '</strong> barang rencana kedatangan:</span>'
                + '<button type="button" class="btn btn-sm btn-light border" onclick="toggleSelectAllScheduled(true)" style="font-size:11px;padding:2px 8px;font-weight:600;">Centang Semua</button></div>';

            currentScheduledData.forEach(function(item, i) {
                const isTool = item.item_type === 'tool';
                const badge = isTool
                    ? '<span class="badge" style="background:#fef3c7;color:#b45309;"><i class="fas fa-helmet-safety"></i> Alat</span>'
                    : '<span class="badge" style="background:#eff6ff;color:#2563eb;"><i class="fas fa-box"></i> Material</span>';
                
                const supplierBadge = item.supplier_name 
                    ? '<span class="badge" style="background:#f0fdf4;color:#166534;font-size:10px;border:1px solid #bbf7d0;"><i class="fas fa-building me-1"></i>' + escHtml(item.supplier_name) + '</span>'
                    : '';

                html += '<label class="scheduled-item-card" id="scheduled-card-' + i + '" style="cursor:pointer;display:flex;align-items:flex-start;gap:12px;padding:10px 12px;border:1px solid #e2e8f0;border-radius:8px;margin-bottom:8px;background:#ffffff;transition:all 0.15s ease;">'
                    + '<input type="checkbox" class="scheduled-checkbox" value="' + i + '" checked style="width:18px;height:18px;margin-top:3px;accent-color:#2563eb;cursor:pointer;">'
                    + '<div style="flex:1;">'
                    + '<div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;margin-bottom:3px;">' + badge
                    + '<strong style="color:#0f172a;font-size:13.5px;">' + escHtml(item.name) + '</strong>'
                    + '<span class="badge badge-secondary" style="font-size:10px;">' + escHtml(item.code) + '</span>'
                    + supplierBadge + '</div>'
                    + '<div class="text-muted" style="font-size:11.5px;display:flex;align-items:center;gap:8px;flex-wrap:wrap;">'
                    + '<span style="color:#b45309;font-weight:600;"><i class="fas fa-layer-group"></i> ' + escHtml(item.stage_reference) + '</span> &bull; '
                    + '<span><i class="fas fa-cubes"></i> Rencana Masuk: <strong style="color:#0f172a;">' + item.quantity + ' ' + escHtml(item.unit) + '</strong></span> &bull; '
                    + '<span style="color:#2563eb;"><i class="fas fa-calendar-day"></i> Jadwal: <strong>' + formatDmy(item.scheduled_date) + '</strong></span>'
                    + (item.notes ? ' &bull; <em>"' + escHtml(item.notes) + '"</em>' : '')
                    + '</div></div></label>';
            });

            container.innerHTML = html;
        })
        .catch(function() {
            container.innerHTML = '<div class="alert alert-danger" style="font-size:12px;">Gagal memuat jadwal kedatangan.</div>';
        });
}

function toggleSelectAllScheduled(forceState) {
    const checkboxes = document.querySelectorAll('.scheduled-checkbox');
    if (checkboxes.length === 0) return;
    let shouldCheck = true;
    if (forceState !== undefined) {
        shouldCheck = forceState;
    } else {
        const checkedCount = document.querySelectorAll('.scheduled-checkbox:checked').length;
        shouldCheck = (checkedCount < checkboxes.length);
    }
    checkboxes.forEach(function(cb) { cb.checked = shouldCheck; });
}

function applySelectedScheduledItems() {
    const checkboxes = document.querySelectorAll('.scheduled-checkbox:checked');
    if (checkboxes.length === 0) { alert('Silakan pilih minimal 1 item kedatangan.'); return; }

    // Hapus baris kosong pertama
    const rows = document.querySelectorAll('#itemsBody tr.item-row');
    if (rows.length === 1) {
        const firstRowSelect = rows[0].querySelector('.item-select');
        if (firstRowSelect && !firstRowSelect.value) rows[0].remove();
    }

    let firstSupplierId = null, firstSupplierName = null, firstScheduledDate = null;
    let firstWarehouseId = null, firstWarehouseName = null;

    checkboxes.forEach(function(cb) {
        const idx = parseInt(cb.value);
        const item = currentScheduledData[idx];
        if (!item) return;
        if (item.supplier_id && !firstSupplierId) {
            firstSupplierId = item.supplier_id;
            firstSupplierName = item.supplier_name || null;
        }
        if (item.scheduled_date && item.scheduled_date !== '-' && !firstScheduledDate) {
            firstScheduledDate = item.scheduled_date;
        }
        if (item.warehouse_id && !firstWarehouseId) {
            firstWarehouseId = item.warehouse_id;
            firstWarehouseName = item.warehouse_name || null;
        }
        addRow(item.item_type, {
            item_type: item.item_type,
            material_id: item.material_id,
            tool_id: item.tool_id,
            qty: item.quantity,
            stage_reference: item.stage_reference,
            unit_abbr: item.unit,
            condition: 'good',
        });
    });

    // Update dan kunci gudang tujuan sesuai item/tahapan
    checkAndUpdateWarehouseLock();

    // Auto-fill supplier
    if (firstSupplierId || firstSupplierName) {
        const existingInput = document.getElementById('supplierNameInput');
        if (existingInput && !existingInput.value.trim()) {
            const sup = firstSupplierId ? grSuppliers.find(function(s) { return s.id == firstSupplierId; }) : null;
            if (sup) setSupplierByName(sup.name, sup.id);
            else if (firstSupplierName) setSupplierByName(firstSupplierName, firstSupplierId || '');
        }
    }

    // Auto-fill tanggal
    if (firstScheduledDate) {
        const dateInput = document.getElementById('receiptDateInput');
        if (dateInput) {
            dateInput.value = firstScheduledDate;
            flashEl(dateInput, '#eff6ff');
        }
    }

    closeScheduledModal();

    // Sembunyikan banner prompt
    const banner = document.getElementById('scheduledBanner');
    if (banner) banner.style.display = 'none';

    showToast(checkboxes.length + ' item berhasil dimasukkan dari jadwal kedatangan!');
}

function showToast(msg) {
    const toast = document.createElement('div');
    toast.style.cssText = 'position:fixed;top:16px;right:16px;z-index:9999;background:#ecfdf5;color:#047857;'
        + 'border:1px solid #a7f3d0;border-radius:8px;padding:10px 16px;font-size:13px;font-weight:600;'
        + 'box-shadow:0 4px 12px rgba(0,0,0,0.1);display:flex;align-items:center;gap:8px;';
    toast.innerHTML = '<i class="fas fa-check-circle text-success" style="font-size:16px;"></i> ' + escHtml(msg);
    document.body.appendChild(toast);
    setTimeout(function() { toast.remove(); }, 3500);
}

// ============================================================
// FILTER TABS
// ============================================================

function filterItemsTab(type) {
    currentTabFilter = type;
    ['all', 'material', 'tool'].forEach(function(t) {
        const btn = document.getElementById('tab-btn-' + t);
        if (btn) {
            if (t === type) btn.classList.add('active');
            else btn.classList.remove('active');
        }
    });

    const rows = document.querySelectorAll('#itemsBody tr.item-row');
    let visibleCount = 0;
    rows.forEach(function(tr) {
        const rowType = tr.getAttribute('data-type');
        if (type === 'all' || rowType === type) { tr.style.display = ''; visibleCount++; }
        else tr.style.display = 'none';
    });

    let emptyNotice = document.getElementById('empty-filter-notice');
    if (visibleCount === 0 && rows.length > 0) {
        if (!emptyNotice) {
            const label = (type === 'tool' ? 'Alat' : 'Material');
            const addFn = (type === 'tool' ? "addRow('tool')" : "addRow('material')");
            const btnColor = (type === 'tool' ? 'btn-warning' : 'btn-primary');
            const icon = (type === 'tool' ? 'fa-helmet-safety' : 'fa-box-open');
            const tr = document.createElement('tr');
            tr.id = 'empty-filter-notice';
            tr.innerHTML = '<td colspan="8" style="text-align:center;padding:36px 16px;background:#fafcff;">'
                + '<div style="max-width:380px;margin:0 auto;display:flex;flex-direction:column;align-items:center;">'
                + '<div style="width:48px;height:48px;border-radius:12px;background:#f1f5f9;color:#64748b;display:inline-flex;align-items:center;justify-content:center;font-size:20px;margin-bottom:10px;">'
                + '<i class="fas ' + icon + '"></i></div>'
                + '<div style="font-weight:700;color:#0f172a;font-size:14px;margin-bottom:4px;">Belum Ada Item ' + label + '</div>'
                + '<p style="color:#64748b;font-size:12px;line-height:1.5;margin-bottom:14px;">Saat ini belum ada barang bertipe ' + label.toLowerCase() + ' pada sesi penerimaan ini.</p>'
                + '<button type="button" class="btn btn-sm ' + btnColor + '" onclick="' + addFn + '" style="border-radius:7px;font-size:12px;font-weight:600;padding:6px 16px;display:inline-flex;align-items:center;gap:6px;">'
                + '<i class="fas fa-plus"></i> Tambah ' + label + '</button>'
                + '</div></td>';
            document.getElementById('itemsBody').appendChild(tr);
        }
    } else if (emptyNotice) {
        emptyNotice.remove();
    }
}

function updateGrandTotal() {
    const rows = document.querySelectorAll('#itemsBody tr.item-row');
    let countAll = rows.length, countMaterial = 0, countTool = 0;
    rows.forEach(function(tr) {
        if (tr.getAttribute('data-type') === 'tool') countTool++;
        else countMaterial++;
    });
    const elAll = document.getElementById('count-all');
    if (elAll) elAll.textContent = countAll;
    const elMat = document.getElementById('count-material');
    if (elMat) elMat.textContent = countMaterial;
    const elTool = document.getElementById('count-tool');
    if (elTool) elTool.textContent = countTool;
    const totalEl = document.getElementById('totalItemCount');
    if (totalEl) {
        totalEl.innerHTML = '<span style="font-size:12px;color:#64748b;font-weight:600;">Total:</span> '
            + '<span class="badge" style="background:#0f172a;color:#ffffff;font-size:11px;padding:3px 8px;border-radius:6px;font-weight:700;">' + countAll + ' Item</span> '
            + '<span class="badge" style="background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;font-size:11px;padding:3px 8px;border-radius:6px;font-weight:600;">' + countMaterial + ' Material</span> '
            + '<span class="badge" style="background:#fffbeb;color:#b45309;border:1px solid #fde68a;font-size:11px;padding:3px 8px;border-radius:6px;font-weight:600;">' + countTool + ' Alat</span>';
    }
    filterItemsTab(currentTabFilter);
}

function recalcRow(idx) {
    recalcDiscrepancy(idx, 'masuk');
}

function validateQtyInput(input, isTool) {
    let val = parseFloat(input.value);
    if (isTool) {
        if (isNaN(val) || val < 1) {
            input.value = 1;
        } else {
            input.value = Math.max(1, Math.round(val));
        }
    } else {
        if (isNaN(val) || val <= 0) {
            input.value = 1;
        }
    }
    const idx = input.getAttribute('data-idx');
    if (idx != null) {
        recalcDiscrepancy(idx, 'masuk');
    } else {
        updateGrandTotal();
    }
}

// ============================================================
// UTILITIES
// ============================================================

function escHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function formatDmy(dateStr) {
    if (!dateStr) return '-';
    const parts = dateStr.split('-');
    if (parts.length === 3) return parts[2] + '/' + parts[1] + '/' + parts[0];
    return dateStr;
}

function flashEl(el, color) {
    if (!el) return;
    el.style.transition = 'background 0.3s ease';
    el.style.background = color;
    setTimeout(function() { el.style.background = ''; }, 800);
}

// ============================================================
// INIT
// ============================================================

document.addEventListener('DOMContentLoaded', function() {
    const mrSelect = document.getElementById('mrSelect');
    const poSelect = document.getElementById('poSelect');

    if (poSelect) {
        window.initialPoSelectOptions = poSelect.innerHTML;
    }

    const initialMrId = (typeof grConfig !== 'undefined' && grConfig.selectedMrId)
        ? grConfig.selectedMrId
        : (mrSelect ? mrSelect.value : '');
    const initialPoId = (typeof grConfig !== 'undefined' && grConfig.selectedPoId)
        ? grConfig.selectedPoId
        : (poSelect ? poSelect.value : '');

    if (initialMrId && initialPoId) {
        if (mrSelect) mrSelect.value = initialMrId;
        if (poSelect) poSelect.value = initialPoId;
        loadMrItems(initialMrId, initialPoId);
    } else if (initialMrId) {
        if (mrSelect) mrSelect.value = initialMrId;
        onMrSelectChange(initialMrId);
    } else if (initialPoId) {
        if (poSelect) poSelect.value = initialPoId;
        loadPoItems(initialPoId);
    } else {
        checkEmptyState();
    }
    checkAndShowScheduledPrompt();
    checkAndUpdateWarehouseLock();
});
