let rowCount = 0;
let currentScheduledData = [];
let currentTabFilter = 'all';

// ============================================================
// ROW MANAGEMENT
// ============================================================

function addRow(type, opts) {
    type = type || 'material';
    opts = opts || {};
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

    let optionsHtml = '';
    for (const catName in categories) {
        optionsHtml += '<optgroup label="' + escHtml(catName) + '">';
        categories[catName].forEach(function(item) {
            const isSelected = isTool ? (opts.tool_id == item.id) : (opts.material_id == item.id);
            const stageBadge = (item.planned_stages && item.planned_stages.length > 0)
                ? ' \u2022 [\uD83D\uDCC5 ' + item.planned_stages.length + ' Tahap Jadwal]' : '';
            const brandText = item.brand ? ' \u00B7 ' + item.brand : '';
            const sizeText = item.size ? ' (' + item.size + ')' : '';
            const codeText = (item.sku || item.code) ? ' [' + (item.sku || item.code) + ']' : '';
            optionsHtml += '<option value="' + item.id + '"' + (isSelected ? ' selected' : '') + '>'
                + escHtml(item.name) + escHtml(brandText) + escHtml(sizeText) + escHtml(codeText) + stageBadge
                + '</option>';
        });
        optionsHtml += '</optgroup>';
    }

    const typeBadge = isTool
        ? '<span class="badge" style="background:rgba(217,119,6,0.12);color:#b45309;font-weight:700;"><i class="fas fa-helmet-safety"></i> Alat</span>'
        : '<span class="badge" style="background:rgba(37,99,235,0.12);color:#2563eb;font-weight:700;"><i class="fas fa-box"></i> Material</span>';

    const poItemInput = opts.purchase_order_item_id
        ? '<input type="hidden" name="items[' + idx + '][purchase_order_item_id]" value="' + opts.purchase_order_item_id + '">' : '';

    const typeInput = '<input type="hidden" name="items[' + idx + '][item_type]" value="' + (isTool ? 'tool' : 'material') + '">';
    const selectName = isTool ? ('items[' + idx + '][tool_id]') : ('items[' + idx + '][material_id]');
    const defaultPrompt = isTool ? '-- Pilih Alat --' : '-- Pilih Material --';

    const qty = (opts.qty != null) ? opts.qty : ((opts.quantity != null) ? opts.quantity : '');
    const stageRef = opts.stage_reference || '';
    const unitAbbr = opts.unit_abbr || (isTool ? 'Unit' : '--');

    const row = '<tr id="row-' + idx + '" class="item-row" data-type="' + (isTool ? 'tool' : 'material') + '">'
        + '<td style="vertical-align:top;padding-top:10px;">'
        + typeInput + poItemInput
        + '<input type="hidden" name="items[' + idx + '][stage_reference]" id="stage-input-' + idx + '" value="' + escHtml(stageRef) + '">'
        + '<input type="hidden" name="items[' + idx + '][condition]" value="good">'
        + '<input type="hidden" name="items[' + idx + '][unit_price]" value="0">'
        + typeBadge + '</td>'
        + '<td style="vertical-align:top;padding-top:10px;">'
        + '<select name="' + selectName + '" class="form-control item-select" required '
        + 'onchange="onItemChange(this,' + idx + ',' + (isTool ? 'true' : 'false') + ')" '
        + 'style="font-weight:600;font-size:12.5px;">'
        + '<option value="">' + defaultPrompt + '</option>' + optionsHtml
        + '</select>'
        + '<div id="item-meta-' + idx + '" class="item-meta-container"></div></td>'
        + '<td style="vertical-align:top;padding-top:10px;">'
        + '<input type="number" name="items[' + idx + '][quantity]" class="form-control qty-input text-center" '
        + 'value="' + qty + '" min="0.01" step="' + (isTool ? '1' : '0.01') + '" '
        + 'placeholder="0" required data-idx="' + idx + '" '
        + 'oninput="recalcRow(' + idx + ')" style="font-weight:700;font-size:13px;"></td>'
        + '<td style="text-align:center;vertical-align:top;padding-top:14px;">'
        + '<span class="badge" id="unit-' + idx + '" '
        + 'style="background:#f1f5f9;color:#475569;border:1px solid #cbd5e1;font-size:11px;padding:3px 7px;border-radius:4px;font-weight:600;">'
        + unitAbbr + '</span></td>'
        + '<td style="text-align:center;vertical-align:top;padding-top:10px;">'
        + '<button type="button" class="btn-delete-row" onclick="removeRow(' + idx + ')" title="Hapus Baris">'
        + '<i class="fas fa-trash-can"></i></button></td></tr>';

    document.getElementById('itemsBody').insertAdjacentHTML('beforeend', row);

    const targetId = isTool ? opts.tool_id : opts.material_id;
    if (targetId) {
        const sel = document.querySelector('[name="' + selectName + '"]');
        if (sel) onItemChange(sel, idx, isTool);
    }

    const itemType = isTool ? 'tool' : 'material';
    if (currentTabFilter !== 'all' && currentTabFilter !== itemType) {
        filterItemsTab(itemType);
    }
    recalcRow(idx);
}

function removeRow(idx) {
    const row = document.getElementById('row-' + idx);
    if (row) row.remove();
    updateGrandTotal();
    const tbody = document.getElementById('itemsBody');
    if (tbody.querySelectorAll('tr.item-row').length === 0) {
        addRow('material');
    }
}

// ============================================================
// ITEM CHANGE - auto-fill supplier, qty, stages
// ============================================================

function onItemChange(sel, idx, isTool) {
    const opt = sel.options[sel.selectedIndex];
    const metaContainer = document.getElementById('item-meta-' + idx);

    if (!opt || !opt.value) {
        const unitEl = document.getElementById('unit-' + idx);
        if (unitEl) unitEl.textContent = isTool ? 'Unit' : '-';
        if (metaContainer) metaContainer.innerHTML = '';
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
        if (qtyInput && (!qtyInput.value || parseFloat(qtyInput.value) === 0)) {
            qtyInput.value = firstPlanned.qty || '';
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

    // Render meta tags
    let metaHtml = '<div class="item-meta-tags">';
    if (item.sku || item.code) {
        metaHtml += '<span class="badge badge-secondary" style="font-family:monospace;font-size:10px;">'
            + '<i class="fas fa-barcode me-1"></i>' + escHtml(item.sku || item.code) + '</span>';
    }
    if (item.brand) {
        metaHtml += '<span class="badge" style="background:#f1f5f9;color:#334155;font-size:10.5px;border:1px solid #e2e8f0;">'
            + '<i class="fas fa-tag me-1 text-muted"></i>Merek: <strong>' + escHtml(item.brand) + '</strong></span>';
    }
    if (item.size) {
        metaHtml += '<span class="badge" style="background:#eff6ff;color:#1e40af;font-size:10.5px;border:1px solid #bfdbfe;">'
            + '<i class="fas fa-ruler-combined me-1"></i>Ukuran: <strong>' + escHtml(item.size) + '</strong></span>';
    }
    if (item.type && item.type !== 'tool') {
        metaHtml += '<span class="badge" style="background:#f5f3ff;color:#6d28d9;font-size:10.5px;border:1px solid #ddd6fe;">'
            + escHtml(item.type) + '</span>';
    }
    if (item.supplier_name) {
        metaHtml += '<span class="badge" style="background:#f0fdf4;color:#166534;font-size:10.5px;border:1px solid #bbf7d0;">'
            + '<i class="fas fa-building me-1"></i>Supplier: <strong>' + escHtml(item.supplier_name) + '</strong></span>';
    }
    metaHtml += '</div>';

    // Render tahapan
    if (item.incoming_stages && item.incoming_stages.length > 0) {
        const plannedCount = (item.planned_stages || []).length;
        metaHtml += '<div class="item-stages-box">'
            + '<div style="font-size:11px;font-weight:700;color:#92400e;display:flex;align-items:center;justify-content:space-between;gap:4px;margin-bottom:4px;">'
            + '<span><i class="fas fa-calendar-check text-warning me-1"></i> Jadwal Tahapan Barang Ini (Klik untuk pilih):</span>'
            + '<span class="badge" style="background:#fef3c7;color:#78350f;font-size:9.5px;padding:1px 6px;">'
            + plannedCount + ' Rencana Belum Masuk</span>'
            + '</div><div class="stage-chips-wrap">';

        const stageInputEl = document.getElementById('stage-input-' + idx);
        const currentStageLower = stageInputEl ? stageInputEl.value.trim().toLowerCase() : '';

        item.incoming_stages.forEach(function(stg, sIdx) {
            const isPlanned = (stg.status === 'planned');
            const stageDate = stg.date ? formatDmy(stg.date) : '-';
            const safeStage = escHtml(stg.stage || ('Tahap ' + (sIdx + 1)));
            const isSelected = currentStageLower && (currentStageLower === safeStage.toLowerCase());
            const isAutoFirst = (sIdx === 0 && isPlanned && !currentStageLower);
            const activeClass = (isSelected || isAutoFirst) ? ' active' : '';

            if (isPlanned) {
                const supId = JSON.stringify(item.supplier_id || null);
                const supName = JSON.stringify(item.supplier_name || null);
                const safeQty = stg.qty ? stg.qty : 0;
                metaHtml += '<button type="button" class="btn-stage-pill' + activeClass + '" '
                    + 'id="stage-pill-' + idx + '-' + sIdx + '" '
                    + 'onclick="selectStageForItem(' + idx + ',\'' + safeStage + '\',' + safeQty + ',\'' + (stg.date || '') + '\',' + sIdx + ',' + supId + ',' + supName + ')" '
                    + 'title="Klik: ' + safeStage + ' - ' + safeQty + ' ' + item.abbr + ' (' + stageDate + ')">'
                    + '<i class="fas fa-clock text-warning"></i>'
                    + '<strong>' + safeStage + '</strong>: ' + safeQty + ' ' + item.abbr
                    + '<span style="opacity:0.85;font-size:9.5px;">(' + stageDate + ')</span>'
                    + '</button>';

                // Auto-trigger tahap terpilih atau tahap pertama
                if (isSelected || isAutoFirst) {
                    (function(s, sI) {
                        setTimeout(function() {
                            selectStageForItem(idx, s.stage || ('T' + (sI + 1)), s.qty, s.date || '', sI, item.supplier_id, item.supplier_name);
                        }, 0);
                    })(stg, sIdx);
                }
            } else {
                metaHtml += '<span class="btn-stage-pill" '
                    + 'style="background:#f0fdf4;border-color:#bbf7d0;color:#166534;cursor:default;" '
                    + 'title="Sudah diterima">'
                    + '<i class="fas fa-check-circle text-success"></i>'
                    + safeStage + ': ' + stg.qty + ' ' + item.abbr + ' (Sudah Masuk)'
                    + '</span>';
            }
        });

        metaHtml += '</div></div>';
    }

    if (metaContainer) metaContainer.innerHTML = metaHtml;
    recalcRow(idx);
}

// ============================================================
// SELECT STAGE - auto-fill qty, tanggal, supplier
// ============================================================

function selectStageForItem(idx, stageName, qty, date, sIdx, supplierId, supplierName) {
    // 1. Stage reference
    const stageInput = document.getElementById('stage-input-' + idx);
    if (stageInput) stageInput.value = stageName;

    // 2. Auto-fill qty
    const qtyInput = document.querySelector('[name="items[' + idx + '][quantity]"]');
    if (qtyInput && qty) {
        qtyInput.value = qty;
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

    // 5. Highlight pill aktif
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
    if (input) input.value = name;
    if (hidden) hidden.value = id || '';
    if (input) onSupplierInput(input);
}

// ============================================================
// LOAD PO ITEMS via AJAX
// ============================================================

function loadPoItems(poId) {
    const banner = document.getElementById('poInfoBanner');
    const bannerText = document.getElementById('poBannerText');
    if (!banner) return;
    if (!poId) { banner.style.display = 'none'; return; }

    fetch(grConfig.poItemsUrl + '/' + poId)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            // Auto-fill supplier dari PO
            if (data.supplier_id && data.supplier_name) {
                setSupplierByName(data.supplier_name, data.supplier_id);
            } else if (data.supplier_id) {
                const sup = grSuppliers.find(function(s) { return s.id == data.supplier_id; });
                if (sup) setSupplierByName(sup.name, sup.id);
            }
            document.getElementById('itemsBody').innerHTML = '';
            rowCount = 0;
            if (data.items && data.items.length > 0) {
                data.items.forEach(function(item) {
                    addRow('material', {
                        material_id: item.material_id,
                        purchase_order_item_id: item.purchase_order_item_id,
                        qty: item.qty_remaining,
                        unit_abbr: item.unit_abbr,
                    });
                });
                banner.style.display = 'block';
                if (bannerText) bannerText.textContent = 'PO Terpilih: ' + data.items.length + ' item dari ' + (data.supplier_name || '-') + '. Silakan periksa kuantitas fisik sebelum menyimpan.';
            } else {
                banner.style.display = 'block';
                if (bannerText) bannerText.innerHTML = '<i class="fas fa-check-circle" style="color:#16a34a;"></i> Seluruh item pada PO ini telah diterima secara penuh.';
            }
        })
        .catch(function() { alert('Gagal memuat rincian item PO.'); });
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
            tr.innerHTML = '<td colspan="5" style="text-align:center;padding:36px 16px;background:#f8fafc;">'
                + '<i class="fas ' + icon + ' text-muted" style="font-size:28px;margin-bottom:8px;display:block;"></i>'
                + '<div style="font-weight:700;color:#334155;font-size:13.5px;margin-bottom:4px;">Belum Ada Item ' + label + ' Didaftarkan</div>'
                + '<p style="color:#64748b;font-size:12px;margin-bottom:12px;">Saat ini belum ada ' + label.toLowerCase() + ' pada sesi penerimaan ini.</p>'
                + '<button type="button" class="btn btn-sm ' + btnColor + '" onclick="' + addFn + '" style="border-radius:6px;font-size:12px;font-weight:600;padding:6px 16px;">'
                + '<i class="fas ' + icon + ' me-1"></i> + Tambah ' + label + '</button></td>';
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
    if (totalEl) totalEl.innerHTML = 'Total: <strong>' + countAll + '</strong> item (<span style="color:#2563eb;font-weight:600;">' + countMaterial + ' Material</span>, <span style="color:#d97706;font-weight:600;">' + countTool + ' Alat</span>)';
    filterItemsTab(currentTabFilter);
}

function recalcRow(idx) {
    updateGrandTotal();
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
    addRow('material');
    checkAndShowScheduledPrompt();
});
