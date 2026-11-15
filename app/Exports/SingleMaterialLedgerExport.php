<?php

namespace App\Exports;

use App\Models\MaterialLedger;
use App\Services\MaterialLedgerService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class SingleMaterialLedgerExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithCustomStartCell, WithTitle
{
    use Exportable;

    protected MaterialLedger $ledger;
    protected ?Collection $cachedRows = null;
    protected array $batchColors = [];

    public function __construct(MaterialLedger $ledger)
    {
        $this->ledger = $ledger;
    }

    public function title(): string
    {
        return 'Catatan Material';
    }

    public function startCell(): string
    {
        return 'A5';
    }

    public function collection(): Collection
    {
        if ($this->cachedRows !== null) {
            return $this->cachedRows;
        }

        $service = new MaterialLedgerService();
        $calculatedEntries = $service->getCalculatedEntries($this->ledger);

        $rows = collect();
        $this->batchColors = [];
        $currentRowIndex = 6;

        foreach ($calculatedEntries as $entry) {
            $dateStr = $entry->delivery_date ? $entry->delivery_date->format('d/m/Y') : '-';
            $supplier = $entry->supplier_name ?: $this->ledger->supplier_name;
            $accountBehalf = $entry->account_behalf ?: ($this->ledger->account_behalf ?: '-');

            $rows->push([
                'tanggal'           => $dateStr,
                'suplier'           => $supplier,
                'an'                => $accountBehalf,
                'no_surat'          => $entry->delivery_note_number,
                'nama_material'     => $entry->material_specification,
                'vol_deposit'       => $entry->deposit_volume ? (float)$entry->deposit_volume : '',
                'vol_pengiriman'    => (float) $entry->delivery_volume,
                'satuan'            => $entry->unit ?: ($this->ledger->default_unit ?: 'm3'),
                'harga'             => (float) $entry->unit_price > 0 ? (float) $entry->unit_price : '',
                'jumlah_dipakai'    => (float) $entry->calculated_amount_used > 0 ? (float) $entry->calculated_amount_used : '',
                'sisa_deposit'      => (float) $entry->running_total_amount > 0 ? (float) $entry->running_total_amount : '',
                'total_volume'      => (float) $entry->running_total_volume,
                'keterangan'        => $entry->notes ?: '',
                'sisa_vol_deposit'  => '-',
            ]);

            $this->batchColors[$currentRowIndex] = strtolower($entry->batch_color ?: 'green');
            $currentRowIndex++;
        }

        $this->cachedRows = $rows;
        return $this->cachedRows;
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Suplier',
            'AN',
            'No. Surat',
            'Nama Material / Truk',
            'Vol. Deposit',
            'Vol Pengiriman',
            'Sat',
            'Harga',
            'Jumlah Dipakai',
            'Sisa Deposit (Kumulatif)',
            'Total Volume',
            'Keterangan',
            'Sisa Vol Deposit',
        ];
    }

    public function map($row): array
    {
        return [
            $row['tanggal'],
            $row['suplier'],
            $row['an'],
            $row['no_surat'],
            $row['nama_material'],
            $row['vol_deposit'],
            $row['vol_pengiriman'],
            $row['satuan'],
            $row['harga'],
            $row['jumlah_dipakai'],
            $row['sisa_deposit'],
            $row['total_volume'],
            $row['keterangan'],
            $row['sisa_vol_deposit'],
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
        $sheet->setCellValue('A2', strtoupper($this->ledger->title));
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(11)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF1E293B'));
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A3:N3');
        $projName = $this->ledger->project?->name ?? ($this->ledger->project_name ?? 'Proyek');
        $periodeStr = $this->ledger->start_date ? $this->ledger->start_date->translatedFormat('d F Y') : '-';
        if ($this->ledger->end_date) {
            $periodeStr .= ' s/d ' . $this->ledger->end_date->translatedFormat('d F Y');
        }
        $sheet->setCellValue('A3', "Proyek: {$projName} | Suplier: {$this->ledger->supplier_name} (AN: " . ($this->ledger->account_behalf ?: '-') . ") | Periode: {$periodeStr}");
        $sheet->getStyle('A3')->getFont()->setSize(10)->setItalic(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF64748B'));
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // 2. Table Headers (Row 5) - Biru Muda Header Persis Excel (#9BC2E6)
        $headerRange = 'A5:N5';
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => '0F172A'], 'size' => 10],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '9BC2E6'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '475569']],
            ],
        ]);
        $sheet->getRowDimension(5)->setRowHeight(25);

        // 3. Data Styling & Batch Colors
        $dataRows = $this->collection();
        $count = $dataRows->count();
        $startDataRow = 6;
        $lastDataRow = 5 + $count;

        if ($count > 0) {
            $dataRange = "A{$startDataRow}:N{$lastDataRow}";
            $sheet->getStyle($dataRange)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle("A{$startDataRow}:A{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C{$startDataRow}:D{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("F{$startDataRow}:G{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("H{$startDataRow}:H{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("N{$startDataRow}:N{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Number formatting
            $sheet->getStyle("F{$startDataRow}:G{$lastDataRow}")->getNumberFormat()->setFormatCode('#,##0.##');
            $sheet->getStyle("I{$startDataRow}:K{$lastDataRow}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("L{$startDataRow}:L{$lastDataRow}")->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle("N{$startDataRow}:N{$lastDataRow}")->getNumberFormat()->setFormatCode('#,##0.00');

            // Apply batch background colors per row
            foreach ($this->batchColors as $rowNum => $colorKey) {
                $hex = match ($colorKey) {
                    'green'            => 'C6E0B4',
                    'yellow'           => 'FFE699',
                    'peach', 'orange'  => 'F8CBAD',
                    'blue'             => 'B4C6E7',
                    default            => 'FFFFFF',
                };

                if ($hex !== 'FFFFFF') {
                    $sheet->getStyle("A{$rowNum}:N{$rowNum}")->getFill()->applyFromArray([
                        'fillType'   => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => $hex],
                    ]);
                }
            }

            // Borders on data
            $sheet->getStyle($dataRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF94A3B8'));

            // 4. Grand Total Row (Row Kuning Terang #FFFF00 Persis Excel)
            $summaryRow = $lastDataRow + 1;
            $sheet->mergeCells("A{$summaryRow}:E{$summaryRow}");
            $sheet->setCellValue("A{$summaryRow}", 'TOTAL REKAPITULASI PENGIRIMAN:');
            $sheet->setCellValue("F{$summaryRow}", "=SUM(F{$startDataRow}:F{$lastDataRow})");
            $sheet->setCellValue("G{$summaryRow}", "=SUM(G{$startDataRow}:G{$lastDataRow})");
            $sheet->setCellValue("J{$summaryRow}", "=SUM(J{$startDataRow}:J{$lastDataRow})");
            $sheet->setCellValue("K{$summaryRow}", "=MAX(K{$startDataRow}:K{$lastDataRow})");
            $sheet->setCellValue("L{$summaryRow}", "=MAX(L{$startDataRow}:L{$lastDataRow})");
            $sheet->setCellValue("M{$summaryRow}", 'TOTAL SISA VOL');
            $sheet->setCellValue("N{$summaryRow}", '-');

            $summaryRange = "A{$summaryRow}:N{$summaryRow}";
            $sheet->getStyle($summaryRange)->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => '000000']],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FFFF00'],
                ],
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '000000']],
                ],
            ]);
            $sheet->getStyle("A{$summaryRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("F{$summaryRow}:G{$summaryRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("J{$summaryRow}:K{$summaryRow}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("L{$summaryRow}:N{$summaryRow}")->getNumberFormat()->setFormatCode('#,##0.00');

            // 5. Signature Block
            $signDateRow  = $summaryRow + 2;
            $signTitleRow = $summaryRow + 4;
            $signNameRow  = $signTitleRow + 4;
            $signRoleRow  = $signNameRow + 1;

            $sheet->mergeCells("K{$signDateRow}:N{$signDateRow}");
            $tglCetak = "Yogyakarta, " . now()->translatedFormat('d F Y');
            $sheet->setCellValue("K{$signDateRow}", $tglCetak);
            $sheet->getStyle("K{$signDateRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("K{$signDateRow}")->getFont()->setSize(10)->setItalic(true);

            // Pembuat
            $sheet->mergeCells("B{$signTitleRow}:D{$signTitleRow}");
            $sheet->setCellValue("B{$signTitleRow}", "Pembuat,");
            $sheet->getStyle("B{$signTitleRow}")->getFont()->setBold(true)->setSize(10);
            $sheet->getStyle("B{$signTitleRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $sheet->mergeCells("B{$signNameRow}:D{$signNameRow}");
            $sheet->setCellValue("B{$signNameRow}", "(  ________________________  )");
            $sheet->getStyle("B{$signNameRow}")->getFont()->setBold(true)->setSize(10);
            $sheet->getStyle("B{$signNameRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $sheet->mergeCells("B{$signRoleRow}:D{$signRoleRow}");
            $sheet->setCellValue("B{$signRoleRow}", "Staf Logistik / Lapangan");
            $sheet->getStyle("B{$signRoleRow}")->getFont()->setSize(9)->setItalic(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF64748B'));
            $sheet->getStyle("B{$signRoleRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Crosscheck
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

            // Mengetahui
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

