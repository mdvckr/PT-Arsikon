<?php

namespace App\Exports;

use App\Models\ProjectBulkMaterial;
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

class BulkMaterialExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithCustomStartCell, WithTitle
{
    use Exportable;

    protected ProjectBulkMaterial $bulkMaterial;
    protected ?Collection $cachedRows = null;

    public function __construct(ProjectBulkMaterial $bulkMaterial)
    {
        $this->bulkMaterial = $bulkMaterial;
    }

    public function title(): string
    {
        return 'Catatan Material Curah';
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

        $items = $this->bulkMaterial->items;
        $rows = collect();
        $no = 1;

        foreach ($items as $item) {
            $rows->push([
                'no'            => $no++,
                'tanggal'       => $item->delivery_date ? $item->delivery_date->format('d/m/Y') : '-',
                'no_nota'       => $item->ticket_number ?: '-',
                'qty'           => (float) $item->quantity,
                'satuan'        => $item->unit ?: 'Rit',
                'jenis_material'=> $item->material_type,
                'harga_satuan'  => (float) $item->unit_price,
                'subtotal'      => (float) $item->subtotal,
                'keterangan'    => $item->notes ?: '-',
            ]);
        }

        $this->cachedRows = $rows;
        return $rows;
    }

    public function headings(): array
    {
        return [
            'NO',
            'TANGGAL',
            'NO. NOTA / SURAT',
            'QTY',
            'SATUAN',
            'JENIS MATERIAL',
            'HARGA SATUAN (Rp)',
            'TOTAL HARGA (Rp)',
            'KETERANGAN',
        ];
    }

    public function map($row): array
    {
        return [
            $row['no'],
            $row['tanggal'],
            $row['no_nota'],
            $row['qty'],
            $row['satuan'],
            $row['jenis_material'],
            $row['harga_satuan'] > 0 ? $row['harga_satuan'] : '-',
            $row['subtotal'] > 0 ? $row['subtotal'] : '-',
            $row['keterangan'],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // 1. Header Metadata
        $sheet->mergeCells('A1:I1');
        $sheet->setCellValue('A1', 'PT ARSIKON CIPTA KARYA');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF1E3A8A'));
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        $sheet->mergeCells('A2:I2');
        $sheet->setCellValue('A2', strtoupper($this->bulkMaterial->title));
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);

        $dateRange = '';
        if ($this->bulkMaterial->start_date) {
            $dateRange = $this->bulkMaterial->start_date->translatedFormat('d M Y');
            if ($this->bulkMaterial->end_date && $this->bulkMaterial->end_date != $this->bulkMaterial->start_date) {
                $dateRange .= ' s/d ' . $this->bulkMaterial->end_date->translatedFormat('d M Y');
            }
        }

        $sheet->setCellValue('A3', 'PROYEK: ' . ($this->bulkMaterial->project?->name ?: $this->bulkMaterial->project_name ?: '-'));
        $sheet->setCellValue('D3', 'SUPLIER: ' . $this->bulkMaterial->supplier_name);
        $sheet->setCellValue('G3', 'PERIODE: ' . ($dateRange ?: '-'));
        $sheet->getStyle('A3:I3')->getFont()->setBold(true)->setSize(10);

        // 2. Table Headers (Row 5)
        $sheet->getStyle('A5:I5')->applyFromArray([
            'font' => [
                'bold'  => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size'  => 10,
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1E3A8A'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet->getRowDimension(5)->setRowHeight(26);

        // 3. Data styling
        $dataCount = $this->collection()->count();
        $startRow = 6;
        $endRow = $startRow + $dataCount - 1;

        if ($dataCount > 0) {
            $sheet->getStyle("A{$startRow}:I{$endRow}")->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color'       => ['rgb' => 'CBD5E1'],
                    ],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);

            // Alignment
            $sheet->getStyle("A{$startRow}:A{$endRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("B{$startRow}:B{$endRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C{$startRow}:C{$endRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("D{$startRow}:D{$endRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("E{$startRow}:E{$endRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("G{$startRow}:H{$endRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            // Summary row at bottom
            $summaryRow = $endRow + 1;
            $sheet->mergeCells("A{$summaryRow}:C{$summaryRow}");
            $sheet->setCellValue("A{$summaryRow}", "TOTAL KESELURUHAN");
            $sheet->setCellValue("D{$summaryRow}", "=SUM(D{$startRow}:D{$endRow})");
            $sheet->setCellValue("E{$summaryRow}", "Rit");
            $sheet->setCellValue("H{$summaryRow}", "=SUM(H{$startRow}:H{$endRow})");

            $sheet->getStyle("A{$summaryRow}:I{$summaryRow}")->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 10,
                ],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F1F5F9'],
                ],
                'borders' => [
                    'top' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '1E3A8A']],
                    'bottom' => ['borderStyle' => Border::BORDER_DOUBLE, 'color' => ['rgb' => '1E3A8A']],
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']],
                ],
            ]);
            $sheet->getStyle("A{$summaryRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("D{$summaryRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("E{$summaryRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("H{$summaryRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            // Ringkasan Per Jenis Material di bawah tabel
            $rekapStart = $summaryRow + 3;
            $sheet->setCellValue("B{$rekapStart}", "RINGKASAN PER JENIS MATERIAL:");
            $sheet->getStyle("B{$rekapStart}")->getFont()->setBold(true);

            $summaryByType = $this->bulkMaterial->summary_by_type;
            $rIndex = $rekapStart + 1;
            foreach ($summaryByType as $type => $info) {
                $sheet->setCellValue("B{$rIndex}", "• " . $type);
                $sheet->setCellValue("C{$rIndex}", $info['total_qty']);
                $sheet->setCellValue("D{$rIndex}", $info['unit']);
                $sheet->getStyle("B{$rIndex}:D{$rIndex}")->getFont()->setSize(9);
                $sheet->getStyle("C{$rIndex}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $rIndex++;
            }
        }

        return [];
    }
}
