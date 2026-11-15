<?php

namespace App\Exports;

use App\Models\Project;
use App\Models\PurchaseOrder;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class WeeklyPurchaseOrderExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithCustomStartCell, WithDrawings
{
    protected Collection $orders;
    protected Carbon $startDate;
    protected Carbon $endDate;
    protected ?Project $project;
    protected bool $isBlind;
    protected ?Collection $cachedRows = null;
    protected array $drawingsList = [];
    protected array $photoRowDimensions = [];
    protected array $weekHeaderRows = [];
    protected array $weekSubtotalRows = [];
    protected ?int $grandTotalRow = null;

    public function __construct(
        Collection $orders,
        ?Carbon $startDate = null,
        ?Carbon $endDate = null,
        ?Project $project = null,
        bool $isBlind = false
    ) {
        $this->orders    = $orders;
        $this->startDate = $startDate ?? now()->startOfWeek(Carbon::MONDAY);
        $this->endDate   = $endDate ?? now()->endOfWeek(Carbon::SUNDAY);
        $this->project   = $project;
        $this->isBlind   = $isBlind;
    }

    public function collection(): Collection
    {
        if ($this->cachedRows !== null) {
            return $this->cachedRows;
        }

        $rows = collect();
        $this->drawingsList = [];
        $this->photoRowDimensions = [];
        $this->weekHeaderRows = [];
        $this->weekSubtotalRows = [];

        // Start data row index in Excel (Header table is at row 5, so data starts at row 6)
        $currentRow = 6;

        // Group orders by week label
        $groupedOrders = $this->orders->groupBy(function (PurchaseOrder $po) {
            $dt = $po->order_date ? Carbon::parse($po->order_date, 'Asia/Jakarta') : Carbon::now('Asia/Jakarta');
            $weekStart = $dt->copy()->startOfWeek(Carbon::MONDAY);
            $weekEnd   = $dt->copy()->endOfWeek(Carbon::SUNDAY);
            $weekNum   = $po->week_number ?: $dt->weekOfMonth;
            return "MINGGU KE-{$weekNum} ({$weekStart->format('d/m/Y')} — {$weekEnd->format('d/m/Y')})";
        });

        $globalPoIndex = 0;

        foreach ($groupedOrders as $weekLabel => $ordersInWeek) {
            // 1. Week Header Row
            $this->weekHeaderRows[] = $currentRow;
            $rows->push([
                'type'             => 'week_header',
                'no'               => $weekLabel,
                'tanggal'          => '',
                'no_po'            => '',
                'supplier'         => '',
                'proyek'           => '',
                'item_name'        => '',
                'quantity'         => '',
                'unit_name'        => '',
                'unit_price'       => '',
                'subtotal'         => '',
                'received_qty'     => '',
                'outstanding_qty'  => '',
                'status'           => '',
                'total_amount'     => '',
                'foto'             => '',
                'keterangan'       => '',
            ]);
            $currentRow++;

            $weekItemStartRow = $currentRow;
            $weekTotalAmount = 0.0;

            foreach ($ordersInWeek as $po) {
                $globalPoIndex++;
                $items = $po->items;
                $itemCount = $items->count();

                $dateStr = $po->order_date ? Carbon::parse($po->order_date)->format('d/m/Y') : '-';
                $poNumber = $po->po_number ?? ('PO-' . $po->id);
                if ($po->material_request_id) {
                    $poNumber .= "\n(MR: #" . $po->material_request_id . ')';
                } elseif ($po->procurement_request_id) {
                    $poNumber .= "\n(PR: #" . $po->procurement_request_id . ')';
                }

                $supplierName = $po->display_supplier_name;
                if (!empty($po->contact_person)) {
                    $supplierName .= "\nPIC: " . $po->contact_person;
                }

                $projectName = $po->project ? $po->project->name : ($po->deliveryWarehouse ? $po->deliveryWarehouse->name : '-');
                $poTotal = $this->isBlind ? 0.0 : (float) $po->total_amount;
                $weekTotalAmount += $poTotal;
                $notes = $po->notes ?: '-';

                // Handle Photo / Drawing
                $photoCellText = '-';
                $physicalPath = $this->resolveAttachmentPhysicalPath($po->attachment_path);

                if ($physicalPath && file_exists($physicalPath)) {
                    $ext = strtolower(pathinfo($physicalPath, PATHINFO_EXTENSION));
                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
                        try {
                            $drawing = new Drawing();
                            $drawing->setName('Foto PO ' . ($po->po_number ?? $po->id));
                            $drawing->setDescription('Lampiran Berkas PO');
                            $drawing->setPath($physicalPath);
                            $drawing->setHeight(50);
                            $drawing->setCoordinates('O' . $currentRow);
                            $drawing->setOffsetX(4);
                            $drawing->setOffsetY(4);
                            $this->drawingsList[] = $drawing;

                            $this->photoRowDimensions[$currentRow] = 52;
                            $photoCellText = ''; // Cell contains the image
                        } catch (\Throwable $e) {
                            $photoCellText = '[Gagal Memuat Foto]';
                        }
                    } elseif ($ext === 'pdf') {
                        $photoCellText = '[Dokumen PDF: ' . basename($po->attachment_path) . ']';
                    } else {
                        $photoCellText = '[' . strtoupper($ext) . ' File]';
                    }
                } elseif (!empty($po->attachment_path)) {
                    $photoCellText = '[Lampiran Tersimpan: ' . basename($po->attachment_path) . ']';
                }

                if ($itemCount === 0) {
                    $rows->push([
                        'type'             => 'data',
                        'no'               => $globalPoIndex,
                        'tanggal'          => $dateStr,
                        'no_po'            => $poNumber,
                        'supplier'         => $supplierName,
                        'proyek'           => $projectName,
                        'item_name'        => '(Tidak ada rincian item)',
                        'quantity'         => 0,
                        'unit_name'        => '-',
                        'unit_price'       => $this->isBlind ? 0 : $poTotal,
                        'subtotal'         => $this->isBlind ? 0 : $poTotal,
                        'received_qty'     => 0,
                        'outstanding_qty'  => 0,
                        'status'           => $po->status_label,
                        'total_amount'     => $this->isBlind ? 0 : $poTotal,
                        'foto'             => $photoCellText,
                        'keterangan'       => $notes,
                    ]);
                    $currentRow++;
                } else {
                    foreach ($items as $idx => $item) {
                        $isFirstItem = ($idx === 0);
                        $qty = (float) $item->quantity;
                        $unitPrice = $this->isBlind ? 0.0 : (float) $item->unit_price;
                        $subtotal = $this->isBlind ? 0.0 : (float) ($item->subtotal ?: ($qty * $unitPrice));
                        $receivedQty = (float) ($item->received_qty ?? 0);
                        $outstandingQty = (float) ($item->outstanding ?? max(0, $qty - $receivedQty));

                        $itemName = $item->displayName();
                        if ($item->notes) {
                            $itemName .= "\nKet: " . $item->notes;
                        }

                        $rows->push([
                            'type'             => 'data',
                            'no'               => $isFirstItem ? $globalPoIndex : '',
                            'tanggal'          => $isFirstItem ? $dateStr : '',
                            'no_po'            => $isFirstItem ? $poNumber : '',
                            'supplier'         => $isFirstItem ? $supplierName : '',
                            'proyek'           => $isFirstItem ? $projectName : '',
                            'item_name'        => $itemName,
                            'quantity'         => $qty,
                            'unit_name'        => $item->displayUnit(),
                            'unit_price'       => $unitPrice,
                            'subtotal'         => $subtotal,
                            'received_qty'     => $receivedQty,
                            'outstanding_qty'  => $outstandingQty,
                            'status'           => $isFirstItem ? $po->status_label : '',
                            'total_amount'     => $isFirstItem ? $poTotal : '',
                            'foto'             => $isFirstItem ? $photoCellText : '',
                            'keterangan'       => $isFirstItem ? $notes : '',
                        ]);
                        $currentRow++;
                    }
                }
            }

            // Week Subtotal Row
            $this->weekSubtotalRows[] = [
                'row'      => $currentRow,
                'label'    => "SUBTOTAL {$weekLabel}:",
                'amount'   => $weekTotalAmount,
                'startRow' => $weekItemStartRow,
                'endRow'   => $currentRow - 1,
            ];

            $rows->push([
                'type'             => 'week_subtotal',
                'no'               => "SUBTOTAL {$weekLabel}:",
                'tanggal'          => '',
                'no_po'            => '',
                'supplier'         => '',
                'proyek'           => '',
                'item_name'        => '',
                'quantity'         => '',
                'unit_name'        => '',
                'unit_price'       => '',
                'subtotal'         => '',
                'received_qty'     => '',
                'outstanding_qty'  => '',
                'status'           => '',
                'total_amount'     => $this->isBlind ? 0.0 : $weekTotalAmount,
                'foto'             => '',
                'keterangan'       => '',
            ]);
            $currentRow++;
        }

        // Grand Total Row
        if ($this->orders->isNotEmpty()) {
            $this->grandTotalRow = $currentRow;
            $grandTotalAmount = $this->isBlind ? 0.0 : (float) $this->orders->sum('total_amount');
            $rows->push([
                'type'             => 'grand_total',
                'no'               => 'GRAND TOTAL REKAPITULASI KESELURUHAN:',
                'tanggal'          => '',
                'no_po'            => '',
                'supplier'         => '',
                'proyek'           => '',
                'item_name'        => '',
                'quantity'         => '',
                'unit_name'        => '',
                'unit_price'       => '',
                'subtotal'         => '',
                'received_qty'     => '',
                'outstanding_qty'  => '',
                'status'           => '',
                'total_amount'     => $grandTotalAmount,
                'foto'             => '',
                'keterangan'       => '',
            ]);
        }

        $this->cachedRows = $rows;
        return $this->cachedRows;
    }

    public function startCell(): string
    {
        return 'A5';
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal PO',
            'No. PO / Ref',
            'Supplier / Vendor',
            'Proyek / Gudang',
            'Barang / Material',
            'Vol (Pesan)',
            'Satuan',
            'Harga Satuan (Rp)',
            'Subtotal (Rp)',
            'Vol Diterima',
            'Outstanding',
            'Status PO',
            'Total PO (Rp)',
            'Foto Berkas / Lampiran',
            'Keterangan',
        ];
    }

    public function map($row): array
    {
        return [
            $row['no'],
            $row['tanggal'],
            $row['no_po'],
            $row['supplier'],
            $row['proyek'],
            $row['item_name'],
            $row['quantity'] !== '' ? (float) $row['quantity'] : '',
            $row['unit_name'],
            $row['unit_price'] !== '' ? (float) $row['unit_price'] : '',
            $row['subtotal'] !== '' ? (float) $row['subtotal'] : '',
            $row['received_qty'] !== '' ? (float) $row['received_qty'] : '',
            $row['outstanding_qty'] !== '' ? (float) $row['outstanding_qty'] : '',
            $row['status'],
            $row['total_amount'] !== '' ? (float) $row['total_amount'] : '',
            $row['foto'],
            $row['keterangan'],
        ];
    }

    public function drawings(): array
    {
        $this->collection();
        return $this->drawingsList;
    }

    public function styles(Worksheet $sheet)
    {
        $this->collection();

        // 1. Kop Dokumen Korporat (Row 1-3)
        $sheet->mergeCells('A1:P1');
        $sheet->setCellValue('A1', 'PT. ARSIKON CIPTA KARYA');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF0F172A'));
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A2:P2');
        $sheet->setCellValue('A2', 'REKAPITULASI PURCHASE ORDER MINGGUAN (DILENGKAPI BUKTI LAMPIRAN FOTO)');
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(11)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF1E293B'));
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A3:P3');
        $projectName = $this->project ? $this->project->name : 'Semua Proyek';
        $periodText = $this->startDate->translatedFormat('d F Y') . ' s/d ' . $this->endDate->translatedFormat('d F Y');
        $sheet->setCellValue('A3', "Proyek: {$projectName} | Periode: {$periodText} | Dicetak: " . now('Asia/Jakarta')->translatedFormat('d F Y H:i') . ' WIB');
        $sheet->getStyle('A3')->getFont()->setSize(9.5)->setItalic(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF64748B'));
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // 2. Table Headers (Row 5)
        $headerRange = 'A5:P5';
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '0F172A'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
                'wrapText'   => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['rgb' => '334155'],
                ],
            ],
        ]);
        $sheet->getRowDimension(5)->setRowHeight(30);

        // 3. Set Custom Row Heights for Photos
        foreach ($this->photoRowDimensions as $rowIdx => $height) {
            $sheet->getRowDimension($rowIdx)->setRowHeight($height);
        }

        // 4. Styling Week Header Rows
        foreach ($this->weekHeaderRows as $wRow) {
            $sheet->mergeCells("A{$wRow}:P{$wRow}");
            $sheet->getStyle("A{$wRow}:P{$wRow}")->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1E293B'], // Slate navy
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'vertical'   => Alignment::VERTICAL_CENTER,
                    'indent'     => 1,
                ],
            ]);
            $sheet->getRowDimension($wRow)->setRowHeight(24);
        }

        // 5. Styling Week Subtotal Rows
        foreach ($this->weekSubtotalRows as $sub) {
            $sRow = $sub['row'];
            $sheet->mergeCells("A{$sRow}:M{$sRow}");
            $sheet->getStyle("A{$sRow}:P{$sRow}")->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => '0F172A'], 'size' => 9.5],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E2E8F0'], // Slate 200
                ],
                'borders' => [
                    'top'    => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '94A3B8']],
                    'bottom' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '64748B']],
                ],
            ]);
            $sheet->getStyle("A{$sRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("N{$sRow}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getRowDimension($sRow)->setRowHeight(22);
        }

        // 6. Styling Grand Total Row
        if ($this->grandTotalRow) {
            $gRow = $this->grandTotalRow;
            $sheet->mergeCells("A{$gRow}:M{$gRow}");
            $sheet->getStyle("A{$gRow}:P{$gRow}")->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10.5],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '0F766E'], // Teal 700
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '115E59']],
                ],
            ]);
            $sheet->getStyle("A{$gRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("N{$gRow}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getRowDimension($gRow)->setRowHeight(26);
        }

        // 7. General Data Table Styling
        $totalDataRows = $this->cachedRows->count();
        if ($totalDataRows > 0) {
            $startDataRow = 6;
            $endDataRow   = 5 + $totalDataRows;
            $dataRange    = "A{$startDataRow}:P{$endDataRow}";

            $sheet->getStyle($dataRange)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle($dataRange)->getAlignment()->setWrapText(true);

            // Perataan kolom
            $sheet->getStyle("A{$startDataRow}:A{$endDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("B{$startDataRow}:B{$endDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("H{$startDataRow}:H{$endDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("M{$startDataRow}:M{$endDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("O{$startDataRow}:O{$endDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Numerik kuantitas
            $sheet->getStyle("G{$startDataRow}:G{$endDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("K{$startDataRow}:L{$endDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("G{$startDataRow}:G{$endDataRow}")->getNumberFormat()->setFormatCode('#,##0.##');
            $sheet->getStyle("K{$startDataRow}:L{$endDataRow}")->getNumberFormat()->setFormatCode('#,##0.##');

            // Numerik rupiah
            $sheet->getStyle("I{$startDataRow}:J{$endDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("N{$startDataRow}:N{$endDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("I{$startDataRow}:J{$endDataRow}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("N{$startDataRow}:N{$endDataRow}")->getNumberFormat()->setFormatCode('#,##0');

            // Borders tipis
            $sheet->getStyle($dataRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFCBD5E1'));
        }

        // Column widths hint
        $sheet->getColumnDimension('A')->setWidth(6);
        $sheet->getColumnDimension('B')->setWidth(14);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(24);
        $sheet->getColumnDimension('E')->setWidth(20);
        $sheet->getColumnDimension('F')->setWidth(28);
        $sheet->getColumnDimension('G')->setWidth(13);
        $sheet->getColumnDimension('H')->setWidth(10);
        $sheet->getColumnDimension('I')->setWidth(16);
        $sheet->getColumnDimension('J')->setWidth(18);
        $sheet->getColumnDimension('K')->setWidth(14);
        $sheet->getColumnDimension('L')->setWidth(14);
        $sheet->getColumnDimension('M')->setWidth(16);
        $sheet->getColumnDimension('N')->setWidth(18);
        $sheet->getColumnDimension('O')->setWidth(22);
        $sheet->getColumnDimension('P')->setWidth(22);

        return [];
    }

    protected function resolveAttachmentPhysicalPath(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        // 1. Cek storage disk private
        if (Storage::disk('private')->exists($path)) {
            return Storage::disk('private')->path($path);
        }

        // 2. Cek storage disk public
        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->path($path);
        }

        // 3. Cek folder public/storage
        if (file_exists(public_path('storage/' . $path))) {
            return public_path('storage/' . $path);
        }

        // 4. Cek path absolut langsung
        if (file_exists($path)) {
            return $path;
        }

        return null;
    }
}
