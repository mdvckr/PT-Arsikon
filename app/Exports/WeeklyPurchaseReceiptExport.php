<?php

namespace App\Exports;

use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class WeeklyPurchaseReceiptExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithCustomStartCell
{
    protected Collection $receipts;
    protected ?Project $project;
    protected Carbon $startDate;
    protected Carbon $endDate;
    protected ?Collection $cachedRows = null;

    public function __construct(Collection $receipts, ?Project $project = null, ?Carbon $startDate = null, ?Carbon $endDate = null)
    {
        $this->receipts  = $receipts;
        $this->project   = $project;
        $this->startDate = $startDate ?? now()->startOfWeek(Carbon::MONDAY);
        $this->endDate   = $endDate ?? now()->endOfWeek(Carbon::SUNDAY);
    }

    public function collection(): Collection
    {
        if ($this->cachedRows !== null) {
            return $this->cachedRows;
        }

        $rows = collect();
        $receiptIndex = 0;

        foreach ($this->receipts as $receipt) {
            $receiptIndex++;
            $items = $receipt->items;
            $itemCount = $items->count();

            // Keterangan & Talangan
            $talanganText = '';
            if ($receipt->is_talangan) {
                $talanganText = 'Talangan: ' . ($receipt->paid_by_name ?? 'Staf Lapangan');
            }
            if ($receipt->notes) {
                $talanganText .= ($talanganText ? ' | ' : '') . $receipt->notes;
            }

            $dateStr = $receipt->receipt_date ? $receipt->receipt_date->format('d/m/Y') : '-';
            $noNota = $receipt->receipt_number ?? ('NOTA-' . $receipt->id);
            $supplierName = $receipt->supplier?->name ?? ($receipt->supplier_name ?? 'Toko Langsung');

            if ($itemCount === 0) {
                $rows->push([
                    'no'           => $receiptIndex,
                    'tanggal'      => $dateStr,
                    'no_nota'      => $noNota,
                    'supplier'     => $supplierName,
                    'item_type'    => 'Material',
                    'item_name'    => $receipt->notes ?? 'Belanja Lapangan',
                    'quantity'     => 1,
                    'unit_name'    => 'Ls',
                    'unit_price'   => (float) $receipt->total_amount,
                    'subtotal'     => (float) $receipt->total_amount,
                    'discount'     => (float) ($receipt->global_discount ?? 0),
                    'shipping_fee' => (float) ($receipt->shipping_fee ?? 0),
                    'total_amount' => (float) $receipt->total_amount,
                    'keterangan'   => $talanganText ?: '-',
                ]);
            } else {
                $totalItemDiscounts = (float) $items->sum('item_discount');
                $globalDisc = (float) ($receipt->global_discount ?? 0);
                $totalDiscNota = $totalItemDiscounts + $globalDisc;
                $shipFee = (float) ($receipt->shipping_fee ?? 0);
                $grandTotal = (float) $receipt->total_amount;

                foreach ($items as $idx => $it) {
                    $isFirst = ($idx === 0);
                    $isLast = ($idx === $itemCount - 1);

                    $qtyVal = (float) $it->quantity;
                    $priceVal = (float) $it->unit_price;
                    $subVal = (float) $it->subtotal;

                    $itemNote = $it->notes ?? '';
                    $rowKet = $isFirst ? $talanganText : '';
                    if ($itemNote) {
                        $rowKet .= ($rowKet ? ' | ' : '') . $itemNote;
                    }

                    $rows->push([
                        'no'           => $isFirst ? $receiptIndex : '',
                        'tanggal'      => $dateStr,
                        'no_nota'      => $noNota,
                        'supplier'     => $supplierName,
                        'item_type'    => $it->item_type ?: 'Material',
                        'item_name'    => $it->item_name ?: ($it->material_name ?: 'Item Belanja'),
                        'quantity'     => $qtyVal,
                        'unit_name'    => $it->unit_name ?: 'Bh',
                        'unit_price'   => $priceVal,
                        'subtotal'     => $subVal,
                        'discount'     => $isLast ? $totalDiscNota : '',
                        'shipping_fee' => $isLast ? $shipFee : '',
                        'total_amount' => $isLast ? $grandTotal : '',
                        'keterangan'   => $rowKet ?: '-',
                    ]);
                }
            }
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
            'Tanggal',
            'No. Nota / Ref',
            'Toko / Supplier',
            'Item',
            'Nama Barang',
            'Jumlah (Vol)',
            'Satuan',
            'Harga Satuan (Rp)',
            'Subtotal (Rp)',
            'Diskon Nota (Rp)',
            'Ongkir (Rp)',
            'Total Bersih (Rp)',
            'Keterangan / Talangan',
        ];
    }

    public function map($row): array
    {
        return [
            $row['no'],
            $row['tanggal'],
            $row['no_nota'],
            $row['supplier'],
            $row['item_type'],
            $row['item_name'],
            $row['quantity'],
            $row['unit_name'],
            $row['unit_price'],
            $row['subtotal'],
            $row['discount'] !== '' ? (float) $row['discount'] : '',
            $row['shipping_fee'] !== '' ? (float) $row['shipping_fee'] : '',
            $row['total_amount'] !== '' ? (float) $row['total_amount'] : '',
            $row['keterangan'],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // 1. Title Block (Row 1-3)
        $sheet->mergeCells('A1:N1');
        $sheet->setCellValue('A1', 'PT. ARSIKON CIPTA KARYA');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF0F172A'));
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A2:N2');
        $sheet->setCellValue('A2', 'REKAPITULASI LAPORAN NOTA BELANJA LAPANGAN (KAS OPERASIONAL)');
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(11)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF1E293B'));
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A3:N3');
        $projectName = $this->project ? $this->project->name : 'Semua Proyek';
        $sheet->setCellValue('A3', "Proyek: {$projectName} | Periode: " . $this->startDate->translatedFormat('d F Y') . ' s/d ' . $this->endDate->translatedFormat('d F Y'));
        $sheet->getStyle('A3')->getFont()->setSize(10)->setItalic(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF64748B'));
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // 2. Table Headers (Row 5)
        $headerRange = 'A5:N5';
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1E293B'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->getRowDimension(5)->setRowHeight(26);

        // 3. Data & Summary Styling
        $dataRows = $this->collection();
        $count = $dataRows->count();
        $startDataRow = 6;
        $lastDataRow = 5 + $count;

        if ($count > 0) {
            $dataRange = "A{$startDataRow}:N{$lastDataRow}";
            $sheet->getStyle($dataRange)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle("A{$startDataRow}:A{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("B{$startDataRow}:B{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("E{$startDataRow}:E{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("G{$startDataRow}:G{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("H{$startDataRow}:H{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Number formatting
            $sheet->getStyle("G{$startDataRow}:G{$lastDataRow}")->getNumberFormat()->setFormatCode('#,##0.##');
            $sheet->getStyle("I{$startDataRow}:M{$lastDataRow}")->getNumberFormat()->setFormatCode('#,##0');

            // Borders on data
            $sheet->getStyle($dataRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFCBD5E1'));

            // Summary Row
            $summaryRow = $lastDataRow + 1;
            $sheet->mergeCells("A{$summaryRow}:I{$summaryRow}");
            $sheet->setCellValue("A{$summaryRow}", 'TOTAL REKAPITULASI:');
            $sheet->setCellValue("J{$summaryRow}", "=SUM(J{$startDataRow}:J{$lastDataRow})");
            $sheet->setCellValue("K{$summaryRow}", "=SUM(K{$startDataRow}:K{$lastDataRow})");
            $sheet->setCellValue("L{$summaryRow}", "=SUM(L{$startDataRow}:L{$lastDataRow})");
            $sheet->setCellValue("M{$summaryRow}", "=SUM(M{$startDataRow}:M{$lastDataRow})");

            $summaryRange = "A{$summaryRow}:N{$summaryRow}";
            $sheet->getStyle($summaryRange)->applyFromArray([
                'font' => ['bold' => true],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F1F5F9'],
                ],
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']],
                ],
            ]);
            $sheet->getStyle("A{$summaryRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("J{$summaryRow}:M{$summaryRow}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("M{$summaryRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FEF3C7');

            // 4. Signature Block (3 Tanda Tangan: Pembuat, Meng-crosscheck, Mengetahui)
            $signDateRow  = $summaryRow + 2;
            $signTitleRow = $summaryRow + 4;
            $signNameRow  = $signTitleRow + 4;
            $signRoleRow  = $signNameRow + 1;

            // Date Stamp di sisi kanan atas tanda tangan
            $sheet->mergeCells("K{$signDateRow}:N{$signDateRow}");
            $tglCetak = "Yogyakarta, " . $this->endDate->translatedFormat('d F Y');
            $sheet->setCellValue("K{$signDateRow}", $tglCetak);
            $sheet->getStyle("K{$signDateRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("K{$signDateRow}")->getFont()->setSize(10)->setItalic(true);

            // 1. Pembuat
            $sheet->mergeCells("B{$signTitleRow}:D{$signTitleRow}");
            $sheet->setCellValue("B{$signTitleRow}", "Pembuat,");
            $sheet->getStyle("B{$signTitleRow}")->getFont()->setBold(true)->setSize(10);
            $sheet->getStyle("B{$signTitleRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $sheet->mergeCells("B{$signNameRow}:D{$signNameRow}");
            $sheet->setCellValue("B{$signNameRow}", "(  ________________________  )");
            $sheet->getStyle("B{$signNameRow}")->getFont()->setBold(true)->setSize(10);
            $sheet->getStyle("B{$signNameRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $sheet->mergeCells("B{$signRoleRow}:D{$signRoleRow}");
            $sheet->setCellValue("B{$signRoleRow}", "Staf Lapangan / Logistik");
            $sheet->getStyle("B{$signRoleRow}")->getFont()->setSize(9)->setItalic(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF64748B'));
            $sheet->getStyle("B{$signRoleRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // 2. Meng-crosscheck
            $sheet->mergeCells("F{$signTitleRow}:I{$signTitleRow}");
            $sheet->setCellValue("F{$signTitleRow}", "Meng-crosscheck,");
            $sheet->getStyle("F{$signTitleRow}")->getFont()->setBold(true)->setSize(10);
            $sheet->getStyle("F{$signTitleRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $sheet->mergeCells("F{$signNameRow}:I{$signNameRow}");
            $sheet->setCellValue("F{$signNameRow}", "(  ________________________  )");
            $sheet->getStyle("F{$signNameRow}")->getFont()->setBold(true)->setSize(10);
            $sheet->getStyle("F{$signNameRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $sheet->mergeCells("F{$signRoleRow}:I{$signRoleRow}");
            $sheet->setCellValue("F{$signRoleRow}", "Site Manager / Pengawas");
            $sheet->getStyle("F{$signRoleRow}")->getFont()->setSize(9)->setItalic(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF64748B'));
            $sheet->getStyle("F{$signRoleRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // 3. Mengetahui
            $sheet->mergeCells("K{$signTitleRow}:N{$signTitleRow}");
            $sheet->setCellValue("K{$signTitleRow}", "Mengetahui,");
            $sheet->getStyle("K{$signTitleRow}")->getFont()->setBold(true)->setSize(10);
            $sheet->getStyle("K{$signTitleRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $sheet->mergeCells("K{$signNameRow}:N{$signNameRow}");
            $sheet->setCellValue("K{$signNameRow}", "(  ________________________  )");
            $sheet->getStyle("K{$signNameRow}")->getFont()->setBold(true)->setSize(10);
            $sheet->getStyle("K{$signNameRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $sheet->mergeCells("K{$signRoleRow}:N{$signRoleRow}");
            $sheet->setCellValue("K{$signRoleRow}", "Project Manager / Direksi");
            $sheet->getStyle("K{$signRoleRow}")->getFont()->setSize(9)->setItalic(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF64748B'));
            $sheet->getStyle("K{$signRoleRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        return [];
    }
}
