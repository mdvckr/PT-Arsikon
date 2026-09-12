@php
    $activeTpl = $printTemplate ?? null;
    $templateUrl = $activeTpl ? $activeTpl->url : asset('assets/kop-surat-a4.png');
    $padTop = $activeTpl ? $activeTpl->padding_top_mm : 45.0;
    $padLeft = $activeTpl ? $activeTpl->padding_left_mm : 14.0;
    $padRight = $activeTpl ? $activeTpl->padding_right_mm : 14.0;
    $padBottom = $activeTpl ? $activeTpl->padding_bottom_mm : 22.0;
@endphp

<x-app-layout>
    <x-slot name="title">Detail PR: {{ $procurement->pr_number }}</x-slot>

    <div class="breadcrumb pr-no-print">
        <a href="{{ route('procurement.index') }}">Permintaan Pengadaan</a>
        <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:10px;"></i></span>
        <span>{{ $procurement->pr_number }}</span>
    </div>

    {{-- Action Bar --}}
    <div class="pr-action-bar pr-no-print">
        <div class="pr-status-pill status-{{ $procurement->status_color }}">
            {{ $procurement->status_label }}
        </div>
        <div class="pr-action-buttons">
            @if(isset($allTemplates) && $allTemplates->count() > 0)
            <div style="display:inline-flex;align-items:center;background:#fff;border:1px solid #cbd5e1;padding:4px 10px;border-radius:6px;">
                <select id="templateSelector" onchange="switchTemplate(this)" style="border:none;background:transparent;font-size:12px;font-weight:600;color:#334155;outline:none;cursor:pointer;">
                    @foreach($allTemplates as $tpl)
                    <option value="{{ $tpl->url }}"
                        data-top="{{ $tpl->padding_top_mm }}"
                        data-left="{{ $tpl->padding_left_mm }}"
                        data-right="{{ $tpl->padding_right_mm }}"
                        data-bottom="{{ $tpl->padding_bottom_mm }}"
                        {{ ($activeTpl && $activeTpl->id === $tpl->id) ? 'selected' : '' }}>
                        {{ $tpl->name }} {{ $tpl->used_for_pr ? '★' : '' }}
                    </option>
                    @endforeach
                </select>
            </div>
            @endif

            @can('approve procurement')
                @if($procurement->status === 'submitted')
                <form method="POST" action="{{ route('procurement.approve', $procurement) }}" style="display:inline;">
                    @csrf
                    <button type="submit" class="pr-btn pr-btn-success" onclick="return confirm('Setujui PR ini?')">
                        Setujui
                    </button>
                </form>
                <button type="button" class="pr-btn pr-btn-danger" onclick="document.getElementById('rejectModal').classList.add('open')">
                    Tolak
                </button>
                @endif
            @endcan
            <button type="button" class="pr-btn pr-btn-print" onclick="window.print()">
                <i class="fas fa-print"></i> Cetak / PDF
            </button>
            <a href="{{ route('procurement.index') }}" class="pr-btn pr-btn-back">
                Kembali
            </a>
        </div>
    </div>

    {{-- PR Document Container (A4 Letterhead Sheet) --}}
    <div class="pr-doc-sheet-wrapper">
        <div class="pr-doc-sheet" id="printArea" style="--pad-top: {{ $padTop }}mm; --pad-left: {{ $padLeft }}mm; --pad-right: {{ $padRight }}mm; --pad-bottom: {{ $padBottom }}mm;">

            {{-- Background Letterhead Cover A4 --}}
            <div class="letterhead-bg">
                <img id="letterheadImage" src="{{ $templateUrl }}" alt="Kop Surat PT Arsikon Cipta Karya" class="letterhead-img">
            </div>

            {{-- Document Printable Content --}}
            <div class="doc-content">
                {{-- 1. JUDUL DOKUMEN --}}
                <div class="doc-title-section">
                    <h2 class="doc-title-main">PURCHASE REQUEST</h2>
                    <div class="doc-title-sub">(SURAT PERMINTAAN PENGADAAN)</div>
                    <div class="doc-number-main">No. {{ $procurement->pr_number }}</div>
                </div>

                {{-- 2. INFORMASI PR --}}
                <div class="doc-meta-section">
                    <table class="doc-meta-table">
                        <tr>
                            <td class="meta-label">Pemohon</td>
                            <td class="meta-colon">:</td>
                            <td class="meta-value"><strong>{{ $procurement->requester?->name ?? '-' }}</strong></td>
                            <td class="meta-label">Tanggal Dibuat</td>
                            <td class="meta-colon">:</td>
                            <td class="meta-value">{{ $procurement->created_at->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <td class="meta-label">Role / Jabatan</td>
                            <td class="meta-colon">:</td>
                            <td class="meta-value">{{ $procurement->requester?->roles?->first()?->name ?? 'Staff' }}</td>
                            <td class="meta-label">Dibutuhkan Sebelum</td>
                            <td class="meta-colon">:</td>
                            <td class="meta-value">{{ $procurement->needed_by ? $procurement->needed_by->format('d/m/Y') : '-' }}</td>
                        </tr>
                        <tr>
                            <td class="meta-label">Status Dokumen</td>
                            <td class="meta-colon">:</td>
                            <td class="meta-value">{{ $procurement->status_label }}</td>
                            <td class="meta-label">Disetujui Oleh</td>
                            <td class="meta-colon">:</td>
                            <td class="meta-value">{{ $procurement->approver?->name ?? '-' }}</td>
                        </tr>
                    </table>

                    @if($procurement->justification)
                    <div class="doc-notes-box">
                        <span class="notes-title">Justifikasi / Alasan:</span> {{ $procurement->justification }}
                    </div>
                    @endif

                    @if($procurement->rejection_reason)
                    <div class="doc-notes-box doc-rejection-box">
                        <span class="notes-title" style="color: #dc2626;">Alasan Penolakan:</span> {{ $procurement->rejection_reason }}
                    </div>
                    @endif
                </div>

                {{-- 3. TABEL MATERIAL / ITEM --}}
                <table class="doc-items-table">
                    <thead>
                        <tr>
                            <th style="width: 36px; text-align: center;">NO</th>
                            <th style="text-align: left;">JENIS MATERIAL</th>
                            <th style="width: 80px; text-align: right;">JUMLAH</th>
                            <th style="width: 130px; text-align: right;">HARGA SATUAN</th>
                            <th style="width: 140px; text-align: right;">TOTAL</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $grandTotal = 0; @endphp
                        @foreach($procurement->items as $i => $item)
                        @php
                            $lineTotal = ($item->estimated_price ?? 0) * $item->quantity;
                            $grandTotal += $lineTotal;
                        @endphp
                        <tr>
                            <td style="text-align: center;">{{ $i + 1 }}</td>
                            <td>
                                <div class="item-name">{{ $item->material?->name ?? '-' }}</div>
                                @if($item->material?->code)
                                <div class="item-code">Kode: {{ $item->material->code }}</div>
                                @endif
                            </td>
                            <td style="text-align: right;">{{ number_format($item->quantity, 2, ',', '.') }}</td>
                            <td style="text-align: right;">
                                @if($item->estimated_price)
                                    Rp {{ number_format($item->estimated_price, 0, ',', '.') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td style="text-align: right; font-weight: 600;">
                                @if($lineTotal > 0)
                                    Rp {{ number_format($lineTotal, 0, ',', '.') }}
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="tfoot-total">
                            <td colspan="4" class="tfoot-label">ESTIMASI TOTAL</td>
                            <td class="tfoot-val">Rp {{ number_format($grandTotal, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>

                {{-- 4. TANDA TANGAN (Signatures) --}}
                <div class="doc-signatures-section">
                    <div class="sig-col">
                        <div class="sig-role">Menyetujui,</div>
                        <div class="sig-space"></div>
                        <div class="sig-name">( {{ $procurement->approver?->name ?? '..................................' }} )</div>
                        <div class="sig-desc">Manajer / Direksi</div>
                    </div>
                    <div class="sig-col">
                        <div class="sig-role">Mengetahui,</div>
                        <div class="sig-space"></div>
                        <div class="sig-name">( .................................. )</div>
                        <div class="sig-desc">Staff Purchasing</div>
                    </div>
                    <div class="sig-col">
                        <div class="sig-role">Pemohon,</div>
                        <div class="sig-space"></div>
                        <div class="sig-name">( {{ $procurement->requester?->name ?? '..................................' }} )</div>
                        <div class="sig-desc">{{ $procurement->requester?->roles?->first()?->name ?? 'Staff Gudang' }}</div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- Reject Modal --}}
    <div class="modal-overlay pr-no-print" id="rejectModal">
        <div class="modal-box">
            <div class="modal-header">
                <i class="fas fa-times-circle" style="color:#ef4444;font-size:20px;"></i>
                <h3>Tolak Permintaan Pengadaan</h3>
            </div>
            <form method="POST" action="{{ route('procurement.reject', $procurement) }}">
                @csrf
                <div class="modal-body">
                    <label class="form-label" style="display:block;margin-bottom:6px;font-weight:600;font-size:13px;">Alasan Penolakan <span style="color:#ef4444;">*</span></label>
                    <textarea name="rejection_reason" class="form-control" rows="4" required
                        style="width:100%;border:1px solid #cbd5e1;border-radius:6px;padding:8px 10px;font-family:inherit;font-size:13px;"
                        placeholder="Jelaskan alasan penolakan PR ini..."></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="pr-btn pr-btn-back" onclick="document.getElementById('rejectModal').classList.remove('open')">Batal</button>
                    <button type="submit" class="pr-btn pr-btn-danger"><i class="fas fa-times"></i> Tolak Dokumen</button>
                </div>
            </form>
        </div>
    </div>

    @push('styles')
    <style>
        /* ===== Action Bar (Screen Only) ===== */
        .pr-action-bar {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 20px; flex-wrap: wrap; gap: 12px;
            max-width: 210mm; margin-left: auto; margin-right: auto;
        }
        .pr-action-buttons { display: flex; gap: 10px; flex-wrap: wrap; }
        .pr-status-pill {
            display: inline-flex; align-items: center; gap: 7px;
            padding: 6px 16px; border-radius: 30px; font-size: 13px; font-weight: 700;
        }
        .status-warning { background: #fef3c7; color: #92400e; }
        .status-success { background: #d1fae5; color: #065f46; }
        .status-danger  { background: #fee2e2; color: #991b1b; }
        .status-primary { background: #ffedd5; color: #9a3412; }

        .pr-btn {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 7px 14px; border-radius: 6px; font-size: 13px; font-weight: 600;
            cursor: pointer; border: 1px solid transparent; text-decoration: none; transition: all .15s ease;
        }
        .pr-btn-success { background: #15803d; color: #fff; }
        .pr-btn-success:hover { background: #166534; }
        .pr-btn-danger  { background: #ffffff; color: #dc2626; border-color: #fca5a5; }
        .pr-btn-danger:hover  { background: #fef2f2; }
        .pr-btn-print   { background: #ffffff; color: #0f172a; border-color: #cbd5e1; }
        .pr-btn-print:hover   { background: #f8fafc; border-color: #94a3b8; }
        .pr-btn-back    { background: #ffffff; color: #475569; border-color: #cbd5e1; }
        .pr-btn-back:hover    { background: #f8fafc; color: #0f172a; }

        /* ===== A4 Document Wrapper & Sheet ===== */
        .pr-doc-sheet-wrapper {
            display: flex;
            justify-content: center;
            width: 100%;
            overflow-x: auto;
            padding-bottom: 30px;
        }

        .pr-doc-sheet {
            position: relative;
            width: 210mm;
            min-height: 297mm;
            background: #ffffff;
            border-radius: 4px;
            box-shadow: 0 4px 25px rgba(0, 0, 0, 0.12);
            box-sizing: border-box;
            overflow: hidden;
        }

        /* Background letterhead template image */
        .letterhead-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            pointer-events: none;
        }
        .letterhead-img {
            width: 100%;
            height: 100%;
            object-fit: fill;
            display: block;
        }

        /* Document printable content area (clearing the top header line) */
        .doc-content {
            position: relative;
            z-index: 1;
            padding-top: var(--pad-top, 45mm);
            padding-left: var(--pad-left, 14mm);
            padding-right: var(--pad-right, 14mm);
            padding-bottom: var(--pad-bottom, 22mm);
            box-sizing: border-box;
        }

        /* ===== JUDUL DOKUMEN ===== */
        .doc-title-section {
            text-align: center;
            margin-bottom: 14px;
        }
        .doc-title-main {
            font-size: 16px;
            font-weight: 800;
            color: #000000;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin: 0;
            text-decoration: underline;
        }
        .doc-title-sub {
            font-size: 11px;
            font-weight: 600;
            color: #334155;
            letter-spacing: 0.04em;
            margin-top: 2px;
        }
        .doc-number-main {
            font-size: 12px;
            color: #1e293b;
            margin-top: 3px;
            font-weight: 700;
        }

        /* ===== INFORMASI PR (META) ===== */
        .doc-meta-section {
            margin-bottom: 14px;
        }
        .doc-meta-table {
            width: 100%;
            border-collapse: collapse;
        }
        .doc-meta-table td {
            padding: 3px 5px;
            font-size: 12px;
            vertical-align: top;
        }
        .meta-label { color: #334155; width: 135px; font-weight: 600; }
        .meta-colon { color: #334155; width: 10px; text-align: center; }
        .meta-value { color: #000000; }
        .doc-notes-box {
            margin-top: 8px;
            padding: 6px 10px;
            background: rgba(248, 250, 252, 0.85);
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            font-size: 11.5px;
            color: #1e293b;
        }
        .doc-rejection-box {
            background: rgba(254, 242, 242, 0.9);
            border-color: #fca5a5;
            color: #991b1b;
        }
        .notes-title { font-weight: 700; margin-right: 4px; }

        /* ===== TABEL ITEM ===== */
        .doc-items-table {
            width: 100%;
            margin-bottom: 18px;
            border-collapse: collapse;
            font-size: 11.5px;
            border: 1px solid #1e293b;
            background: rgba(255, 255, 255, 0.75); /* Translucent so watermark is visible */
        }
        .doc-items-table th {
            background: rgba(241, 245, 249, 0.95);
            border: 1px solid #1e293b;
            padding: 6px 8px;
            font-size: 11px;
            font-weight: 700;
            color: #000000;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }
        .doc-items-table td {
            border: 1px solid #1e293b;
            padding: 6px 8px;
            color: #000000;
            vertical-align: middle;
        }
        .item-name { font-weight: 600; color: #000000; }
        .item-code { font-size: 10px; color: #475569; margin-top: 1px; }
        .tfoot-total td {
            border: 1px solid #1e293b;
            padding: 8px 10px;
            font-weight: 800;
            background: rgba(241, 245, 249, 0.95);
            font-size: 12px;
        }
        .tfoot-label { text-align: right; letter-spacing: 0.03em; }
        .tfoot-val { text-align: right; }

        /* ===== SIGNATURES ===== */
        .doc-signatures-section {
            display: flex;
            justify-content: space-between;
            margin-top: 24px;
            page-break-inside: avoid;
        }
        .sig-col { width: 30%; text-align: center; }
        .sig-role { font-size: 11.5px; font-weight: 600; color: #000000; margin-bottom: 4px; }
        .sig-space { height: 55px; }
        .sig-name {
            font-size: 11.5px; font-weight: 700; color: #000000;
            display: inline-block; padding-bottom: 2px; min-width: 140px;
        }
        .sig-desc { font-size: 10px; color: #475569; margin-top: 3px; }

        /* Modal */
        .modal-overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(15,23,42,.5); z-index: 9999;
            align-items: center; justify-content: center;
            backdrop-filter: blur(4px);
        }
        .modal-overlay.open { display: flex; }
        .modal-box {
            background: #fff; border-radius: 12px;
            width: 100%; max-width: 460px;
            box-shadow: 0 20px 60px rgba(0,0,0,.2);
        }
        .modal-header {
            padding: 18px 22px; border-bottom: 1px solid #f1f5f9;
            display: flex; align-items: center; gap: 10px;
        }
        .modal-header h3 { font-size: 16px; font-weight: 700; color: #1e293b; margin: 0; }
        .modal-body { padding: 18px 22px; }
        .modal-footer {
            padding: 14px 22px; border-top: 1px solid #f1f5f9;
            display: flex; justify-content: flex-end; gap: 10px;
        }

        /* ===== SCREEN RESPONSIVE (Mobile / Tablet) ===== */
        @media screen and (max-width: 768px) {
            .pr-action-bar {
                flex-direction: column;
                align-items: stretch;
                gap: 12px;
            }
            .pr-action-buttons {
                flex-direction: column;
                width: 100%;
            }
            .pr-action-buttons .pr-btn,
            .pr-action-buttons a,
            .pr-action-buttons form,
            .pr-action-buttons button {
                width: 100%;
                justify-content: center;
                box-sizing: border-box;
            }
            .pr-doc-sheet-wrapper {
                justify-content: flex-start;
                padding-bottom: 20px;
                -webkit-overflow-scrolling: touch;
            }
            .pr-doc-sheet {
                box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            }
        }

        /* ===== PRINT MEDIA STYLES (A4 PDF / Cetak) ===== */
        @media print {
            @page {
                size: A4 portrait;
                margin: 0mm !important;
            }

            html, body {
                background: #ffffff !important;
                color: #000000 !important;
                margin: 0 !important;
                padding: 0 !important;
                width: 210mm !important;
                height: 297mm !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .pr-no-print, .topbar, .sidebar, .breadcrumb,
            .modal-overlay, nav, header, aside {
                display: none !important;
            }

            .main-wrapper, .page-content {
                margin: 0 !important;
                padding: 0 !important;
                display: block !important;
                width: 210mm !important;
                min-height: 0 !important;
                border: none !important;
                box-shadow: none !important;
                background: transparent !important;
            }

            .pr-doc-sheet-wrapper {
                padding: 0 !important;
                margin: 0 !important;
                display: block !important;
                overflow: visible !important;
            }

            .pr-doc-sheet {
                position: relative !important;
                width: 210mm !important;
                min-height: 297mm !important;
                height: 297mm !important;
                box-shadow: none !important;
                border: none !important;
                border-radius: 0 !important;
                margin: 0 !important;
                padding: 0 !important;
                background: #ffffff !important;
                page-break-after: avoid !important;
                overflow: hidden !important;
            }

            .letterhead-bg {
                position: absolute !important;
                top: 0 !important;
                left: 0 !important;
                width: 210mm !important;
                height: 297mm !important;
                z-index: 0 !important;
                display: block !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .letterhead-img {
                width: 210mm !important;
                height: 297mm !important;
                display: block !important;
                object-fit: fill !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .doc-content {
                position: relative !important;
                z-index: 1 !important;
                padding-top: var(--pad-top, 45mm) !important;
                padding-left: var(--pad-left, 14mm) !important;
                padding-right: var(--pad-right, 14mm) !important;
                padding-bottom: var(--pad-bottom, 22mm) !important;
            }

            .doc-items-table {
                border: 1px solid #000000 !important;
            }
            .doc-items-table th, .doc-items-table td {
                border: 1px solid #000000 !important;
                color: #000000 !important;
            }
            .doc-items-table thead th {
                background: #f1f5f9 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .doc-items-table tr { page-break-inside: avoid; }
            .doc-signatures-section { page-break-inside: avoid; }
        }
    </style>
    @endpush

    @push('scripts')
    <script>
        function switchTemplate(select) {
            const opt = select.options[select.selectedIndex];
            const imgUrl = opt.value;
            const top = opt.dataset.top || 45;
            const left = opt.dataset.left || 14;
            const right = opt.dataset.right || 14;
            const bottom = opt.dataset.bottom || 22;

            const img = document.getElementById('letterheadImage');
            if (img) img.src = imgUrl;

            const sheet = document.getElementById('printArea');
            if (sheet) {
                sheet.style.setProperty('--pad-top', top + 'mm');
                sheet.style.setProperty('--pad-left', left + 'mm');
                sheet.style.setProperty('--pad-right', right + 'mm');
                sheet.style.setProperty('--pad-bottom', bottom + 'mm');
            }
        }
    </script>
    @endpush
</x-app-layout>