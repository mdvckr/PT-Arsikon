<x-app-layout>
    <x-slot name="title">Catat Nota Pembelian Harian</x-slot>

    <div class="mb-4">
        <a href="{{ route('purchase-receipts.index') }}" class="text-muted" style="font-size:13px;">
            <x-lucide-arrow-left class="me-1" /> Kembali ke Daftar Nota
        </a>
        <h2 style="font-size:20px;font-weight:700;color:#0f172a;margin-top:4px;">Pencatatan Nota Pembelian Harian</h2>
        <p class="text-muted" style="font-size:13px;">Pencatatan belanja operasional langsung di proyek.</p>
    </div>

    @if($errors->any())
        <div class="alert alert-danger mb-4" style="background:#fef2f2;border:1px solid #ef4444;color:#991b1b;padding:12px 16px;border-radius:8px;">
            <strong class="block font-semibold mb-1">Terjadi Kesalahan Input:</strong>
            <ul class="list-disc ms-5 text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div x-data="notaReceiptForm()">
        <form action="{{ route('purchase-receipts.store') }}" method="POST" enctype="multipart/form-data" id="form-purchase-receipt">
            @csrf

            {{-- 1. DATA HEADER NOTA --}}
            <div class="card mb-4" style="padding:20px;">
                <h3 style="font-size:16px;font-weight:600;margin-bottom:16px;color:#1e293b;">Data Nota Pembelian</h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    {{-- Pilih Proyek --}}
                    <div>
                        <label class="form-label font-semibold">Proyek <span class="text-danger">*</span></label>
                        <select name="project_id" id="project_id_select" class="form-control" required x-model="selectedProjectId" @change="onProjectChange($event)">
                            @if($projects->count() > 1)
                                <option value="">-- Pilih Proyek --</option>
                            @endif
                            @foreach($projects as $prj)
                                <option value="{{ $prj->id }}" 
                                        data-balance="{{ (float)$prj->operational_balance }}"
                                        data-name="{{ $prj->name }}"
                                        {{ (old('project_id', $selectedProject?->id) == $prj->id) ? 'selected' : '' }}>
                                    {{ $prj->name }}
                                </option>
                            @endforeach
                        </select>
                        <input type="hidden" name="project_name" :value="projectName">
                        <small class="text-muted block mt-1" style="font-size:11.5px;color:#047857;font-weight:600;">
                            <x-lucide-wallet class="text-emerald-600 me-1" /> Saldo Kas Proyek: <span x-text="formatRupiah(currentBalance)"></span>
                        </small>
                    </div>

                    {{-- Supplier / Toko --}}
                    <div>
                        <label class="form-label font-semibold">Supplier / Toko Pembelian <span class="text-danger">*</span></label>
                        <input type="text" name="supplier_name" value="{{ old('supplier_name') }}" placeholder="Contoh: Toko Bangunan Berkah / Depot Pasir" class="form-control" required>
                    </div>

                    {{-- Tanggal Nota --}}
                    <div>
                        <label class="form-label font-semibold">Tanggal Nota <span class="text-danger">*</span></label>
                        <input type="date" name="receipt_date" value="{{ old('receipt_date', date('Y-m-d')) }}" class="form-control" required>
                    </div>

                    {{-- Nomor Nota Fisik --}}
                    <div>
                        <label class="form-label font-semibold">Nomor Nota Fisik / Faktur</label>
                        <input type="text" name="receipt_number" value="{{ old('receipt_number') }}" placeholder="Contoh: NOTA-8812 / Bon Toko" class="form-control">
                    </div>

                    {{-- Status Pembayaran --}}
                    <div>
                        <label class="form-label font-semibold">Status Pembayaran Nota <span class="text-danger">*</span></label>
                        <input type="hidden" name="payment_status" value="paid">
                        <div class="form-control" style="background:#ecfdf5;color:#065f46;font-weight:700;display:flex;align-items:center;gap:6px;border-color:#a7f3d0;cursor:default;">
                            <x-lucide-circle-check class="text-emerald-600" />
                            <span>Sudah Dibayar (Lunas Lapangan)</span>
                        </div>
                        <small class="text-muted" style="font-size:11px;margin-top:2px;display:block;">Belanja telah dibayarkan langsung di toko/lapangan.</small>
                    </div>

                    {{-- Sumber Dana Belanja --}}
                    <div>
                        <label class="form-label font-semibold">Sumber Dana Belanja <span class="text-danger">*</span></label>
                        <select name="payment_source" x-model="paymentSource" class="form-control" style="font-weight:600;" required>
                            <option value="project_cash">💵 Kas Operasional Proyek</option>
                            <option value="personal_advance">👤 Talangan Pribadi (Uang Sendiri)</option>
                        </select>
                        <small class="text-muted" style="font-size:11px;margin-top:2px;display:block;">Pilih jika ditalangi staf/mandor sebelum dana pusat turun.</small>
                    </div>

                    {{-- Ditalangi Oleh (Muncul jika Talangan Pribadi) --}}
                    <div x-show="paymentSource === 'personal_advance'" x-transition>
                        <label class="form-label font-semibold" style="color:#b45309;">
                            Ditalangi Oleh (Nama Lengkap) <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="paid_by_name" x-model="paidByName" :required="paymentSource === 'personal_advance'" placeholder="Contoh: Mandor Joko / Pak Budi (Pelaksana)" class="form-control" style="border-color:#f59e0b;background:#fffbeb;font-weight:600;">
                        <small class="text-muted" style="font-size:11px;margin-top:2px;display:block;color:#92400e;">Nama pemilik uang untuk pencatatan penggantian (reimburse) dari pusat.</small>
                    </div>

                    {{-- Upload Foto Nota --}}
                    <div>
                        <label class="form-label font-semibold">Upload Foto / Scan Nota Fisik</label>
                        <input type="file" name="image" accept="image/*,application/pdf" class="form-control" style="padding:6px;">
                        <small class="text-muted block mt-1" style="font-size:11px;">Format: JPG, PNG, WEBP, PDF (Max: 10MB).</small>
                    </div>

                    {{-- Catatan --}}
                    <div class="md:col-span-3">
                        <label class="form-label font-semibold">Catatan / Keterangan Pembelian</label>
                        <input type="text" name="notes" value="{{ old('notes') }}" placeholder="Peruntukan pembelian material, nama pekerja yang membeli, atau keterangan lainnya..." class="form-control">
                    </div>
                </div>
            </div>

            {{-- 2. RINCIAN ITEM BARANG --}}
            <div class="card mb-4" style="padding:20px;">
                <div class="flex justify-between items-center mb-3">
                    <div>
                        <h3 style="font-size:16px;font-weight:600;color:#1e293b;">Rincian Barang yang Dibeli</h3>
                        <p class="text-muted" style="font-size:12px;">Daftar item belanja lapangan sesuai bon fisik.</p>
                    </div>
                    <button type="button" class="btn btn-sm btn-secondary" @click="addItem()">
                        <x-lucide-plus class="me-1" /> Tambah Baris Barang
                    </button>
                </div>

                <div class="table-wrap">
                    <table class="data-table" id="items-table">
                        <thead>
                            <tr>
                                <th style="width: 14%;">Item <span class="text-danger">*</span></th>
                                <th style="width: 26%;">Nama Barang <span class="text-danger">*</span></th>
                                <th style="width: 10%;">Satuan <span class="text-danger">*</span></th>
                                <th style="width: 9%; text-align:right;">Vol (Qty) <span class="text-danger">*</span></th>
                                <th style="width: 13%; text-align:right;">Harga Satuan (Rp) <span class="text-danger">*</span></th>
                                <th style="width: 11%; text-align:right;">Diskon Item (Rp)</th>
                                <th style="width: 14%; text-align:right;">Subtotal Bersih</th>
                                <th style="width: 3%; text-align:center;">Hapus</th>
                            </tr>
                        </thead>
                        <tbody id="items-tbody">
                            <template x-for="(item, index) in items" :key="index">
                                <tr class="item-row">
                                    <td>
                                        <select :name="`items[${index}][item_type]`" x-model="item.item_type" class="form-control" style="padding:6px 8px; font-weight:600;" required>
                                            <option value="Material">Material</option>
                                            <option value="Alat">Alat</option>
                                            <option value="Konsumsi">Konsumsi</option>
                                            <option value="Operasional">Operasional</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" :name="`items[${index}][item_name]`" :list="item.item_type === 'Material' ? 'material-list' : null" x-model="item.item_name" @input="onItemInput(item, $event)" :placeholder="item.item_type === 'Material' ? 'Misal: Semen Gresik, Triplek...' : (item.item_type === 'Alat' ? 'Misal: Sewa Molen, Cangkul...' : (item.item_type === 'Konsumsi' ? 'Misal: Makan Siang Pekerja, Air Galon...' : 'Nama barang / keperluan...'))" class="form-control" required>
                                    </td>
                                    <td>
                                        <input type="text" :name="`items[${index}][unit_name]`" list="unit-list" x-model="item.unit_name" placeholder="Zak/Pcs/Porsi" class="form-control" required>
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0.01" :name="`items[${index}][quantity]`" x-model.number="item.quantity" @input="updateRowSubtotal(item)" class="form-control text-end" required>
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0" :name="`items[${index}][unit_price]`" x-model.number="item.unit_price" @input="updateRowSubtotal(item)" class="form-control text-end" required>
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0" :name="`items[${index}][item_discount]`" x-model.number="item.item_discount" @input="updateRowSubtotal(item)" class="form-control text-end" placeholder="0">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control text-end font-semibold" style="background:#f8fafc;" readonly :value="formatRupiah(getItemSubtotal(item))">
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-icon" @click="removeItem(index)" title="Hapus Baris">
                                            <x-lucide-trash-2 />
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                {{-- Datalist untuk Material dan Satuan --}}
                <datalist id="material-list">
                    @foreach($materials as $mat)
                        <option value="{{ $mat->name }}" data-unit="{{ $mat->unit?->symbol ?? '' }}">{{ $mat->name }}</option>
                    @endforeach
                </datalist>

                <datalist id="unit-list">
                    <option value="Zak">Zak</option>
                    <option value="Lbr">Lembar (Lbr)</option>
                    <option value="Pcs">Pcs</option>
                    <option value="Bh">Buah (Bh)</option>
                    <option value="Porsi">Porsi</option>
                    <option value="Bks">Bungkus (Bks)</option>
                    <option value="Hari">Hari</option>
                    <option value="Kg">Kilogram (Kg)</option>
                    <option value="M3">Meter Kubik (M3)</option>
                    <option value="M2">Meter Persegi (M2)</option>
                    <option value="Meter">Meter (m)</option>
                    <option value="Batang">Batang</option>
                    <option value="Roll">Roll</option>
                    <option value="Kaleng">Kaleng</option>
                    <option value="Dus">Dus / Box</option>
                    <option value="Set">Set</option>
                    <option value="Liter">Liter</option>
                </datalist>

                {{-- 3. BIAYA TAMBAHAN, DISKON GLOBAL & KALKULASI REAL-TIME --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6 pt-5" style="border-top:1px solid #e2e8f0;">
                    {{-- Komponen Biaya & Diskon --}}
                    <div style="display:flex;flex-direction:column;gap:12px;">
                        <h4 style="font-size:14px;font-weight:700;color:#1e293b;">Biaya Tambahan & Potongan Nota</h4>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="form-label font-semibold" style="font-size:12px;">Diskon Global Nota (Rp)</label>
                                <input type="number" step="0.01" min="0" name="global_discount" x-model.number="globalDiscount" class="form-control" placeholder="0">
                                <small class="text-muted block mt-1" style="font-size:11px;">Potongan faktur/voucher total belanja di kasir.</small>
                            </div>
                            <div>
                                <label class="form-label font-semibold" style="font-size:12px;">Ongkos Kirim (Rp)</label>
                                <input type="number" step="0.01" min="0" name="shipping_fee" x-model.number="shippingFee" class="form-control" placeholder="0">
                                <small class="text-muted block mt-1" style="font-size:11px;">Biaya angkut armada toko ke proyek.</small>
                            </div>
                        </div>
                    </div>

                    {{-- Kartu Ringkasan Real-Time --}}
                    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:18px;">
                        <h4 style="font-size:14px;font-weight:700;color:#0f172a;margin-bottom:12px;display:flex;align-items:center;gap:6px;">
                            <x-lucide-calculator style="font-size:16px;color:#2563eb;" />
                            Ringkasan Perhitungan Nota
                        </h4>

                        <div style="display:flex;flex-direction:column;gap:8px;font-size:13px;">
                            <div class="flex justify-between" style="color:#475569;">
                                <span>Total Belanja Kotor:</span>
                                <span class="font-semibold" x-text="formatRupiah(subtotalKotor)"></span>
                            </div>

                            <div class="flex justify-between" style="color:#059669;" x-show="totalDiscount > 0">
                                <span>Total Diskon (Item + Global):</span>
                                <span class="font-semibold" x-text="'- ' + formatRupiah(totalDiscount)"></span>
                            </div>

                            <div class="flex justify-between" style="color:#475569;" x-show="shippingFee > 0">
                                <span>Ongkos Kirim:</span>
                                <span class="font-semibold" x-text="'+ ' + formatRupiah(shippingFee)"></span>
                            </div>

                            <div class="flex justify-between items-baseline pt-2" style="border-top:1px solid #cbd5e1;">
                                <span style="font-weight:700;color:#0f172a;font-size:14px;">TOTAL AKHIR BERSIH:</span>
                                <span style="font-weight:800;color:#047857;font-size:20px;" x-text="formatRupiah(grandTotal)"></span>
                            </div>

                            <div class="flex justify-between items-center pt-2 text-xs" style="border-top:1px dashed #cbd5e1;">
                                <span style="color:#64748b;">
                                    <span x-show="paymentSource === 'personal_advance'">Saldo Kas Proyek (Tetap Utuh):</span>
                                    <span x-show="paymentSource !== 'personal_advance'">Sisa Saldo Kas Operasional:</span>
                                </span>
                                <span :style="remainingBalance >= 0 ? 'color:#059669;font-weight:700;' : 'color:#dc2626;font-weight:800;'" x-text="formatRupiah(remainingBalance)"></span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Alert jika Talangan Pribadi: info bahwa saldo kas & topup tidak terpotong --}}
                <div x-show="paymentSource === 'personal_advance'" class="mt-4 p-3 rounded-lg" style="background:#f0fdf4;border:1.5px solid #86efac;color:#166534;">
                    <div class="flex items-start gap-2.5">
                        <x-lucide-shield-check class="text-emerald-600 mt-0.5" style="font-size:18px;flex-shrink:0;" />
                        <div>
                            <strong style="font-size:13px;display:block;color:#166534;">Saldo Kas & Top-Up Pusat Terlindungi (Tidak Terpotong)</strong>
                            <span style="font-size:12px;color:#15803d;line-height:1.4;display:block;margin-top:2px;">
                                Karena belanja ditalangi uang pribadi (<span x-text="paidByName || 'Staf'"></span>), saldo kas operasional proyek <strong>tidak dipotong</strong> dan saldo dana Top-Up dari Kantor Pusat nantinya akan <strong>masuk 100% utuh tanpa terpotong</strong>.
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Alert Saldo Kas Kurang (hanya jika pakai Kas Proyek) --}}
                <div x-show="paymentSource === 'project_cash' && isBalanceInsufficient" class="mt-4 p-3 rounded-lg" style="background:#fff7ed;border:1.5px solid #fdba74;color:#9a3412;">
                    <div class="flex items-start gap-2.5">
                        <x-lucide-circle-alert class="text-amber-600 mt-0.5" style="font-size:18px;flex-shrink:0;" />
                        <div>
                            <strong style="font-size:13px;display:block;color:#9a3412;">Saldo Kas Proyek Saat Ini Kurang!</strong>
                            <span style="font-size:12px;color:#7c2d12;line-height:1.4;display:block;margin-top:2px;">
                                Total belanja (<span x-text="formatRupiah(grandTotal)"></span>) melebihi saldo kas tersedia (<span x-text="formatRupiah(currentBalance)"></span>). Jika belanja ini menggunakan uang pribadi mandor/staf, silakan ubah Sumber Dana di atas ke <strong>"Talangan Pribadi (Uang Sendiri)"</strong> agar saldo kas & top-up proyek <strong>tidak terpotong</strong>.
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3 mb-6">
                <a href="{{ route('purchase-receipts.index') }}" class="btn btn-light">Batal</a>
                <button type="submit" id="submit_btn" class="btn btn-primary" style="padding:10px 24px;font-weight:700;">
                    <x-lucide-save class="me-1" />
                    <span x-text="isBalanceInsufficient ? 'Simpan Nota (Catat Talangan)' : 'Simpan & Potong Saldo Kas'"></span>
                </button>
            </div>
        </form>
    </div>

    @php
        $rawItems = old('items');
        if ($rawItems && is_array($rawItems)) {
            $initialItems = [];
            foreach (array_values($rawItems) as $it) {
                $q = (float)($it['quantity'] ?? 1);
                $p = (float)($it['unit_price'] ?? 0);
                $d = (float)($it['item_discount'] ?? 0);
                $sub = max(0, ($q * $p) - min($q * $p, max(0, $d)));
                $initialItems[] = [
                    'item_type'     => (string)($it['item_type'] ?? 'Material'),
                    'item_name'     => (string)($it['item_name'] ?? ($it['material_name'] ?? '')),
                    'unit_name'     => (string)($it['unit_name'] ?? ''),
                    'quantity'      => $q,
                    'unit_price'    => $p,
                    'item_discount' => $d,
                    'subtotal'      => $sub,
                ];
            }
        } else {
            $initialItems = [
                [
                    'item_type'     => 'Material',
                    'item_name'     => '',
                    'unit_name'     => '',
                    'quantity'      => 1,
                    'unit_price'    => 0,
                    'item_discount' => 0,
                    'subtotal'      => 0,
                ]
            ];
        }
    @endphp

    <script>
        function notaReceiptForm() {
            return {
                currentBalance: {{ (float)($currentBalance ?? 0) }},
                selectedProjectId: @json(old('project_id', (string)($selectedProject?->id ?? ''))),
                projectName: @json(old('project_name', (string)($selectedProject?->name ?? ''))),
                globalDiscount: {{ (float) old('global_discount', 0) }},
                shippingFee: {{ (float) old('shipping_fee', 0) }},
                paymentSource: @json(old('payment_source', 'project_cash')),
                paidByName: @json(old('paid_by_name', auth()->user()?->name ?? '')),
                items: @json($initialItems),

                init() {
                    this.items.forEach(item => {
                        this.updateRowSubtotal(item);
                    });
                },

                addItem() {
                    this.items.push({
                        item_type: 'Material',
                        item_name: '',
                        unit_name: '',
                        quantity: 1,
                        unit_price: 0,
                        item_discount: 0,
                        subtotal: 0,
                    });
                },

                removeItem(index) {
                    if (this.items.length > 1) {
                        this.items.splice(index, 1);
                    } else {
                        alert('Minimal harus mengisi 1 item barang pada nota!');
                    }
                },

                updateRowSubtotal(item) {
                    const qty = parseFloat(item.quantity) || 0;
                    const price = parseFloat(item.unit_price) || 0;
                    const raw = qty * price;
                    let discount = parseFloat(item.item_discount) || 0;
                    if (discount < 0) discount = 0;
                    if (discount > raw) discount = raw;
                    item.subtotal = Math.max(0, raw - discount);
                    return item.subtotal;
                },

                getItemSubtotal(item) {
                    const qty = parseFloat(item.quantity) || 0;
                    const price = parseFloat(item.unit_price) || 0;
                    const raw = qty * price;
                    let discount = parseFloat(item.item_discount) || 0;
                    if (discount < 0) discount = 0;
                    if (discount > raw) discount = raw;
                    return Math.max(0, raw - discount);
                },

                get subtotalKotor() {
                    return this.items.reduce((sum, item) => {
                        const qty = parseFloat(item.quantity) || 0;
                        const price = parseFloat(item.unit_price) || 0;
                        return sum + (qty * price);
                    }, 0);
                },

                get totalItemDiscount() {
                    return this.items.reduce((sum, item) => {
                        const qty = parseFloat(item.quantity) || 0;
                        const price = parseFloat(item.unit_price) || 0;
                        const raw = qty * price;
                        let disc = parseFloat(item.item_discount) || 0;
                        if (disc < 0) disc = 0;
                        if (disc > raw) disc = raw;
                        return sum + disc;
                    }, 0);
                },

                get subtotalItemsNet() {
                    return this.items.reduce((sum, item) => sum + this.getItemSubtotal(item), 0);
                },

                get totalDiscount() {
                    return this.totalItemDiscount + (parseFloat(this.globalDiscount) || 0);
                },

                get grandTotal() {
                    const netItems = this.subtotalItemsNet;
                    const gDisc = parseFloat(this.globalDiscount) || 0;
                    const ship = parseFloat(this.shippingFee) || 0;
                    return Math.max(0, netItems - gDisc) + ship;
                },

                get remainingBalance() {
                    if (this.paymentSource === 'personal_advance') {
                        return this.currentBalance; // Saldo kas proyek tetap utuh jika ditalangi pribadi!
                    }
                    return this.currentBalance - this.grandTotal;
                },

                get isBalanceInsufficient() {
                    if (this.paymentSource === 'personal_advance') {
                        return false;
                    }
                    return this.grandTotal > this.currentBalance;
                },

                formatRupiah(num) {
                    return 'Rp ' + Math.round(num || 0).toLocaleString('id-ID');
                },

                onProjectChange(e) {
                    const selectEl = e.target;
                    const opt = selectEl.options[selectEl.selectedIndex];
                    if (opt && opt.value) {
                        this.currentBalance = parseFloat(opt.dataset.balance) || 0;
                        this.projectName = opt.dataset.name || opt.text;
                    } else {
                        this.currentBalance = 0;
                        this.projectName = '';
                    }
                },

                onItemInput(item, e) {
                    const val = (e.target.value || '').trim().toLowerCase();
                    if (!val) return;
                    const list = document.getElementById('material-list');
                    if (!list || !list.options) return;
                    for (let i = 0; i < list.options.length; i++) {
                        const opt = list.options[i];
                        if ((opt.value || '').trim().toLowerCase() === val) {
                            if (opt.dataset.unit && !item.unit_name) {
                                item.unit_name = opt.dataset.unit;
                            }
                            break;
                        }
                    }
                }
            };
        }

        window.notaReceiptForm = notaReceiptForm;

        if (window.Alpine) {
            window.Alpine.data('notaReceiptForm', notaReceiptForm);
        } else {
            document.addEventListener('alpine:init', () => {
                window.Alpine.data('notaReceiptForm', notaReceiptForm);
            });
        }
    </script>
</x-app-layout>