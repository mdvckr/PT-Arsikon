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

    rows.forEach(function (row) {
        const rowType = row.getAttribute('data-type');
        const isTool = (rowType === 'tool');
        const sel = row.querySelector('.item-select');
        const stageInput = row.querySelector('[id^="stage-input-"]');
        const stageVal = stageInput ? stageInput.value.trim() : '';

        if (sel && sel.value) {
            const list = isTool ? grTools : grMaterials;
            const item = list.find(function (i) { return i.id == sel.value; });
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
        setTimeout(function () {
            el.focus();
            flashEl(el, '#bfdbfe');
        }, 250);
    }
}

function ensureUnitsDatalist() {
    // Datalist dinonaktifkan agar tidak memunculkan popup OS hitam bawaan browser
}

function checkEmptyState(customMessage) {
    const tbody = document.getElementById('itemsBody');
    if (!tbody) return;
    const rows = tbody.querySelectorAll('tr.item-row');
    const emptyRow = document.getElementById('emptyItemsRow');
    if (rows.length === 0) {
        if (!emptyRow) {
            const desc = customMessage || 'Tambahkan baris material atau alat langsung sesuai nota fisik / surat jalan yang diterima.';
            const html = '<tr id="emptyItemsRow" class="empty-receipt-row">'
                + '<td colspan="13" class="empty-receipt-canvas">'
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
                + '</div>'
                + '</td></tr>';
            tbody.innerHTML = html;
        }
    } else {
        if (emptyRow) emptyRow.remove();
    }
    updateGrandTotal();
}

function suggestCustomSku(name, isTool) {
    const clean = (name || '').replace(/[^a-zA-Z0-9]/g, '').toUpperCase();
    let prefix = isTool ? 'TL' : 'MAT';
    if (clean.length >= 3) {
        prefix = clean.substring(0, 3);
    }
    const randNum = Math.floor(100 + Math.random() * 900);
    return prefix + '-' + randNum;
}

function onItemNameInput(idx, val) {
    const row = document.getElementById('row-' + idx);
    if (!row) return;
    const isTool = row.getAttribute('data-type') === 'tool';
    const itemsList = isTool ? grTools : grMaterials;
    const clean = (val || '').trim().toLowerCase();

    const matIdInput = document.getElementById('material-id-' + idx);
    const toolIdInput = document.getElementById('tool-id-' + idx);
    const catSelect = document.getElementById('item-cat-' + idx);
    const unitInput = document.getElementById('item-unit-' + idx);
    const brandInput = document.getElementById('item-brand-' + idx);
    const skuInput = document.getElementById('sku-input-' + idx);
    const metaContainer = document.getElementById('item-meta-' + idx);

    if (!clean) {
        if (matIdInput) matIdInput.value = '';
        if (toolIdInput) toolIdInput.value = '';
        if (metaContainer) metaContainer.innerHTML = '';
        return;
    }

    // Cari item yang cocok di inventori gudang user sendiri
    const match = itemsList.find(function (item) {
        return (item.name || '').trim().toLowerCase() === clean;
    });

    if (match) {
        if (isTool) {
            if (toolIdInput) toolIdInput.value = match.id;
            if (matIdInput) matIdInput.value = '';
        } else {
            if (matIdInput) matIdInput.value = match.id;
            if (toolIdInput) toolIdInput.value = '';
        }

        if (catSelect && match.category_id) {
            catSelect.value = match.category_id;
        }
        if (unitInput) {
            unitInput.value = match.abbr || (isTool ? 'Unit' : 'Unit');
        }
        if (brandInput && !brandInput.value && match.brand) {
            brandInput.value = match.brand;
        }
        const sku = match.sku || match.code || '';
        if (skuInput) {
            skuInput.value = sku;
            skuInput.style.backgroundColor = '#f8fafc';
        }
        if (metaContainer) {
            metaContainer.innerHTML = '<span class="badge" style="background:#f1f5f9;color:#334155;border:1px solid #cbd5e1;font-weight:600;font-size:10px;"><i class="fas fa-warehouse me-1 text-primary"></i>Stok Gudang</span>';
        }
    } else {
        // Barang belum ada di inventori gudang -> didaftarkan sebagai item baru
        if (matIdInput) matIdInput.value = '';
        if (toolIdInput) toolIdInput.value = '';
        if (skuInput) {
            if (!skuInput.value || skuInput.value.startsWith('MAT-') || skuInput.value.startsWith('TL-')) {
                skuInput.value = suggestCustomSku(val, isTool);
            }
            skuInput.style.backgroundColor = '#ffffff';
        }
        if (metaContainer) {
            metaContainer.innerHTML = '<span class="badge" style="background:#fef3c7;color:#92400e;border:1px solid #fde68a;font-weight:600;font-size:10px;"><i class="fas fa-sparkles me-1 text-warning"></i>Barang Baru (Didaftarkan ke Sistem)</span>';
        }
    }
}

// ============================================================
// SUBGROUP (KELOMPOK) DROPDOWN HELPERS
// ============================================================

function getAvailableSubgroups(catId, isTool) {
    const typesSet = new Set();

    // 1. Data kelompok asli dari database (window.grMaterialGroups atau window.grToolGroups)
    const dbGroupsMap = isTool
        ? (typeof window.grToolGroups !== 'undefined' ? window.grToolGroups : (typeof grToolGroups !== 'undefined' ? grToolGroups : {}))
        : (typeof window.grMaterialGroups !== 'undefined' ? window.grMaterialGroups : (typeof grMaterialGroups !== 'undefined' ? grMaterialGroups : {}));
    const allDbGroups = isTool
        ? (typeof window.grAllToolGroups !== 'undefined' ? window.grAllToolGroups : (typeof grAllToolGroups !== 'undefined' ? grAllToolGroups : []))
        : (typeof window.grAllMaterialGroups !== 'undefined' ? window.grAllMaterialGroups : (typeof grAllMaterialGroups !== 'undefined' ? grAllMaterialGroups : []));

    if (catId && dbGroupsMap[catId] && Array.isArray(dbGroupsMap[catId])) {
        dbGroupsMap[catId].forEach(function (g) {
            if (g && typeof g === 'string' && g.trim()) typesSet.add(g.trim());
        });
    } else if (!catId && Array.isArray(allDbGroups)) {
        allDbGroups.forEach(function (g) {
            if (g && typeof g === 'string' && g.trim()) typesSet.add(g.trim());
        });
    }

    // 2. Kumpulkan juga tipe yang terdaftar pada array master item (grTools atau grMaterials)
    const list = isTool ? grTools : grMaterials;
    if (Array.isArray(list)) {
        list.forEach(function (i) {
            if (i.type && i.type.trim()) {
                if (!catId || i.category_id == catId) {
                    typesSet.add(i.type.trim());
                }
            }
        });
    }

    return Array.from(typesSet).sort(function (a, b) {
        return a.localeCompare(b, undefined, { sensitivity: 'base' });
    });
}

function renderUnitOptions(selectedUnit) {
    const units = (typeof window.grUnits !== 'undefined' && Array.isArray(window.grUnits))
        ? window.grUnits
        : ((typeof grUnits !== 'undefined' && Array.isArray(grUnits)) ? grUnits : []);

    let html = '<option value="">— Pilih Satuan —</option>';
    let matched = false;
    const normSelected = (selectedUnit || '').trim().toLowerCase();

    units.forEach(function (u) {
        const code = u.code || '';
        const name = u.name || '';
        const isSel = (normSelected && (code.toLowerCase() === normSelected || name.toLowerCase() === normSelected));
        if (isSel) matched = true;
        html += '<option value="' + escHtml(code) + '"' + (isSel ? ' selected' : '') + '>' + escHtml(name) + ' (' + escHtml(code) + ')</option>';
    });

    if (normSelected && !matched) {
        html += '<option value="' + escHtml(selectedUnit) + '" selected>' + escHtml(selectedUnit) + '</option>';
    }

    html += '<option value="__NEW_UNIT__" style="font-weight:700;color:#2563eb;">✨ ✚ Satuan Baru / Lainnya...</option>';
    return html;
}

function onCustomUnitChange(idx) {
    const sel = document.getElementById('item-unit-' + idx);
    if (!sel) return;
    const val = sel.value;

    const selectWrap = document.getElementById('unit-select-wrap-' + idx);
    const inputWrap = document.getElementById('unit-input-wrap-' + idx);
    const textInput = document.getElementById('item-unit-custom-input-' + idx);

    if (val === '__NEW_UNIT__') {
        if (selectWrap) selectWrap.style.display = 'none';
        if (inputWrap) inputWrap.style.display = 'flex';
        sel.removeAttribute('name');
        if (textInput) {
            textInput.name = 'items[' + idx + '][custom_unit]';
            textInput.value = '';
            textInput.focus();
        }
    } else {
        sel.name = 'items[' + idx + '][custom_unit]';
        if (textInput) {
            textInput.removeAttribute('name');
            textInput.value = val;
        }
    }
}

function revertUnitToDropdown(idx) {
    const selectWrap = document.getElementById('unit-select-wrap-' + idx);
    const inputWrap = document.getElementById('unit-input-wrap-' + idx);
    const sel = document.getElementById('item-unit-' + idx);
    const textInput = document.getElementById('item-unit-custom-input-' + idx);

    if (inputWrap) inputWrap.style.display = 'none';
    if (selectWrap) selectWrap.style.display = 'flex';

    if (textInput) textInput.removeAttribute('name');
    if (sel) {
        sel.name = 'items[' + idx + '][custom_unit]';
        sel.value = '';
    }
    syncAutoItemName(idx);
}

function syncAutoItemName(idx) {
    const nameInput = document.getElementById('item-name-' + idx);
    if (!nameInput) return;

    const brandInput = document.getElementById('item-brand-' + idx);
    const sizeInput = document.getElementById('item-size-' + idx);
    const unitSelect = document.getElementById('item-unit-' + idx);
    const unitCustomInput = document.getElementById('item-unit-custom-input-' + idx);
    const typeSelect = document.getElementById('item-type-select-' + idx);
    const typeInput = document.getElementById('item-type-input-' + idx);

    // Dapatkan baseName
    let baseName = nameInput.dataset.baseName;
    if (baseName == null) {
        const curVal = (nameInput.value || '').trim();
        if (curVal) {
            baseName = curVal;
        } else {
            const curType = (typeInput && typeInput.value) ? typeInput.value.trim() : ((typeSelect && typeSelect.value && typeSelect.value !== '__NEW_SUBGROUP__') ? typeSelect.value.trim() : '');
            baseName = curType;
        }
        nameInput.dataset.baseName = baseName;
    }

    // Ambil size dan unit (brand tidak diikutsertakan agar merek tidak mengubah nama barang)
    const size = sizeInput ? sizeInput.value.trim() : '';
    let unit = '';
    if (unitCustomInput && unitCustomInput.value.trim()) {
        unit = unitCustomInput.value.trim();
    } else if (unitSelect && unitSelect.value && unitSelect.value !== '__NEW_UNIT__') {
        unit = unitSelect.value.trim();
    }

    // Rakit Nama Barang lengkap: [baseName] [size] [unit]
    const parts = [];
    if (baseName) {
        parts.push(baseName);
    }
    if (size) {
        const lowerBase = (baseName || '').toLowerCase();
        const lowerSize = size.toLowerCase();
        if (!lowerBase.split(/\s+/).includes(lowerSize) && !lowerBase.endsWith(lowerSize)) {
            parts.push(size);
        }
    }
    if (unit) {
        const joinedSoFar = parts.join(' ').toLowerCase();
        const lowerUnit = unit.toLowerCase();
        if (!joinedSoFar.endsWith(lowerUnit) && !joinedSoFar.split(/\s+/).includes(lowerUnit)) {
            parts.push(unit);
        }
    }

    const fullName = parts.join(' ');
    if (fullName) {
        nameInput.value = fullName;
    }

    // Auto-update SKU jika masih pola default MAT- / TL-
    const skuInput = document.getElementById('sku-input-' + idx);
    if (skuInput && (!skuInput.value || skuInput.value.startsWith('MAT-') || skuInput.value.startsWith('TL-'))) {
        const isTool = (document.getElementById('item-type-' + idx)?.value === 'tool');
        skuInput.value = suggestCustomSku(fullName, isTool);
    }
}

function onCustomNameInput(idx) {
    const nameInput = document.getElementById('item-name-' + idx);
    if (!nameInput) return;

    const brandInput = document.getElementById('item-brand-' + idx);
    const sizeInput = document.getElementById('item-size-' + idx);
    const unitSelect = document.getElementById('item-unit-' + idx);
    const unitCustomInput = document.getElementById('item-unit-custom-input-' + idx);

    const size = sizeInput ? sizeInput.value.trim() : '';
    let unit = '';
    if (unitCustomInput && unitCustomInput.value.trim()) {
        unit = unitCustomInput.value.trim();
    } else if (unitSelect && unitSelect.value && unitSelect.value !== '__NEW_UNIT__') {
        unit = unitSelect.value.trim();
    }

    let val = nameInput.value;

    // Lepaskan akhiran unit atau size jika ada untuk mengekstrak baseName murni (brand tidak mempengaruhi nama)
    const suffixes = [
        [size, unit].filter(Boolean).join(' '),
        unit,
        size
    ].filter(Boolean);

    for (let s = 0; s < suffixes.length; s++) {
        const suf = suffixes[s];
        if (suf && val.toLowerCase().endsWith(suf.toLowerCase()) && val.length > suf.length) {
            val = val.slice(0, val.length - suf.length).trim();
            break;
        }
    }

    nameInput.dataset.baseName = val;
    nameInput.dataset.baseNameAuto = 'false';

    // Update SKU
    const skuInput = document.getElementById('sku-input-' + idx);
    if (skuInput && (!skuInput.value || skuInput.value.startsWith('MAT-') || skuInput.value.startsWith('TL-'))) {
        const isTool = (document.getElementById('item-type-' + idx)?.value === 'tool');
        skuInput.value = suggestCustomSku(nameInput.value, isTool);
    }
}

function onSubgroupTextInput(idx) {
    const textInput = document.getElementById('item-type-input-' + idx);
    const nameInput = document.getElementById('item-name-' + idx);
    if (textInput && nameInput) {
        if (!nameInput.dataset.baseName || nameInput.dataset.baseNameAuto === 'true') {
            nameInput.dataset.baseName = textInput.value;
            nameInput.dataset.baseNameAuto = 'true';
            syncAutoItemName(idx);
        }
    }
}

function onSubgroupSelectChange(idx, isTool) {
    const sel = document.getElementById('item-type-select-' + idx);
    if (!sel) return;
    const val = sel.value;

    const selectWrap = document.getElementById('subgroup-select-wrap-' + idx);
    const inputWrap = document.getElementById('subgroup-input-wrap-' + idx);
    const textInput = document.getElementById('item-type-input-' + idx);

    if (val === '__NEW_SUBGROUP__') {
        if (selectWrap) selectWrap.style.display = 'none';
        if (inputWrap) inputWrap.style.display = 'flex';
        sel.removeAttribute('name');
        if (textInput) {
            textInput.name = 'items[' + idx + '][type]';
            textInput.value = '';
            textInput.focus();
        }
    } else {
        sel.name = 'items[' + idx + '][type]';
        if (textInput) {
            textInput.removeAttribute('name');
            textInput.value = val;
        }
    }

    const nameInput = document.getElementById('item-name-' + idx);
    if (nameInput) {
        if (!nameInput.dataset.baseName || nameInput.dataset.baseNameAuto === 'true') {
            nameInput.dataset.baseName = (val !== '__NEW_SUBGROUP__') ? val : '';
            nameInput.dataset.baseNameAuto = 'true';
            syncAutoItemName(idx);
        }
    }
}

function revertSubgroupToDropdown(idx, isTool) {
    const selectWrap = document.getElementById('subgroup-select-wrap-' + idx);
    const inputWrap = document.getElementById('subgroup-input-wrap-' + idx);
    const sel = document.getElementById('item-type-select-' + idx);
    const textInput = document.getElementById('item-type-input-' + idx);

    if (inputWrap) inputWrap.style.display = 'none';
    if (selectWrap) selectWrap.style.display = 'flex';

    if (textInput) textInput.removeAttribute('name');
    if (sel) {
        sel.name = 'items[' + idx + '][type]';
        sel.value = '';
    }
}

function onCustomCategoryChange(idx, isTool) {
    const catSelect = document.getElementById('item-cat-' + idx);
    if (!catSelect) return;
    const catVal = catSelect.value;

    const catSelectWrap = document.getElementById('cat-select-wrap-' + idx);
    const catInputWrap = document.getElementById('cat-input-wrap-' + idx);
    const catTextInput = document.getElementById('cat-custom-input-' + idx);

    if (catVal === '__NEW_CATEGORY__') {
        if (catSelectWrap) catSelectWrap.style.display = 'none';
        if (catInputWrap) catInputWrap.style.display = 'flex';
        catSelect.removeAttribute('name');
        if (catTextInput) {
            catTextInput.name = 'items[' + idx + '][category_name]';
            catTextInput.value = '';
            catTextInput.focus();
        }
    } else {
        catSelect.name = 'items[' + idx + '][category_id]';
        if (catTextInput) {
            catTextInput.removeAttribute('name');
            catTextInput.value = '';
        }
    }

    const subgroupSelect = document.getElementById('item-type-select-' + idx);
    if (!subgroupSelect) return;

    const catId = (catVal !== '__NEW_CATEGORY__') ? catVal : null;
    const curVal = subgroupSelect.value;
    const groups = getAvailableSubgroups(catId, isTool);

    let html = '<option value="">— Pilih Kelompok —</option>';
    groups.forEach(function (g) {
        const isSel = (curVal && curVal.toLowerCase() === g.toLowerCase()) ? ' selected' : '';
        html += '<option value="' + escHtml(g) + '"' + isSel + '>' + escHtml(g) + '</option>';
    });
    html += '<option value="__NEW_SUBGROUP__" style="font-weight:700;color:#2563eb;">✨ + Ketik Kelompok Baru...</option>';
    subgroupSelect.innerHTML = html;
}

function revertCategoryToDropdown(idx, isTool) {
    const catSelectWrap = document.getElementById('cat-select-wrap-' + idx);
    const catInputWrap = document.getElementById('cat-input-wrap-' + idx);
    const catSelect = document.getElementById('item-cat-' + idx);
    const catTextInput = document.getElementById('cat-custom-input-' + idx);

    if (catInputWrap) catInputWrap.style.display = 'none';
    if (catSelectWrap) catSelectWrap.style.display = 'flex';

    if (catTextInput) catTextInput.removeAttribute('name');
    if (catSelect) {
        catSelect.name = 'items[' + idx + '][category_id]';
        catSelect.value = '';
    }
    onCustomCategoryChange(idx, isTool);
}

function onMasterCategoryFilterChange(idx, isTool) {
    const catSel = document.getElementById('master-cat-filter-' + idx);
    const catId = catSel ? catSel.value : '';

    if (catId === '__NEW_CATEGORY__') {
        switchRowToCustom(idx, isTool, { category_id: '__NEW_CATEGORY__' });
        return;
    }

    const catInput = document.getElementById('cat-input-' + idx);
    if (catInput) catInput.value = catId;

    // Perbarui pilihan dropdown Kelompok sesuai kategori yang dipilih
    const subgroupSel = document.getElementById('master-subgroup-filter-' + idx);
    if (subgroupSel) {
        const groups = getAvailableSubgroups(catId, isTool);
        let sHtml = '<option value="">— Semua Kelompok —</option>';
        groups.forEach(function (g) {
            sHtml += '<option value="' + escHtml(g) + '">' + escHtml(g) + '</option>';
        });
        sHtml += '<option value="__NEW_SUBGROUP__" style="font-weight:700;color:#2563eb;">✨ + Ketik Kelompok Baru...</option>';
        subgroupSel.innerHTML = sHtml;
        subgroupSel.value = '';
    }
    const typeInput = document.getElementById('item-type-input-' + idx);
    if (typeInput) typeInput.value = '';

    filterMasterItemDropdown(idx, isTool);
}

function onMasterSubgroupFilterChange(idx, isTool) {
    const subgroupSel = document.getElementById('master-subgroup-filter-' + idx);
    const subType = subgroupSel ? subgroupSel.value : '';

    if (subType === '__NEW_SUBGROUP__') {
        const catSel = document.getElementById('master-cat-filter-' + idx);
        const catId = catSel ? catSel.value : '';
        switchRowToCustom(idx, isTool, { category_id: catId, type: '__NEW_SUBGROUP__' });
        return;
    }

    const typeInput = document.getElementById('item-type-input-' + idx);
    if (typeInput) typeInput.value = subType;

    filterMasterItemDropdown(idx, isTool);
}

function filterMasterItemDropdown(idx, isTool) {
    const itemSelect = document.getElementById('item-select-' + idx);
    if (!itemSelect) return;

    const catSel = document.getElementById('master-cat-filter-' + idx);
    const subgroupSel = document.getElementById('master-subgroup-filter-' + idx);

    const catId = catSel ? catSel.value : '';
    const subType = subgroupSel ? subgroupSel.value.trim().toLowerCase() : '';

    const itemsList = isTool ? grTools : grMaterials;
    const currentItemId = itemSelect.value;

    const categories = {};
    itemsList.forEach(function (item) {
        if (catId && item.category_id != catId) return;
        if (subType && (item.type || '').trim().toLowerCase() !== subType) return;

        const cat = item.category_name || (isTool ? 'Alat / Mesin' : 'Lainnya');
        const sub = item.type || 'Umum';
        const groupKey = cat + ' ➔ ' + sub;
        if (!categories[groupKey]) categories[groupKey] = [];
        categories[groupKey].push(item);
    });

    let html = '<option value="">-- Pilih ' + (isTool ? 'Alat' : 'Material') + ' --</option>';
    html += '<option value="__NEW_CUSTOM_ITEM__" style="font-weight:700;color:#2563eb;">✨ + Input ' + (isTool ? 'Alat' : 'Material') + ' Baru (Manual)...</option>';

    for (const groupKey in categories) {
        html += '<optgroup label="' + escHtml(groupKey) + '">';
        categories[groupKey].forEach(function (item) {
            const isSelected = (currentItemId == item.id);
            const brandText = item.brand ? ' · ' + item.brand : '';
            const sizeText = item.size ? ' (' + item.size + ')' : '';
            const codeText = (item.sku || item.code) ? ' [' + (item.sku || item.code) + ']' : '';
            const stageBadge = (item.planned_stages && item.planned_stages.length > 0) ? ' 📅' : '';
            html += '<option value="' + item.id + '"' + (isSelected ? ' selected' : '') + '>'
                + escHtml(item.name) + escHtml(sizeText) + escHtml(brandText) + escHtml(codeText) + stageBadge
                + '</option>';
        });
        html += '</optgroup>';
    }

    itemSelect.innerHTML = html;
}

function toggleRowType(idx) {
    const row = document.getElementById('row-' + idx);
    if (!row) return;
    const currentType = row.getAttribute('data-type');
    const newType = currentType === 'tool' ? 'material' : 'tool';
    const isTool = newType === 'tool';

    row.setAttribute('data-type', newType);

    const typeInput = document.getElementById('item-type-' + idx);
    if (typeInput) typeInput.value = newType;

    const badgeBtn = document.getElementById('type-badge-' + idx);
    if (badgeBtn) {
        if (isTool) {
            badgeBtn.style.background = '#fffbeb';
            badgeBtn.style.color = '#b45309';
            badgeBtn.style.borderColor = '#fde68a';
            badgeBtn.title = 'Klik untuk ubah ke Material';
            badgeBtn.innerHTML = '<i class="fas fa-helmet-safety"></i> Alat';
        } else {
            badgeBtn.style.background = '#eff6ff';
            badgeBtn.style.color = '#1d4ed8';
            badgeBtn.style.borderColor = '#bfdbfe';
            badgeBtn.title = 'Klik untuk ubah ke Alat';
            badgeBtn.innerHTML = '<i class="fas fa-box"></i> Material';
        }
    }

    // Periksa apakah baris dalam mode custom atau mode select normal
    const selectEl = document.getElementById('item-select-' + idx);
    if (selectEl) {
        // Mode Select Normal: Update pilihan item sesuai tipe baru
        const itemsList = isTool ? grTools : grMaterials;
        const categories = {};
        itemsList.forEach(function (item) {
            const cat = item.category_name || (isTool ? 'Alat / Mesin' : 'Lainnya');
            const sub = item.type || 'Umum';
            const groupKey = cat + ' ➔ ' + sub;
            if (!categories[groupKey]) categories[groupKey] = [];
            categories[groupKey].push(item);
        });

        let selectOptionsHtml = '<option value="">-- Pilih ' + (isTool ? 'Alat' : 'Material') + ' --</option>';
        selectOptionsHtml += '<option value="__NEW_CUSTOM_ITEM__" style="font-weight:700;color:#2563eb;">✨ + Input ' + (isTool ? 'Alat' : 'Material') + ' Baru (Manual)...</option>';

        for (const groupKey in categories) {
            selectOptionsHtml += '<optgroup label="' + escHtml(groupKey) + '">';
            categories[groupKey].forEach(function (item) {
                const brandText = item.brand ? ' · ' + item.brand : '';
                const sizeText = item.size ? ' (' + item.size + ')' : '';
                const codeText = (item.sku || item.code) ? ' [' + (item.sku || item.code) + ']' : '';
                const stageBadge = (item.planned_stages && item.planned_stages.length > 0) ? ' 📅' : '';
                selectOptionsHtml += '<option value="' + item.id + '">'
                    + escHtml(item.name) + escHtml(sizeText) + escHtml(brandText) + escHtml(codeText) + stageBadge
                    + '</option>';
            });
            selectOptionsHtml += '</optgroup>';
        }

        selectEl.name = 'items[' + idx + '][' + (isTool ? 'tool_id' : 'material_id') + ']';
        selectEl.innerHTML = selectOptionsHtml;
        selectEl.value = '';

        const skuBadge = document.getElementById('sku-badge-' + idx);
        const skuInput = document.getElementById('sku-input-' + idx);
        const catBadge = document.getElementById('cat-badge-' + idx);
        const catInput = document.getElementById('cat-input-' + idx);
        const subgroupBadge = document.getElementById('subgroup-badge-' + idx);
        const subgroupInput = document.getElementById('item-type-input-' + idx);
        const sizeBadge = document.getElementById('size-badge-' + idx);
        const sizeInput = document.getElementById('item-size-' + idx);
        const brandBadge = document.getElementById('brand-badge-' + idx);
        const brandInput = document.getElementById('item-brand-' + idx);
        const unitEl = document.getElementById('unit-' + idx);
        const unitInput = document.getElementById('item-unit-' + idx);
        const metaContainer = document.getElementById('item-meta-' + idx);

        const catFilter = document.getElementById('master-cat-filter-' + idx);
        if (catFilter) {
            const catList = isTool
                ? (typeof grToolCategories !== 'undefined' ? grToolCategories : (window.grToolCategories || []))
                : (typeof grMaterialCategories !== 'undefined' ? grMaterialCategories : (window.grMaterialCategories || []));
            let catOptions = '<option value="">— Semua Kategori —</option>';
            catList.forEach(function (cat) {
                catOptions += '<option value="' + cat.id + '">' + escHtml(cat.name) + '</option>';
            });
            catOptions += '<option value="__NEW_CATEGORY__" style="font-weight:700;color:#2563eb;">✨ + Ketik Kategori Baru...</option>';
            catFilter.innerHTML = catOptions;
            catFilter.value = '';
        }
        const subgroupFilter = document.getElementById('master-subgroup-filter-' + idx);
        if (subgroupFilter) {
            const groups = getAvailableSubgroups(null, isTool);
            let sHtml = '<option value="">— Semua Kelompok —</option>';
            groups.forEach(function (g) {
                sHtml += '<option value="' + escHtml(g) + '">' + escHtml(g) + '</option>';
            });
            sHtml += '<option value="__NEW_SUBGROUP__" style="font-weight:700;color:#2563eb;">✨ + Ketik Kelompok Baru...</option>';
            subgroupFilter.innerHTML = sHtml;
            subgroupFilter.value = '';
        }

        if (skuBadge) skuBadge.textContent = '-';
        if (skuInput) skuInput.value = '';
        if (catBadge) catBadge.innerHTML = '<span class="badge" style="background:#f8fafc;border:1px solid #e2e8f0;color:#334155;font-weight:600;padding:5px 9px;border-radius:6px;font-size:11.5px;display:inline-block;">-</span>';
        if (catInput) catInput.value = '';
        if (subgroupBadge) subgroupBadge.innerHTML = '<span class="badge" style="background:#f8fafc;border:1px solid #e2e8f0;color:#475569;font-weight:600;padding:5px 9px;border-radius:6px;font-size:11.5px;display:inline-block;">-</span>';
        if (subgroupInput) subgroupInput.value = '';
        if (sizeBadge) sizeBadge.innerHTML = '<span class="badge" style="background:#f8fafc;border:1px solid #e2e8f0;color:#475569;font-weight:600;padding:5px 9px;border-radius:6px;font-size:11.5px;display:inline-block;">-</span>';
        if (sizeInput) sizeInput.value = '';
        if (brandBadge) brandBadge.innerHTML = '<span class="badge" style="background:#f8fafc;border:1px solid #e2e8f0;color:#475569;font-weight:600;padding:5px 9px;border-radius:6px;font-size:11.5px;display:inline-block;">-</span>';
        if (brandInput) brandInput.value = '';
        if (unitEl) unitEl.textContent = isTool ? 'Unit' : '-';
        if (unitInput) unitInput.value = isTool ? 'Unit' : '';
        if (metaContainer) metaContainer.innerHTML = '';
    } else {
        // Mode Custom: Update Kategori & SKU Placeholder
        const catList = isTool
            ? (typeof grToolCategories !== 'undefined' ? grToolCategories : (window.grToolCategories || []))
            : (typeof grMaterialCategories !== 'undefined' ? grMaterialCategories : (window.grMaterialCategories || []));
        const catSelect = document.getElementById('item-cat-' + idx);
        if (catSelect) {
            let catOptions = '<option value="">— Pilih Kategori —</option>';
            catList.forEach(function (cat) {
                catOptions += '<option value="' + cat.id + '">' + escHtml(cat.name) + '</option>';
            });
            catOptions += '<option value="__NEW_CATEGORY__" style="font-weight:700;color:#2563eb;">✨ + Ketik Kategori Baru...</option>';
            catSelect.innerHTML = catOptions;
            catSelect.value = '';
            onCustomCategoryChange(idx, isTool);
        }
        const skuInput = document.getElementById('sku-input-' + idx);
        if (skuInput) {
            skuInput.placeholder = isTool ? 'mis. TL-001' : 'mis. MAT-001';
            const nameInput = document.getElementById('item-name-' + idx);
            if (nameInput && nameInput.value) {
                skuInput.value = suggestCustomSku(nameInput.value, isTool);
            }
        }
        const unitSelect = document.getElementById('item-unit-' + idx);
        if (unitSelect && isTool) {
            unitSelect.value = 'Unit';
        }
    }

    // Qty adjustments
    const qtyInput = document.getElementById('item-qty-' + idx);
    if (qtyInput) {
        qtyInput.min = isTool ? '1' : '0.01';
        qtyInput.step = isTool ? '1' : 'any';
        if (isTool) validateQtyInput(qtyInput, true);
    }
    const shippedInput = document.getElementById('item-shipped-' + idx);
    if (shippedInput) {
        shippedInput.min = isTool ? '1' : '0.01';
        shippedInput.step = isTool ? '1' : 'any';
        if (isTool) validateQtyInput(shippedInput, true);
    }

    checkAndUpdateWarehouseLock();
    updateGrandTotal();
}

function switchRowToCustom(idx, isTool, initialData) {
    initialData = initialData || {};
    const row = document.getElementById('row-' + idx);
    if (!row) return;

    const skuCell = row.querySelector('.col-sku');
    const catCell = row.querySelector('.col-cat');
    const subgroupCell = row.querySelector('.col-subgroup');
    const itemCell = row.querySelector('.col-item');
    const sizeCell = row.querySelector('.col-size');
    const brandCell = row.querySelector('.col-brand');
    const unitCell = row.querySelector('.col-unit');

    const catList = isTool
        ? (typeof grToolCategories !== 'undefined' ? grToolCategories : (window.grToolCategories || []))
        : (typeof grMaterialCategories !== 'undefined' ? grMaterialCategories : (window.grMaterialCategories || []));
    let catOptions = '<option value="">— Pilih Kategori —</option>';
    catList.forEach(function (cat) {
        const isSel = (initialData.category_id && cat.id == initialData.category_id) ? ' selected' : '';
        catOptions += '<option value="' + cat.id + '"' + isSel + '>' + escHtml(cat.name) + '</option>';
    });
    catOptions += '<option value="__NEW_CATEGORY__" style="font-weight:700;color:#2563eb;">✨ + Ketik Kategori Baru...</option>';

    const curUnit = initialData.custom_unit || (isTool ? 'UNIT' : '');
    const unitOptions = renderUnitOptions(curUnit);

    if (!initialData.category_id) {
        const catFilter = document.getElementById('master-cat-filter-' + idx);
        if (catFilter && catFilter.value) initialData.category_id = catFilter.value;
    }
    if (!initialData.type) {
        const subgroupFilter = document.getElementById('master-subgroup-filter-' + idx);
        if (subgroupFilter && subgroupFilter.value) initialData.type = subgroupFilter.value;
    }

    const initSku = initialData.custom_sku || suggestCustomSku(initialData.name || '', isTool);
    const initType = initialData.type || initialData.custom_type || '';
    const initBrand = initialData.brand || initialData.custom_brand || '';

    // Col 2: SKU
    if (skuCell) {
        skuCell.innerHTML = '<input type="text" name="items[' + idx + '][custom_sku]" id="sku-input-' + idx + '" class="form-control text-uppercase text-center" '
            + 'placeholder="' + (isTool ? 'TL-001' : 'MAT-001') + '" value="' + escHtml(initSku) + '" '
            + 'style="font-family:monospace;font-weight:700;font-size:12px;height:38px;border-radius:7px;background:#ffffff;" '
            + 'title="Kode Material / Alat">';
    }

    // Col 3: Kategori (Bisa Pilih Dropdown atau Ketik Baru)
    const showCustomCatInput = (initialData.category_id === '__NEW_CATEGORY__' || Boolean(initialData.category_name));
    const initCatName = (initialData.category_id === '__NEW_CATEGORY__') ? '' : (initialData.category_name || '');
    if (catCell) {
        catCell.innerHTML = '<div id="cat-wrapper-' + idx + '">'
            + '<div id="cat-select-wrap-' + idx + '" style="display:' + (showCustomCatInput ? 'none' : 'flex') + ';align-items:center;gap:4px;">'
            + '<select ' + (showCustomCatInput ? '' : 'name="items[' + idx + '][category_id]" ') + 'id="item-cat-' + idx + '" class="form-control" onchange="onCustomCategoryChange(' + idx + ', ' + (isTool ? 'true' : 'false') + ')" ' + (showCustomCatInput ? '' : 'required ') + 'style="font-size:11.5px;height:38px;border-radius:7px;">'
            + catOptions + '</select>'
            + '</div>'
            + '<div id="cat-input-wrap-' + idx + '" style="display:' + (showCustomCatInput ? 'flex' : 'none') + ';align-items:center;gap:4px;">'
            + '<input type="text" ' + (showCustomCatInput ? 'name="items[' + idx + '][category_name]" ' : '') + 'id="cat-custom-input-' + idx + '" class="form-control" '
            + 'placeholder="Kategori baru..." value="' + escHtml(initCatName) + '" '
            + 'style="font-size:11.5px;height:38px;border-radius:7px;" title="Kategori Baru">'
            + '<button type="button" class="btn btn-sm btn-light border" onclick="revertCategoryToDropdown(' + idx + ', ' + (isTool ? 'true' : 'false') + ')" title="Pilih dari Daftar Kategori" style="font-size:11px;height:38px;padding:0 8px;border-radius:7px;color:#64748b;"><i class="fas fa-list"></i></button>'
            + '</div>'
            + '</div>';
    }

    // Col 4: Kelompok (Bisa Pilih Dropdown atau Ketik Baru)
    if (subgroupCell) {
        const catIdForSub = (initialData.category_id !== '__NEW_CATEGORY__') ? initialData.category_id : null;
        const availableSubgroups = getAvailableSubgroups(catIdForSub, isTool);
        let subgroupSelectOpts = '<option value="">— Pilih Kelompok —</option>';
        let isKnownSubgroup = false;
        availableSubgroups.forEach(function (g) {
            const isSel = (initType && initType !== '__NEW_SUBGROUP__' && initType.toLowerCase() === g.toLowerCase());
            if (isSel) isKnownSubgroup = true;
            subgroupSelectOpts += '<option value="' + escHtml(g) + '"' + (isSel ? ' selected' : '') + '>' + escHtml(g) + '</option>';
        });
        subgroupSelectOpts += '<option value="__NEW_SUBGROUP__" style="font-weight:700;color:#2563eb;">✨ + Ketik Kelompok Baru...</option>';

        const showCustomSubgroup = (initType === '__NEW_SUBGROUP__' || (initType && !isKnownSubgroup));
        const initSubVal = (initType === '__NEW_SUBGROUP__') ? '' : initType;

        subgroupCell.innerHTML = '<div id="subgroup-wrapper-' + idx + '">'
            + '<div id="subgroup-select-wrap-' + idx + '" style="display:' + (showCustomSubgroup ? 'none' : 'flex') + ';align-items:center;gap:4px;">'
            + '<select ' + (showCustomSubgroup ? '' : 'name="items[' + idx + '][type]" ') + 'id="item-type-select-' + idx + '" class="form-control" onchange="onSubgroupSelectChange(' + idx + ', ' + (isTool ? 'true' : 'false') + ')" style="font-size:11.5px;height:38px;border-radius:7px;">'
            + subgroupSelectOpts
            + '</select>'
            + '</div>'
            + '<div id="subgroup-input-wrap-' + idx + '" style="display:' + (showCustomSubgroup ? 'flex' : 'none') + ';align-items:center;gap:4px;">'
            + '<input type="text" ' + (showCustomSubgroup ? 'name="items[' + idx + '][type]" ' : '') + 'id="item-type-input-' + idx + '" class="form-control" '
            + 'placeholder="mis. Bata, Semen..." value="' + escHtml(initSubVal) + '" '
            + 'oninput="onSubgroupTextInput(' + idx + ')" '
            + 'style="font-size:11.5px;height:38px;border-radius:7px;" title="Kelompok Barang">'
            + '<button type="button" class="btn btn-sm btn-light border" onclick="revertSubgroupToDropdown(' + idx + ', ' + (isTool ? 'true' : 'false') + ')" title="Pilih dari Dropdown" style="font-size:11px;height:38px;padding:0 8px;border-radius:7px;color:#64748b;"><i class="fas fa-list"></i></button>'
            + '</div>'
            + '</div>';
    }

    // Col 5: Nama Barang
    if (itemCell) {
        itemCell.innerHTML = '<div style="display:flex;align-items:center;gap:6px;">'
            + '<input type="text" name="items[' + idx + '][name]" id="item-name-' + idx + '" class="form-control" '
            + 'placeholder="Ketik nama barang baru..." value="' + escHtml(initialData.name || '') + '" required '
            + 'data-base-name="' + escHtml(initialData.name || '') + '" '
            + 'style="font-weight:600;font-size:12.5px;height:38px;border-radius:7px;" '
            + 'oninput="onCustomNameInput(' + idx + ');">'
            + '<button type="button" class="btn btn-sm btn-light border" onclick="revertRowToStandard(' + idx + ', ' + (isTool ? 'true' : 'false') + ')" title="Kembali ke Pilih dari Inventori" style="font-size:11px;height:38px;padding:0 8px;border-radius:7px;color:#64748b;"><i class="fas fa-rotate-left"></i></button>'
            + '</div>'
            + '<div class="mt-1" style="display:flex;align-items:center;gap:6px;">'
            + '</div>'
            + '<input type="hidden" name="items[' + idx + '][' + (isTool ? 'tool_id' : 'material_id') + ']" value="">';
    }

    // Col 6: Ukuran / Spesifikasi
    if (sizeCell) {
        const initSize = initialData.size || initialData.custom_size || initialData.specification || '';
        sizeCell.innerHTML = '<input type="text" name="items[' + idx + '][size]" id="item-size-' + idx + '" class="form-control" '
            + 'placeholder="mis. 10mm, 60x60..." value="' + escHtml(initSize) + '" '
            + 'oninput="syncAutoItemName(' + idx + ')" '
            + 'style="font-size:11.5px;height:38px;border-radius:7px;" title="Ukuran / Spesifikasi Barang">';
    }

    // Col 7: Merek
    if (brandCell) {
        brandCell.innerHTML = '<input type="text" name="items[' + idx + '][brand]" id="item-brand-' + idx + '" class="form-control" '
            + 'placeholder="Merek..." value="' + escHtml(initBrand) + '" '
            + 'style="font-size:11.5px;height:38px;border-radius:7px;" title="Merek Barang">';
    }

    // Col 7: Satuan
    if (unitCell) {
        unitCell.innerHTML = '<div id="unit-wrapper-' + idx + '">'
            + '<div id="unit-select-wrap-' + idx + '" style="display:flex;align-items:center;gap:4px;">'
            + '<select name="items[' + idx + '][custom_unit]" id="item-unit-' + idx + '" class="form-control form-select text-center" required '
            + 'onchange="onCustomUnitChange(' + idx + '); syncAutoItemName(' + idx + ');" '
            + 'style="font-size:11.5px;font-weight:700;height:38px;border-radius:7px;background:#ffffff;border:1.5px solid #cbd5e1;">'
            + unitOptions + '</select>'
            + '</div>'
            + '<div id="unit-input-wrap-' + idx + '" style="display:none;align-items:center;gap:4px;">'
            + '<input type="text" id="item-unit-custom-input-' + idx + '" class="form-control text-center" placeholder="Satuan baru..." '
            + 'oninput="syncAutoItemName(' + idx + ')" '
            + 'style="font-size:11.5px;font-weight:700;height:38px;border-radius:7px;" title="Satuan Baru">'
            + '<button type="button" class="btn btn-sm btn-light border" onclick="revertUnitToDropdown(' + idx + ')" title="Pilih dari Daftar Satuan" style="font-size:11px;height:38px;padding:0 8px;border-radius:7px;color:#64748b;"><i class="fas fa-list"></i></button>'
            + '</div>'
            + '</div>';
    }

    if (showCustomCatInput) {
        setTimeout(function () {
            const el = document.getElementById('cat-custom-input-' + idx);
            if (el) el.focus();
        }, 50);
    } else if (showCustomSubgroup) {
        setTimeout(function () {
            const el = document.getElementById('item-type-input-' + idx);
            if (el) el.focus();
        }, 50);
    } else {
        setTimeout(function () {
            const el = document.getElementById('item-name-' + idx);
            if (el) el.focus();
        }, 50);
    }
}

function revertRowToStandard(idx, isTool) {
    const row = document.getElementById('row-' + idx);
    if (!row) return;
    row.remove();
    addRow(isTool ? 'tool' : 'material');
}

function addRow(type, opts) {
    type = type || 'material';
    opts = opts || {};

    const emptyRow = document.getElementById('emptyItemsRow');
    if (emptyRow) emptyRow.remove();

    const idx = rowCount++;
    const isTool = (type === 'tool' || opts.item_type === 'tool');
    const itemsList = isTool ? grTools : grMaterials;
    const catList = isTool
        ? (typeof grToolCategories !== 'undefined' ? grToolCategories : (window.grToolCategories || []))
        : (typeof grMaterialCategories !== 'undefined' ? grMaterialCategories : (window.grMaterialCategories || []));

    // Resolve target master item
    let targetId = isTool ? opts.tool_id : opts.material_id;
    let foundMasterItem = null;
    if (targetId) {
        foundMasterItem = itemsList.find(function (i) { return i.id == targetId; });
    }
    const initialName = opts.name || opts.material_name || opts.custom_item_name || (foundMasterItem ? foundMasterItem.name : '');
    if (!foundMasterItem && initialName) {
        foundMasterItem = itemsList.find(function (i) {
            return (i.name || '').trim().toLowerCase() === initialName.trim().toLowerCase();
        });
        if (foundMasterItem) {
            targetId = foundMasterItem.id;
        }
    }

    const isCustom = Boolean(opts.is_custom || (!targetId && (opts.name || opts.custom_item_name)) || (!foundMasterItem && opts.custom_sku));

    const initialCatId = opts.category_id || (foundMasterItem ? (foundMasterItem.category_id || '') : '');
    const initialCatName = (foundMasterItem ? (foundMasterItem.category_name || '') : '') || (catList.find(function (c) { return c.id == initialCatId; })?.name || '-');
    const initialType = opts.type || opts.custom_type || (foundMasterItem ? (foundMasterItem.type || '') : '');
    const initialBrand = opts.brand || opts.custom_brand || (foundMasterItem ? (foundMasterItem.brand || '') : '');
    const initialSize = opts.size || opts.custom_size || opts.specification || (foundMasterItem ? (foundMasterItem.size || '') : '');
    const initialUnit = opts.custom_unit || opts.unit || opts.unit_abbr || opts.abbr || (foundMasterItem ? (foundMasterItem.abbr || (isTool ? 'UNIT' : '')) : (isTool ? 'UNIT' : ''));
    let initialQty = (opts.quantity != null) ? opts.quantity : ((opts.qty_received != null) ? opts.qty_received : ((opts.qty != null) ? opts.qty : 0));
    if (isTool && initialQty !== '' && initialQty !== null) {
        const numQty = parseFloat(initialQty);
        initialQty = (isNaN(numQty) || numQty < 1) ? 1 : Math.round(numQty);
    }
    let initialShipped = (opts.qty_shipped != null && opts.qty_shipped !== '') ? opts.qty_shipped : initialQty;
    if (isTool && initialShipped !== '' && initialShipped !== null) {
        const numShip = parseFloat(initialShipped);
        initialShipped = (isNaN(numShip) || numShip < 1) ? 1 : Math.round(numShip);
    }
    let initialMissing = (opts.qty_missing != null && opts.qty_missing !== '') ? parseFloat(opts.qty_missing) : 0;
    if (initialShipped !== '' && initialQty !== '' && (opts.qty_missing == null || opts.qty_missing === '')) {
        initialMissing = Math.max(0, (parseFloat(initialShipped) || 0) - (parseFloat(initialQty) || 0));
    }
    let initialAction = opts.missing_action || ((initialMissing > 0) ? 'pending' : 'none');
    if (initialMissing <= 0) {
        initialAction = 'none';
    }
    const initialNotes = opts.notes || '';
    const initialSku = foundMasterItem ? (foundMasterItem.sku || foundMasterItem.code || '-') : (opts.sku || opts.custom_sku || suggestCustomSku(initialName, isTool));
    const stageRef = opts.stage_reference || '';

    // Group items by category and subgroup (type) for the select dropdown
    const categories = {};
    itemsList.forEach(function (item) {
        const cat = item.category_name || (isTool ? 'Alat / Mesin' : 'Lainnya');
        const sub = item.type || 'Umum';
        const groupKey = cat + ' ➔ ' + sub;
        if (!categories[groupKey]) categories[groupKey] = [];
        categories[groupKey].push(item);
    });

    let selectOptionsHtml = '<option value="">-- Pilih ' + (isTool ? 'Alat' : 'Material') + ' --</option>';
    selectOptionsHtml += '<option value="__NEW_CUSTOM_ITEM__" style="font-weight:700;color:#2563eb;">✨ + Input ' + (isTool ? 'Alat' : 'Material') + ' Baru (Manual)...</option>';

    for (const groupKey in categories) {
        selectOptionsHtml += '<optgroup label="' + escHtml(groupKey) + '">';
        categories[groupKey].forEach(function (item) {
            const isSelected = (!isCustom && (targetId == item.id));
            const brandText = item.brand ? ' · ' + item.brand : '';
            const sizeText = item.size ? ' (' + item.size + ')' : '';
            const codeText = (item.sku || item.code) ? ' [' + (item.sku || item.code) + ']' : '';
            const stageBadge = (item.planned_stages && item.planned_stages.length > 0) ? ' 📅' : '';
            selectOptionsHtml += '<option value="' + item.id + '"' + (isSelected ? ' selected' : '') + '>'
                + escHtml(item.name) + escHtml(sizeText) + escHtml(brandText) + escHtml(codeText) + stageBadge
                + '</option>';
        });
        selectOptionsHtml += '</optgroup>';
    }

    // Type Badge button
    const typeBadgeHtml = isTool
        ? '<button type="button" class="btn btn-sm" onclick="toggleRowType(' + idx + ')" id="type-badge-' + idx + '" style="background:#fffbeb;color:#b45309;border:1px solid #fde68a;font-weight:700;font-size:11px;padding:4px 8px;border-radius:6px;display:inline-flex;align-items:center;gap:4px;cursor:pointer;" title="Klik untuk ubah ke Material"><i class="fas fa-helmet-safety"></i> Alat</button>'
        : '<button type="button" class="btn btn-sm" onclick="toggleRowType(' + idx + ')" id="type-badge-' + idx + '" style="background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;font-weight:700;font-size:11px;padding:4px 8px;border-radius:6px;display:inline-flex;align-items:center;gap:4px;cursor:pointer;" title="Klik untuk ubah ke Alat"><i class="fas fa-box"></i> Material</button>';

    // Ref text if from PO / MR
    const poNum = opts.purchase_order_number || opts.po_number || '';
    let refText = '';
    if (poNum) {
        refText = '<span style="display:block;font-size:10px;color:#64748b;font-family:monospace;margin-top:2px;">#' + escHtml(poNum) + '</span>';
    } else if (opts.mr_number) {
        refText = '<span style="display:block;font-size:10px;color:#64748b;font-family:monospace;margin-top:2px;">#' + escHtml(opts.mr_number) + '</span>';
    }

    // Construction standard units for custom unit select
    const unitOptionsHtml = renderUnitOptions(initialUnit);

    let catSelectOptionsHtml = '<option value="">— Pilih Kategori —</option>';
    catList.forEach(function (cat) {
        const isSel = (initialCatId && cat.id == initialCatId) ? ' selected' : '';
        catSelectOptionsHtml += '<option value="' + cat.id + '"' + isSel + '>' + escHtml(cat.name) + '</option>';
    });

    // BUILD CELLS ACCORDING TO isCustom:
    let skuCellHtml = '';
    let catCellHtml = '';
    let subgroupCellHtml = '';
    let itemCellHtml = '';
    let sizeCellHtml = '';
    let brandCellHtml = '';
    let unitCellHtml = '';

    if (isCustom) {
        skuCellHtml = '<td class="col-sku" style="vertical-align:middle;text-align:center;">'
            + '<input type="text" name="items[' + idx + '][custom_sku]" id="sku-input-' + idx + '" class="form-control text-uppercase text-center" '
            + 'placeholder="' + (isTool ? 'TL-001' : 'MAT-001') + '" value="' + escHtml(initialSku === '-' ? suggestCustomSku(initialName, isTool) : initialSku) + '" '
            + 'style="font-family:monospace;font-weight:700;font-size:12px;height:38px;border-radius:7px;background:#ffffff;" '
            + 'title="Kode Material / Alat">'
            + '</td>';

        const showCustomCat = (initialCatId === '__NEW_CATEGORY__' || Boolean(opts.category_name));
        const initCatName = (initialCatId === '__NEW_CATEGORY__') ? '' : (opts.category_name || '');

        catCellHtml = '<td class="col-cat" style="vertical-align:top;padding-top:10px;">'
            + '<div id="cat-wrapper-' + idx + '">'
            + '<div id="cat-select-wrap-' + idx + '" style="display:' + (showCustomCat ? 'none' : 'flex') + ';align-items:center;gap:4px;">'
            + '<select ' + (showCustomCat ? '' : 'name="items[' + idx + '][category_id]" ') + 'id="item-cat-' + idx + '" class="form-control" onchange="onCustomCategoryChange(' + idx + ', ' + (isTool ? 'true' : 'false') + ')" ' + (showCustomCat ? '' : 'required ') + 'style="font-size:11.5px;height:38px;border-radius:7px;">'
            + catSelectOptionsHtml
            + '</select>'
            + '</div>'
            + '<div id="cat-input-wrap-' + idx + '" style="display:' + (showCustomCat ? 'flex' : 'none') + ';align-items:center;gap:4px;">'
            + '<input type="text" ' + (showCustomCat ? 'name="items[' + idx + '][category_name]" ' : '') + 'id="cat-custom-input-' + idx + '" class="form-control" '
            + 'placeholder="Kategori baru..." value="' + escHtml(initCatName) + '" '
            + 'style="font-size:11.5px;height:38px;border-radius:7px;" title="Kategori Baru">'
            + '<button type="button" class="btn btn-sm btn-light border" onclick="revertCategoryToDropdown(' + idx + ', ' + (isTool ? 'true' : 'false') + ')" title="Pilih dari Daftar Kategori" style="font-size:11px;height:38px;padding:0 8px;border-radius:7px;color:#64748b;"><i class="fas fa-list"></i></button>'
            + '</div>'
            + '</div>'
            + '</td>';

        const availableSubgroups = getAvailableSubgroups(initialCatId, isTool);
        let subgroupSelectOpts = '<option value="">— Pilih Kelompok —</option>';
        let isKnownSubgroup = false;
        availableSubgroups.forEach(function (g) {
            const isSel = (initialType && initialType !== '__NEW_SUBGROUP__' && initialType.toLowerCase() === g.toLowerCase());
            if (isSel) isKnownSubgroup = true;
            subgroupSelectOpts += '<option value="' + escHtml(g) + '"' + (isSel ? ' selected' : '') + '>' + escHtml(g) + '</option>';
        });
        subgroupSelectOpts += '<option value="__NEW_SUBGROUP__" style="font-weight:700;color:#2563eb;">✨ + Ketik Kelompok Baru...</option>';

        const showCustomInput = (initialType === '__NEW_SUBGROUP__' || (initialType && !isKnownSubgroup));
        const initSubVal = (initialType === '__NEW_SUBGROUP__') ? '' : initialType;

        subgroupCellHtml = '<td class="col-subgroup" style="vertical-align:top;padding-top:10px;">'
            + '<div id="subgroup-wrapper-' + idx + '">'
            + '<div id="subgroup-select-wrap-' + idx + '" style="display:' + (showCustomInput ? 'none' : 'flex') + ';align-items:center;gap:4px;">'
            + '<select ' + (showCustomInput ? '' : 'name="items[' + idx + '][type]" ') + 'id="item-type-select-' + idx + '" class="form-control" onchange="onSubgroupSelectChange(' + idx + ', ' + (isTool ? 'true' : 'false') + ')" style="font-size:11.5px;height:38px;border-radius:7px;">'
            + subgroupSelectOpts
            + '</select>'
            + '</div>'
            + '<div id="subgroup-input-wrap-' + idx + '" style="display:' + (showCustomInput ? 'flex' : 'none') + ';align-items:center;gap:4px;">'
            + '<input type="text" ' + (showCustomInput ? 'name="items[' + idx + '][type]" ' : '') + 'id="item-type-input-' + idx + '" class="form-control" '
            + 'placeholder="mis. Bata, Semen..." value="' + escHtml(initSubVal) + '" '
            + 'oninput="onSubgroupTextInput(' + idx + ')" '
            + 'style="font-size:11.5px;height:38px;border-radius:7px;" title="Kelompok Barang">'
            + '<button type="button" class="btn btn-sm btn-light border" onclick="revertSubgroupToDropdown(' + idx + ', ' + (isTool ? 'true' : 'false') + ')" title="Pilih dari Dropdown" style="font-size:11px;height:38px;padding:0 8px;border-radius:7px;color:#64748b;"><i class="fas fa-list"></i></button>'
            + '</div>'
            + '</div>'
            + '</td>';

        itemCellHtml = '<td class="col-item" style="vertical-align:top;padding-top:10px;">'
            + '<div style="display:flex;align-items:center;gap:6px;">'
            + '<input type="text" name="items[' + idx + '][name]" id="item-name-' + idx + '" class="form-control" '
            + 'placeholder="Ketik nama barang baru..." value="' + escHtml(initialName) + '" required '
            + 'data-base-name="' + escHtml(initialName) + '" '
            + 'style="font-weight:600;font-size:12.5px;height:38px;border-radius:7px;" '
            + 'oninput="onCustomNameInput(' + idx + ');">'
            + '<button type="button" class="btn btn-sm btn-light border" onclick="revertRowToStandard(' + idx + ', ' + (isTool ? 'true' : 'false') + ')" title="Kembali ke Pilih dari Inventori" style="font-size:11px;height:38px;padding:0 8px;border-radius:7px;color:#64748b;"><i class="fas fa-rotate-left"></i></button>'
            + '</div>'
            + '<div class="mt-1" style="display:flex;align-items:center;gap:6px;">'
            + '</div>'
            + '<input type="hidden" name="items[' + idx + '][' + (isTool ? 'tool_id' : 'material_id') + ']" value="">'
            + '</td>';

        sizeCellHtml = '<td class="col-size" style="vertical-align:top;padding-top:10px;">'
            + '<input type="text" name="items[' + idx + '][size]" id="item-size-' + idx + '" class="form-control" '
            + 'placeholder="mis. 10mm, 60x60..." value="' + escHtml(initialSize) + '" '
            + 'oninput="syncAutoItemName(' + idx + ')" '
            + 'style="font-size:11.5px;height:38px;border-radius:7px;" title="Ukuran / Spesifikasi Barang">'
            + '</td>';

        brandCellHtml = '<td class="col-brand" style="vertical-align:top;padding-top:10px;">'
            + '<input type="text" name="items[' + idx + '][brand]" id="item-brand-' + idx + '" class="form-control" '
            + 'placeholder="Merek..." value="' + escHtml(initialBrand) + '" '
            + 'style="font-size:11.5px;height:38px;border-radius:7px;" title="Merek Barang">'
            + '</td>';

        unitCellHtml = '<td class="col-unit" style="vertical-align:top;padding-top:10px;text-align:center;">'
            + '<div id="unit-wrapper-' + idx + '">'
            + '<div id="unit-select-wrap-' + idx + '" style="display:flex;align-items:center;gap:4px;">'
            + '<select name="items[' + idx + '][custom_unit]" id="item-unit-' + idx + '" class="form-control form-select text-center" required '
            + 'onchange="onCustomUnitChange(' + idx + '); syncAutoItemName(' + idx + ');" '
            + 'style="font-size:11.5px;font-weight:700;height:38px;border-radius:7px;background:#ffffff;border:1.5px solid #cbd5e1;">'
            + unitOptionsHtml
            + '</select>'
            + '</div>'
            + '<div id="unit-input-wrap-' + idx + '" style="display:none;align-items:center;gap:4px;">'
            + '<input type="text" id="item-unit-custom-input-' + idx + '" class="form-control text-center" placeholder="Satuan baru..." '
            + 'oninput="syncAutoItemName(' + idx + ')" '
            + 'style="font-size:11.5px;font-weight:700;height:38px;border-radius:7px;" title="Satuan Baru">'
            + '<button type="button" class="btn btn-sm btn-light border" onclick="revertUnitToDropdown(' + idx + ')" title="Pilih dari Daftar Satuan" style="font-size:11px;height:38px;padding:0 8px;border-radius:7px;color:#64748b;"><i class="fas fa-list"></i></button>'
            + '</div>'
            + '</div>'
            + '</td>';
    } else {
        const displaySku = foundMasterItem ? (foundMasterItem.sku || foundMasterItem.code || '-') : '-';
        const displayCat = foundMasterItem ? (foundMasterItem.category_name || '-') : '-';
        const displayType = foundMasterItem ? (foundMasterItem.type || '') : (initialType || '');
        const displayBrand = foundMasterItem ? (foundMasterItem.brand || '-') : '-';

        skuCellHtml = '<td class="col-sku" style="vertical-align:middle;text-align:center;">'
            + '<span class="badge" id="sku-badge-' + idx + '" '
            + 'style="font-family:monospace;font-size:11.5px;background:#f1f5f9;color:#0f172a;border:1.5px solid #cbd5e1;padding:6px 9px;border-radius:6px;display:inline-block;font-weight:700;letter-spacing:0.3px;">'
            + escHtml(displaySku)
            + '</span>'
            + '<input type="hidden" name="items[' + idx + '][custom_sku]" id="sku-input-' + idx + '" value="' + escHtml(displaySku === '-' ? '' : displaySku) + '">'
            + '</td>';

        let masterCatOpts = '<option value="">— Semua Kategori —</option>';
        catList.forEach(function (cat) {
            const isSel = (initialCatId && cat.id == initialCatId) ? ' selected' : '';
            masterCatOpts += '<option value="' + cat.id + '"' + isSel + '>' + escHtml(cat.name) + '</option>';
        });
        masterCatOpts += '<option value="__NEW_CATEGORY__" style="font-weight:700;color:#2563eb;">✨ + Ketik Kategori Baru...</option>';

        catCellHtml = '<td class="col-cat" style="vertical-align:top;padding-top:10px;">'
            + '<select id="master-cat-filter-' + idx + '" class="form-control" onchange="onMasterCategoryFilterChange(' + idx + ', ' + (isTool ? 'true' : 'false') + ')" '
            + 'style="font-size:11.5px;height:38px;border-radius:7px;background:#f8fafc;border:1.5px solid #cbd5e1;" title="Pilih / Filter Kategori">'
            + masterCatOpts
            + '</select>'
            + '<input type="hidden" name="items[' + idx + '][category_id]" id="cat-input-' + idx + '" value="' + escHtml(initialCatId) + '">'
            + '</td>';

        const masterSubgroups = getAvailableSubgroups(initialCatId, isTool);
        let masterSubgroupOpts = '<option value="">— Semua Kelompok —</option>';
        masterSubgroups.forEach(function (g) {
            const isSel = (displayType && displayType.toLowerCase() === g.toLowerCase()) ? ' selected' : '';
            masterSubgroupOpts += '<option value="' + escHtml(g) + '"' + isSel + '>' + escHtml(g) + '</option>';
        });
        masterSubgroupOpts += '<option value="__NEW_SUBGROUP__" style="font-weight:700;color:#2563eb;">✨ + Ketik Kelompok Baru...</option>';

        subgroupCellHtml = '<td class="col-subgroup" style="vertical-align:top;padding-top:10px;">'
            + '<select id="master-subgroup-filter-' + idx + '" class="form-control" onchange="onMasterSubgroupFilterChange(' + idx + ', ' + (isTool ? 'true' : 'false') + ')" '
            + 'style="font-size:11.5px;height:38px;border-radius:7px;background:#f8fafc;border:1.5px solid #cbd5e1;" title="Pilih / Filter Kelompok">'
            + masterSubgroupOpts
            + '</select>'
            + '<input type="hidden" name="items[' + idx + '][type]" id="item-type-input-' + idx + '" value="' + escHtml(displayType) + '">'
            + '</td>';

        itemCellHtml = '<td class="col-item" style="vertical-align:top;padding-top:10px;">'
            + '<select name="items[' + idx + '][' + (isTool ? 'tool_id' : 'material_id') + ']" id="item-select-' + idx + '" class="form-control item-select" required '
            + 'onchange="onItemChange(this, ' + idx + ', ' + (isTool ? 'true' : 'false') + ')" '
            + 'style="font-weight:600;font-size:12.5px;height:38px;border-radius:7px;">'
            + selectOptionsHtml
            + '</select>'
            + '<div id="item-meta-' + idx + '" class="item-meta-container" style="margin-top:3px;"></div>'
            + '</td>';

        const displaySize = foundMasterItem ? (foundMasterItem.size || '-') : (initialSize || '-');
        sizeCellHtml = '<td class="col-size" style="vertical-align:middle;">'
            + '<span class="badge" id="size-badge-' + idx + '" '
            + 'style="background:#f8fafc;border:1px solid #e2e8f0;color:#475569;font-weight:600;padding:5px 9px;border-radius:6px;font-size:11.5px;display:inline-block;">'
            + escHtml(displaySize)
            + '</span>'
            + '<input type="hidden" name="items[' + idx + '][size]" id="item-size-' + idx + '" value="' + escHtml(displaySize === '-' ? '' : displaySize) + '">'
            + '</td>';

        brandCellHtml = '<td class="col-brand" style="vertical-align:middle;">'
            + '<span class="badge" id="brand-badge-' + idx + '" '
            + 'style="background:#f8fafc;border:1px solid #e2e8f0;color:#475569;font-weight:600;padding:5px 9px;border-radius:6px;font-size:11.5px;display:inline-block;">'
            + escHtml(displayBrand)
            + '</span>'
            + '<input type="hidden" name="items[' + idx + '][brand]" id="item-brand-' + idx + '" value="' + escHtml(displayBrand === '-' ? '' : displayBrand) + '">'
            + '</td>';

        unitCellHtml = '<td class="col-unit" style="vertical-align:middle;text-align:center;">'
            + '<span class="badge" id="unit-' + idx + '" '
            + 'style="background:#f1f5f9;color:#334155;border:1.5px solid #cbd5e1;font-size:11.5px;padding:5px 9px;border-radius:6px;font-weight:700;display:inline-block;">'
            + escHtml(initialUnit || '-')
            + '</span>'
            + '<input type="hidden" name="items[' + idx + '][custom_unit]" id="item-unit-' + idx + '" value="' + escHtml(initialUnit) + '">'
            + '</td>';
    }

    const row = '<tr id="row-' + idx + '" class="item-row" data-type="' + (isTool ? 'tool' : 'material') + '">'
        // Col 1: Tipe
        + '<td class="col-type" style="vertical-align:middle;text-align:center;">'
        + '<input type="hidden" name="items[' + idx + '][item_type]" id="item-type-' + idx + '" value="' + (isTool ? 'tool' : 'material') + '">'
        + '<input type="hidden" name="items[' + idx + '][condition]" value="good">'
        + '<input type="hidden" name="items[' + idx + '][unit_price]" value="' + (opts.unit_price || 0) + '">'
        + '<input type="hidden" name="items[' + idx + '][stage_reference]" id="stage-input-' + idx + '" value="' + escHtml(stageRef) + '">'
        + '<div style="display:inline-flex;flex-direction:column;align-items:center;gap:1px;">'
        + typeBadgeHtml + refText
        + '</div>'
        + '</td>'

        // Col 2: Kode SKU / Material
        + skuCellHtml

        // Col 3: Kategori
        + catCellHtml

        // Col 4: Kelompok
        + subgroupCellHtml

        // Col 5: Nama Barang
        + itemCellHtml

        // Col 6: Ukuran / Spesifikasi
        + sizeCellHtml

        // Col 7: Merek
        + brandCellHtml

        // Col 8: Satuan
        + unitCellHtml

        // Col 8: Qty Nota / SJ
        + '<td class="col-shipped" style="vertical-align:top;padding-top:10px;">'
        + '<input type="number" name="items[' + idx + '][qty_shipped]" id="item-shipped-' + idx + '" class="form-control text-center shipped-input" '
        + 'value="' + initialShipped + '" min="0" step="' + (isTool ? '1' : 'any') + '" '
        + 'placeholder="0" data-idx="' + idx + '" '
        + 'oninput="onShippedInput(' + idx + ')" onchange="validateQtyInput(this, ' + (isTool ? 'true' : 'false') + '); onShippedInput(' + idx + ');" '
        + 'style="font-weight:700;font-size:13px;color:#334155;border-radius:7px;height:38px;" title="Kuantitas yang tertera di surat jalan atau nota vendor">'
        + '</td>'

        // Col 9: Diterima (Stok) *
        + '<td class="col-qty" style="vertical-align:top;padding-top:10px;">'
        + '<input type="number" name="items[' + idx + '][quantity]" id="item-qty-' + idx + '" class="form-control qty-input text-center" '
        + 'value="' + initialQty + '" min="' + (isTool ? '1' : '0.01') + '" step="' + (isTool ? '1' : 'any') + '" '
        + 'placeholder="' + (isTool ? '1' : '0') + '" required data-idx="' + idx + '" '
        + 'oninput="onReceivedInput(' + idx + ')" onchange="validateQtyInput(this, ' + (isTool ? 'true' : 'false') + '); onReceivedInput(' + idx + ');" '
        + 'style="font-weight:800;font-size:13.5px;color:#1e3a8a;border:2px solid #3b82f6;border-radius:7px;box-shadow:0 1px 3px rgba(37,99,235,0.08);height:38px;" title="Kuantitas fisik diterima baik yang menambah stok gudang">'
        + '</td>'

        // Col 10: Selisih & Status
        + '<td class="col-diff" style="vertical-align:top;padding-top:10px;text-align:center;">'
        + '<input type="hidden" name="items[' + idx + '][qty_missing]" id="item-missing-' + idx + '" value="' + initialMissing + '">'
        + '<div id="item-diff-badge-' + idx + '" style="font-size:11px;font-weight:700;margin-bottom:3px;">'
        + (initialMissing > 0
            ? '<span class="badge" style="background:#fee2e2;color:#991b1b;border:1px solid #fca5a5;font-size:10.5px;padding:2px 7px;border-radius:5px;"><i class="fas fa-triangle-exclamation me-1" style="font-size:9px;"></i> Selisih: -' + initialMissing + '</span>'
            : '<span class="badge" style="background:#f0fdf4;color:#166534;border:1px solid #bbf7d0;font-size:11px;padding:3px 8px;border-radius:5px;"><i class="fas fa-check me-1"></i> Sesuai (0)</span>'
        )
        + '</div>'
        + '<select name="items[' + idx + '][missing_action]" id="item-missing-action-' + idx + '" '
        + 'class="form-control form-control-sm text-center missing-action-select" '
        + 'onchange="onMissingActionChange(' + idx + ')" '
        + 'style="display:' + (initialMissing > 0 ? 'block' : 'none') + '; font-size:11px;font-weight:600;height:32px;border-radius:7px;padding:2px 5px;cursor:pointer;'
        + (initialMissing > 0 ? (initialAction === 'lost' ? 'background:#fee2e2;color:#991b1b;border-color:#fca5a5;' : 'background:#fef3c7;color:#92400e;border-color:#fde68a;') : 'background:#f8fafc;color:#94a3b8;border-color:#e2e8f0;') + '" '
        + 'title="Pilihan tindakan selisih">'
        + '<option value="pending"' + (initialMissing > 0 && initialAction !== 'lost' ? ' selected' : '') + '>⏳ Belum Dikirim Semua</option>'
        + '<option value="lost"' + (initialMissing > 0 && initialAction === 'lost' ? ' selected' : '') + '>❌ Dinyatakan Hilang</option>'
        + '</select>'
        + '</td>'

        // Col 11: Catatan Fisik
        + '<td class="col-notes" style="vertical-align:top;padding-top:10px;">'
        + '<input type="text" name="items[' + idx + '][notes]" id="item-notes-' + idx + '" class="form-control" '
        + 'placeholder="Kondisi fisik / keterangan..." value="' + escHtml(initialNotes) + '" '
        + 'style="font-size:12px;height:38px;border-radius:7px;">'
        + '</td>'

        // Col 12: Action Hapus
        + '<td class="col-action" style="vertical-align:top;padding-top:12px;text-align:center;">'
        + '<button type="button" class="btn-delete-row" onclick="removeRow(' + idx + ')" title="Hapus Baris" style="width:32px;height:32px;border-radius:7px;border:none;background:#fee2e2;color:#dc2626;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;">'
        + '<i class="fas fa-trash-can"></i></button>'
        + '</td>'
        + '</tr>';

    document.getElementById('itemsBody').insertAdjacentHTML('beforeend', row);

    // If master item was found on initialization, trigger metadata & stages
    if (!isCustom && foundMasterItem) {
        const selEl = document.getElementById('item-select-' + idx);
        if (selEl) onItemChange(selEl, idx, isTool);
    }

    const itemType = isTool ? 'tool' : 'material';
    if (currentTabFilter !== 'all' && currentTabFilter !== itemType) {
        filterItemsTab(itemType);
    }
    updateGrandTotal();
}

function openQuickNewItemModal(defaultType) {
    defaultType = defaultType || 'material';
    const modal = document.getElementById('quickNewItemModal');
    if (!modal) return;
    const isTool = (defaultType === 'tool');

    const radioTool = document.getElementById('quickItemTypeTool');
    const radioMat = document.getElementById('quickItemTypeMat');
    if (isTool && radioTool) radioTool.checked = true;
    else if (radioMat) radioMat.checked = true;

    updateQuickItemModalType(defaultType);
    modal.style.display = 'flex';
}

function closeQuickNewItemModal() {
    const modal = document.getElementById('quickNewItemModal');
    if (modal) modal.style.display = 'none';
}

function updateQuickItemModalType(type) {
    const isTool = (type === 'tool');
    const catSelect = document.getElementById('quickItemCat');
    const skuInput = document.getElementById('quickItemSku');
    const unitSelect = document.getElementById('quickItemUnit');

    const catList = isTool ? grToolCategories : grMaterialCategories;
    if (catSelect) {
        let opts = '<option value="">— Pilih Kategori —</option>';
        catList.forEach(function (c) {
            opts += '<option value="' + c.id + '">' + escHtml(c.name) + '</option>';
        });
        catSelect.innerHTML = opts;
    }
    if (skuInput) {
        skuInput.value = suggestCustomSku('', isTool);
    }
    if (unitSelect) {
        unitSelect.innerHTML = renderUnitOptions(isTool ? 'UNIT' : '');
    }
}

function submitQuickNewItem() {
    const type = document.querySelector('input[name="quick_item_type"]:checked')?.value || 'material';
    const isTool = (type === 'tool');
    const nameInput = document.getElementById('quickItemName');
    const skuInput = document.getElementById('quickItemSku');
    const catSelect = document.getElementById('quickItemCat');
    const unitSelect = document.getElementById('quickItemUnit');
    const brandInput = document.getElementById('quickItemBrand');

    const name = nameInput ? nameInput.value.trim() : '';
    if (!name) {
        alert('Nama barang wajib diisi.');
        if (nameInput) nameInput.focus();
        return;
    }

    const sku = skuInput ? skuInput.value.trim() : suggestCustomSku(name, isTool);
    const catId = catSelect ? catSelect.value : '';
    const unit = unitSelect ? unitSelect.value : (isTool ? 'UNIT' : '');
    const brand = brandInput ? brandInput.value.trim() : '';

    closeQuickNewItemModal();

    addRow(type, {
        is_custom: true,
        name: name,
        custom_sku: sku,
        category_id: catId,
        custom_unit: unit,
        brand: brand,
        quantity: 1,
        qty_shipped: 1,
        qty_missing: 0,
        missing_action: 'none',
    });

    if (nameInput) nameInput.value = '';
    if (brandInput) brandInput.value = '';
}

function prefillExistingItems(items) {
    if (Array.isArray(items)) {
        items.forEach(function (item) {
            addRow(item.item_type || 'material', item);
        });
    }
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
    if (!sel || !sel.options || sel.selectedIndex < 0) {
        return;
    }
    const val = sel.value;

    if (val === '__NEW_CUSTOM_ITEM__') {
        switchRowToCustom(idx, isTool);
        return;
    }

    const skuBadge = document.getElementById('sku-badge-' + idx);
    const skuInput = document.getElementById('sku-input-' + idx);
    const catBadge = document.getElementById('cat-badge-' + idx);
    const catInput = document.getElementById('cat-input-' + idx);
    const subgroupBadge = document.getElementById('subgroup-badge-' + idx);
    const subgroupInput = document.getElementById('item-type-input-' + idx);
    const sizeBadge = document.getElementById('size-badge-' + idx);
    const sizeInput = document.getElementById('item-size-' + idx);
    const brandBadge = document.getElementById('brand-badge-' + idx);
    const brandInput = document.getElementById('item-brand-' + idx);
    const unitEl = document.getElementById('unit-' + idx);
    const unitInput = document.getElementById('item-unit-' + idx);
    const metaContainer = document.getElementById('item-meta-' + idx);

    if (!val) {
        const catFilter = document.getElementById('master-cat-filter-' + idx);
        if (catFilter) catFilter.value = '';
        const subgroupFilter = document.getElementById('master-subgroup-filter-' + idx);
        if (subgroupFilter) subgroupFilter.value = '';

        if (skuBadge) skuBadge.textContent = '-';
        if (skuInput) skuInput.value = '';
        if (catBadge) catBadge.innerHTML = '<span class="badge" style="background:#f8fafc;border:1px solid #e2e8f0;color:#334155;font-weight:600;padding:5px 9px;border-radius:6px;font-size:11.5px;display:inline-block;">-</span>';
        if (catInput) catInput.value = '';
        if (subgroupBadge) subgroupBadge.innerHTML = '<span class="badge" style="background:#f8fafc;border:1px solid #e2e8f0;color:#475569;font-weight:600;padding:5px 9px;border-radius:6px;font-size:11.5px;display:inline-block;">-</span>';
        if (subgroupInput) subgroupInput.value = '';
        if (sizeBadge) sizeBadge.innerHTML = '<span class="badge" style="background:#f8fafc;border:1px solid #e2e8f0;color:#475569;font-weight:600;padding:5px 9px;border-radius:6px;font-size:11.5px;display:inline-block;">-</span>';
        if (sizeInput) sizeInput.value = '';
        if (brandBadge) brandBadge.innerHTML = '<span class="badge" style="background:#f8fafc;border:1px solid #e2e8f0;color:#475569;font-weight:600;padding:5px 9px;border-radius:6px;font-size:11.5px;display:inline-block;">-</span>';
        if (brandInput) brandInput.value = '';
        if (unitEl) unitEl.textContent = isTool ? 'Unit' : '-';
        if (unitInput) unitInput.value = isTool ? 'Unit' : '';
        if (metaContainer) metaContainer.innerHTML = '';
        checkAndUpdateWarehouseLock();
        return;
    }

    const itemsList = isTool ? grTools : grMaterials;
    const item = itemsList.find(function (i) { return i.id == val; });
    if (!item) return;

    const catFilter = document.getElementById('master-cat-filter-' + idx);
    if (catFilter && item.category_id) {
        catFilter.value = item.category_id;
        const subgroupFilter = document.getElementById('master-subgroup-filter-' + idx);
        if (subgroupFilter) {
            const groups = getAvailableSubgroups(item.category_id, isTool);
            let sHtml = '<option value="">— Semua Kelompok —</option>';
            groups.forEach(function (g) {
                sHtml += '<option value="' + escHtml(g) + '">' + escHtml(g) + '</option>';
            });
            sHtml += '<option value="__NEW_SUBGROUP__" style="font-weight:700;color:#2563eb;">✨ + Ketik Kelompok Baru...</option>';
            subgroupFilter.innerHTML = sHtml;
        }
    }
    const subgroupFilter = document.getElementById('master-subgroup-filter-' + idx);
    if (subgroupFilter && item.type) {
        let hasOpt = Array.from(subgroupFilter.options).some(function (o) { return o.value.toLowerCase() === item.type.toLowerCase(); });
        if (!hasOpt) {
            subgroupFilter.insertAdjacentHTML('beforeend', '<option value="' + escHtml(item.type) + '">' + escHtml(item.type) + '</option>');
        }
        subgroupFilter.value = item.type;
    }

    if (skuBadge) skuBadge.textContent = item.sku || item.code || '-';
    if (skuInput) skuInput.value = item.sku || item.code || '';
    if (catBadge) {
        catBadge.innerHTML = '<span class="badge" style="background:#f8fafc;border:1px solid #e2e8f0;color:#334155;font-weight:600;padding:5px 9px;border-radius:6px;font-size:11.5px;display:inline-block;">'
            + escHtml(item.category_name || '-') + '</span>';
    }
    if (catInput) catInput.value = item.category_id || '';

    if (subgroupBadge) {
        subgroupBadge.innerHTML = '<span class="badge" style="background:#f8fafc;border:1px solid #e2e8f0;color:#475569;font-weight:600;padding:5px 9px;border-radius:6px;font-size:11.5px;display:inline-block;">'
            + escHtml(item.type || '-') + '</span>';
    }
    if (subgroupInput) subgroupInput.value = item.type || '';

    if (sizeBadge) {
        sizeBadge.innerHTML = '<span class="badge" style="background:#f8fafc;border:1px solid #e2e8f0;color:#475569;font-weight:600;padding:5px 9px;border-radius:6px;font-size:11.5px;display:inline-block;">'
            + escHtml(item.size || '-') + '</span>';
    }
    if (sizeInput) sizeInput.value = item.size || '';

    if (brandBadge) {
        brandBadge.innerHTML = '<span class="badge" style="background:#f8fafc;border:1px solid #e2e8f0;color:#475569;font-weight:600;padding:5px 9px;border-radius:6px;font-size:11.5px;display:inline-block;">'
            + escHtml(item.brand || '-') + '</span>';
    }
    if (brandInput) brandInput.value = item.brand || '';

    const abbr = item.abbr || (isTool ? 'Unit' : 'Unit');
    if (unitEl) unitEl.textContent = abbr;
    if (unitInput) unitInput.value = abbr;

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
        const qtyInput = document.getElementById('item-qty-' + idx);
        const shippedInput = document.getElementById('item-shipped-' + idx);
        if (qtyInput && (!qtyInput.value || parseFloat(qtyInput.value) <= 0)) {
            const pQty = parseFloat(firstPlanned.qty) || 1;
            qtyInput.value = isTool ? Math.max(1, Math.round(pQty)) : pQty;
            if (shippedInput && (!shippedInput.value || parseFloat(shippedInput.value) <= 0)) {
                shippedInput.value = qtyInput.value;
            }
        }
        if (firstPlanned.date) {
            const dateInput = document.getElementById('receiptDateInput');
            if (dateInput) {
                dateInput.value = firstPlanned.date;
                flashEl(dateInput, '#eff6ff');
            }
        }
    }

    // Render clean subtle meta line
    const metaParts = [];
    if (item.brand) metaParts.push('<span>Merek: <strong style="color:#475569;">' + escHtml(item.brand) + '</strong></span>');
    if (item.size) metaParts.push('<span>Ukuran: <strong style="color:#475569;">' + escHtml(item.size) + '</strong></span>');
    if (item.type && item.type !== 'tool' && item.type !== 'material') metaParts.push('<span>' + escHtml(item.type) + '</span>');

    let metaHtml = '';
    if (metaParts.length > 0) {
        metaHtml = '<div style="font-size:11px;color:#64748b;margin-top:4px;display:flex;align-items:center;gap:6px;flex-wrap:wrap;">'
            + metaParts.join('<span style="color:#cbd5e1;">•</span>')
            + '</div>';
    }

    // Render tahapan
    const plannedStages = (item.planned_stages || []).filter(function (stg) { return stg.status === 'planned'; });
    if (plannedStages.length > 0) {
        const stageInputEl = document.getElementById('stage-input-' + idx);
        const currentStageLower = stageInputEl ? stageInputEl.value.trim().toLowerCase() : '';

        metaHtml += '<div style="margin-top:6px;display:flex;align-items:center;gap:6px;flex-wrap:wrap;">'
            + '<span style="font-size:11px;font-weight:600;color:#92400e;display:inline-flex;align-items:center;gap:3px;"><i class="fas fa-clock text-warning"></i> Pilih Tahap:</span>';

        plannedStages.forEach(function (stg, sIdx) {
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
                (function (s, sI, sQty, sWhId, sWhName) {
                    setTimeout(function () {
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
    allPills.forEach(function (p) { p.classList.remove('active'); });
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
    const found = grSuppliers.find(function (s) { return s.name.toLowerCase() === val; });
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
        .then(function (r) {
            if (!r.ok) throw new Error('HTTP error ' + r.status);
            return r.json();
        })
        .then(function (data) {
            // Auto-fill supplier dari PO
            if (data.supplier_id && data.supplier_name) {
                setSupplierByName(data.supplier_name, data.supplier_id);
            } else if (data.supplier_id) {
                const sup = grSuppliers.find(function (s) { return s.id == data.supplier_id; });
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
                data.items.forEach(function (item) {
                    const targetQty = (item.qty_remaining != null) ? item.qty_remaining : item.qty_ordered;
                    addRow(item.item_type || 'material', {
                        material_id: item.material_id,
                        tool_id: item.tool_id,
                        material_name: item.material_name,
                        custom_item_name: item.custom_item_name,
                        custom_item_unit: item.custom_item_unit,
                        custom_item_type: item.custom_item_type,
                        unit_abbr: item.unit_abbr,
                        category_id: item.category_id,
                        category_name: item.category_name,
                        sku: item.sku,
                        custom_sku: item.custom_sku,
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
        .catch(function (err) {
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

    const mr = (typeof grMaterialRequests !== 'undefined' ? grMaterialRequests : []).find(function (m) { return m.id == mrId; });

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
        .then(function (r) {
            if (!r.ok) throw new Error('HTTP error ' + r.status);
            return r.json();
        })
        .then(function (data) {
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
                    data.purchase_orders.forEach(function (po) {
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
                displayItems.forEach(function (item) {
                    const targetQty = (item.qty_remaining != null) ? item.qty_remaining : item.qty_ordered;
                    addRow(item.item_type || 'material', {
                        material_id: item.material_id,
                        tool_id: item.tool_id,
                        material_name: item.material_name,
                        custom_item_name: item.custom_item_name,
                        custom_item_unit: item.custom_item_unit,
                        custom_item_type: item.custom_item_type,
                        unit_abbr: item.unit_abbr,
                        category_id: item.category_id,
                        category_name: item.category_name,
                        sku: item.sku,
                        custom_sku: item.custom_sku,
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
        .catch(function (err) {
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
    (grMaterials || []).forEach(function (m) {
        (m.planned_stages || []).forEach(function (stg, sIdx) {
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
    (grTools || []).forEach(function (t) {
        (t.planned_stages || []).forEach(function (stg, sIdx) {
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
    list.sort(function (a, b) {
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
    const todayCount = planned.filter(function (i) { return i.scheduled_date === todayDate; }).length;
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

    planned.forEach(function (item) {
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
            const sup = firstSupplierId ? grSuppliers.find(function (s) { return s.id == firstSupplierId; }) : null;
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
        .then(function (r) { return r.json(); })
        .then(function (data) {
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

            currentScheduledData.forEach(function (item, i) {
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
        .catch(function () {
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
    checkboxes.forEach(function (cb) { cb.checked = shouldCheck; });
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

    checkboxes.forEach(function (cb) {
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
            const sup = firstSupplierId ? grSuppliers.find(function (s) { return s.id == firstSupplierId; }) : null;
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
    setTimeout(function () { toast.remove(); }, 3500);
}

// ============================================================
// FILTER TABS
// ============================================================

function filterItemsTab(type) {
    currentTabFilter = type;
    ['all', 'material', 'tool'].forEach(function (t) {
        const btn = document.getElementById('tab-btn-' + t);
        if (btn) {
            if (t === type) btn.classList.add('active');
            else btn.classList.remove('active');
        }
    });

    const rows = document.querySelectorAll('#itemsBody tr.item-row');
    let visibleCount = 0;
    rows.forEach(function (tr) {
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
            tr.innerHTML = '<td colspan="13" style="text-align:center;padding:36px 16px;background:#fafcff;">'
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

function onReceivedInput(idx) {
    const qtyInput = document.getElementById('item-qty-' + idx);
    const shippedInput = document.getElementById('item-shipped-' + idx);
    if (!qtyInput) return;

    if (shippedInput && (shippedInput.value === '' || shippedInput.dataset.autoSynced !== 'false')) {
        shippedInput.value = qtyInput.value;
        shippedInput.dataset.autoSynced = 'true';
    }
    recalcItemDiscrepancy(idx);
    updateGrandTotal();
}

function onShippedInput(idx) {
    const shippedInput = document.getElementById('item-shipped-' + idx);
    if (shippedInput) {
        shippedInput.dataset.autoSynced = 'false';
    }
    recalcItemDiscrepancy(idx);
    updateGrandTotal();
}

function onMissingActionChange(idx) {
    const actionSelect = document.getElementById('item-missing-action-' + idx);
    if (!actionSelect) return;
    const action = actionSelect.value;
    if (action === 'lost') {
        actionSelect.style.background = '#fee2e2';
        actionSelect.style.color = '#991b1b';
        actionSelect.style.borderColor = '#fca5a5';
    } else if (action === 'pending') {
        actionSelect.style.background = '#fef3c7';
        actionSelect.style.color = '#92400e';
        actionSelect.style.borderColor = '#fde68a';
    } else {
        actionSelect.style.background = '#f8fafc';
        actionSelect.style.color = '#94a3b8';
        actionSelect.style.borderColor = '#e2e8f0';
    }
}

function recalcItemDiscrepancy(idx) {
    const qtyInput = document.getElementById('item-qty-' + idx);
    const shippedInput = document.getElementById('item-shipped-' + idx);
    const missingHidden = document.getElementById('item-missing-' + idx);
    const badgeWrap = document.getElementById('item-diff-badge-' + idx);
    const actionSelect = document.getElementById('item-missing-action-' + idx);

    if (!qtyInput || !missingHidden) return;

    const recVal = parseFloat(qtyInput.value) || 0;
    const shipVal = (shippedInput && shippedInput.value !== '') ? (parseFloat(shippedInput.value) || 0) : recVal;

    const diff = Math.max(0, shipVal - recVal);
    missingHidden.value = diff;

    if (diff > 0) {
        if (badgeWrap) {
            badgeWrap.style.display = 'inline-block';
            badgeWrap.innerHTML = '<span class="badge" style="background:#fee2e2;color:#991b1b;border:1px solid #fca5a5;font-size:10.5px;padding:2px 7px;border-radius:5px;"><i class="fas fa-triangle-exclamation me-1" style="font-size:9px;"></i> Selisih: -' + diff + '</span>';
        }
        if (actionSelect) {
            actionSelect.style.display = 'block';
            if (actionSelect.value === 'none') {
                actionSelect.value = 'pending';
            }
            onMissingActionChange(idx);
        }
    } else {
        if (badgeWrap) {
            badgeWrap.style.display = 'inline-block';
            badgeWrap.innerHTML = '<span class="badge" style="background:#f0fdf4;color:#166534;border:1px solid #bbf7d0;font-size:11px;padding:3px 8px;border-radius:5px;"><i class="fas fa-check me-1"></i> Sesuai (0)</span>';
        }
        if (actionSelect) {
            actionSelect.style.display = 'none';
            actionSelect.value = 'none';
            onMissingActionChange(idx);
        }
    }
}

function updateGrandTotal() {
    const rows = document.querySelectorAll('#itemsBody tr.item-row');
    let countAll = rows.length, countMaterial = 0, countTool = 0, totalMissing = 0;
    rows.forEach(function (tr) {
        if (tr.getAttribute('data-type') === 'tool') countTool++;
        else countMaterial++;
    });
    document.querySelectorAll('input[id^="item-missing-"]').forEach(function (inp) {
        totalMissing += parseFloat(inp.value) || 0;
    });

    const elAll = document.getElementById('count-all');
    if (elAll) elAll.textContent = countAll;
    const elMat = document.getElementById('count-material');
    if (elMat) elMat.textContent = countMaterial;
    const elTool = document.getElementById('count-tool');
    if (elTool) elTool.textContent = countTool;
    const totalEl = document.getElementById('totalItemCount');
    if (totalEl) {
        let diffBadge = '';
        if (totalMissing > 0) {
            diffBadge = ' <span class="badge" style="background:#fee2e2;color:#991b1b;border:1px solid #fca5a5;font-size:11px;padding:3px 8px;border-radius:6px;font-weight:700;"><i class="fas fa-triangle-exclamation me-1"></i>Selisih/Hilang: ' + totalMissing + '</span>';
        }
        totalEl.innerHTML = '<span style="font-size:12px;color:#64748b;font-weight:600;">Total:</span> '
            + '<span class="badge" style="background:#0f172a;color:#ffffff;font-size:11px;padding:3px 8px;border-radius:6px;font-weight:700;">' + countAll + ' Item</span> '
            + '<span class="badge" style="background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;font-size:11px;padding:3px 8px;border-radius:6px;font-weight:600;">' + countMaterial + ' Material</span> '
            + '<span class="badge" style="background:#fffbeb;color:#b45309;border:1px solid #fde68a;font-size:11px;padding:3px 8px;border-radius:6px;font-weight:600;">' + countTool + ' Alat</span>'
            + diffBadge;
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
    setTimeout(function () { el.style.background = ''; }, 800);
}

// ============================================================
// INIT
// ============================================================

document.addEventListener('DOMContentLoaded', function () {
    ensureUnitsDatalist();

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

    if (initialMrId && initialPoId && typeof loadMrItems === 'function') {
        if (mrSelect) mrSelect.value = initialMrId;
        if (poSelect) poSelect.value = initialPoId;
        loadMrItems(initialMrId, initialPoId);
    } else if (initialMrId && typeof onMrSelectChange === 'function') {
        if (mrSelect) mrSelect.value = initialMrId;
        onMrSelectChange(initialMrId);
    } else if (initialPoId && typeof loadPoItems === 'function') {
        if (poSelect) poSelect.value = initialPoId;
        loadPoItems(initialPoId);
    } else {
        const tbody = document.getElementById('itemsBody');
        const hasExisting = (typeof grExistingItems !== 'undefined' && Array.isArray(grExistingItems) && grExistingItems.length > 0);
        if (tbody && tbody.querySelectorAll('tr.item-row').length === 0 && !hasExisting) {
            addRow('material');
        } else {
            checkEmptyState();
        }
    }
    checkAndShowScheduledPrompt();
    checkAndUpdateWarehouseLock();
});
