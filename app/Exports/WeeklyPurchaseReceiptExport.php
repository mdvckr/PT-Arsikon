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
    protected int $rowNumber = 0;

    public function __construct(Collection $receipts, ?Project $project = null, ?Carbon $startDate = null, ?Carbon $endDate = null)
    {
        $this->receipts  = $receipts;
        $this->project   = $project;
        $this->startDate = $startDate ?? now()->startOfWeek(Carbon::MONDAY);
        $this->endDate   = $endDate ?? now()->endOfWeek(Carbon::SUNDAY);
    }

    public function collection(): Collection
    {
        return $this->receipts;
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
            'Proyek',
            'Toko / Supplier',
            'Rincian Item Belanja',
            'Subtotal Kotor (Rp)',
            'Diskon (Rp)',
            'Ongkir (Rp)',
            'Total Bersih (Rp)',
            'Status',
        ];
    }

    public function map($receipt): array
    {
        $this->rowNumber++;

        $itemLines = [];
        $itemDiscountSum = 0;
        $rawSubtotalSum = 0;

        foreach ($receipt->items as $it) {
            $qtyVal = (float)$it->quantity;
            $qtyStr = ($qtyVal == floor($qtyVal)) ? number_format($qtyVal, 0) : number_format($qtyVal, 2, ',', '.');
            $priceStr = number_format($it->unit_price, 0, ',', '.');
            $itemDiscount = (float) ($it->item_discount ?? 0);
            $itemDiscountSum += $itemDiscount;
            $rawSubtotalSum += ($qtyVal * (float)$it->unit_price);

            $line = $it->item_name . ' (' . $qtyStr . ' ' . ($it->unit_name ?? 'Bh') . ' @ Rp ' . $priceStr;
            if ($itemDiscount > 0) {
                $line .= ' - disc Rp ' . number_format($itemDiscount, 0, ',', '.');
            }
            $line .= ')';
            $itemLines[] = $line;
        }

        $rincianText = !empty($itemLines) ? implode("; \n", $itemLines) : ($receipt->notes ?? '-');
        $kotor = (float) ($receipt->items_subtotal > 0 ? $receipt->items_subtotal : ($rawSubtotalSum > 0 ? $rawSubtotalSum : $receipt->total_amount));
        $diskon = (float) (($receipt->global_discount ?? 0) + $itemDiscountSum);
        $ongkir = (float) ($receipt->shipping_fee ?? 0);
        $bersih = (float) $receipt->total_amount;

        return [
            $this->rowNumber,
            $receipt->receipt_date ? $receipt->receipt_date->format('d/m/Y') : '-',
            $receipt->receipt_number ?? ('NOTA-' . $receipt->id),
            $receipt->project?->name ?? ($receipt->project_name ?? '-'),
            $receipt->supplier?->name ?? ($receipt->supplier_name ?? 'Toko Langsung'),
            $rincianText,
            $kotor,
            $diskon,
            $ongkir,
            $bersih,
            strtoupper($receipt->status_label ?? 'LUNAS'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // 1. Title Block (Row 1-3)
        $sheet->mergeCells('A1:K1');
        $sheet->setCellValue('A1', 'PT. ARSIKON KONSTRUKSI INDONESIA');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF0F172A'));
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A2:K2');
        $sheet->setCellValue('A2', 'REKAPITULASI LAPORAN NOTA BELANJA LAPANGAN (KAS OPERASIONAL)');
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(11)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF1E293B'));
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A3:K3');
        $projectName = $this->project ? $this->project->name : 'Semua Proyek';
        $sheet->setCellValue('A3', "Proyek: {$projectName} | Periode: " . $this->startDate->translatedFormat('d F Y') . ' s/d ' . $this->endDate->translatedFormat('d F Y'));
        $sheet->getStyle('A3')->getFont()->setSize(10)->setItalic(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF64748B'));
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // 2. Table Headers (Row 5)
        $headerRange = 'A5:K5';
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
        $count = $this->receipts->count();
        $startDataRow = 6;
        $lastDataRow = 5 + $count;

        if ($count > 0) {
            $dataRange = "A{$startDataRow}:K{$lastDataRow}";
            $sheet->getStyle($dataRange)->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
            $sheet->getStyle("A{$startDataRow}:A{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("B{$startDataRow}:B{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("K{$startDataRow}:K{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("F{$startDataRow}:F{$lastDataRow}")->getAlignment()->setWrapText(true);

            // Number formatting for currency columns
            $sheet->getStyle("G{$startDataRow}:I{$lastDataRow}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("J{$startDataRow}:J{$lastDataRow}")->getNumberFormat()->setFormatCode('#,##0');

            // Borders on data
            $sheet->getStyle($dataRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFCBD5E1'));

            // Summary Row
            $summaryRow = $lastDataRow + 1;
            $sheet->mergeCells("A{$summaryRow}:F{$summaryRow}");
            $sheet->setCellValue("A{$summaryRow}", 'TOTAL REKAPITULASI:');
            $sheet->setCellValue("G{$summaryRow}", "=SUM(G{$startDataRow}:G{$lastDataRow})");
            $sheet->setCellValue("H{$summaryRow}", "=SUM(H{$startDataRow}:H{$lastDataRow})");
            $sheet->setCellValue("I{$summaryRow}", "=SUM(I{$startDataRow}:I{$lastDataRow})");
            $sheet->setCellValue("J{$summaryRow}", "=SUM(J{$startDataRow}:J{$lastDataRow})");

            $summaryRange = "A{$summaryRow}:K{$summaryRow}";
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
            $sheet->getStyle("G{$summaryRow}:I{$summaryRow}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("J{$summaryRow}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("J{$summaryRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FEF3C7');
        }

        return [];
    }
}