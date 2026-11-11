<?php

namespace App\Exports;

use App\Models\Category;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Support\Collection;
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

class MaterialGroupStockSheet implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithCustomStartCell, WithTitle
{
    protected int $month;
    protected int $year;
    protected ?Warehouse $warehouse;
    protected ?Collection $cachedRows = null;

    public function __construct(int $month, int $year, ?Warehouse $warehouse = null)
    {
        $this->month     = $month;
        $this->year      = $year;
        $this->warehouse = $warehouse;
    }

    public function title(): string
    {
        return 'Stok Material (Bahan)';
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

        $query = Category::where('type', 'material')
            ->with(['materials' => function ($mq) {
                $mq->with(['unit', 'supplier', 'inventories']);
                if ($this->warehouse) {
                    $mq->whereHas('inventories', fn($iq) => $iq->where('warehouse_id', $this->warehouse->id));
                }
            }])
            ->orderBy('name');

        $categories = $query->get();
        $rows = collect();
        $rowIndex = 0;

        foreach ($categories as $cat) {
            // Group materials by type (kelompok barang)
            $typeGroups = $cat->materials->groupBy(function ($m) {
                return !empty(trim($m->type ?? '')) ? trim($m->type) : 'Umum / Lainnya';
            });

            foreach ($typeGroups as $typeName => $materials) {
                foreach ($materials as $mat) {
                    $rowIndex++;

                    $inventories = $mat->inventories;
                    if ($this->warehouse) {
                        $inventories = $inventories->where('warehouse_id', $this->warehouse->id);
                    }

                    $totalQty = (float) $inventories->sum('quantity');
                    $allocatedQty = (float) $inventories->sum('qty_allocated');
                    $sisaQty = max(0, $totalQty - $allocatedQty);
                    $minStock = $this->warehouse && $this->warehouse->is_central
                        ? (float) ($mat->min_stock_central ?? 0)
                        : (float) ($mat->min_stock ?? 0);

                    $rows->push([
                        'no'            => $rowIndex,
                        'category'      => $cat->name,
                        'group'         => $typeName,
                        'sku'           => $mat->sku ?? '-',
                        'name'          => $mat->name,
                        'unit'          => $mat->unit?->name ?? ($mat->unit?->code ?? '-'),
                        'min_stock'     => $minStock,
                        'total_stock'   => $totalQty,
                        'sisa_stock'    => $sisaQty,
                        'supplier'      => $mat->supplier?->name ?? ($mat->supplier_name ?? '-'),
                    ]);
                }
            }
        }

        $this->cachedRows = $rows;
        return $rows;
    }

    public function headings(): array
    {
        return [
            'NO',
            'KATEGORI MATERIAL',
            'KELOMPOK BARANG',
            'KODE SKU',
            'NAMA MATERIAL',
            'SATUAN',
            'MIN. STOK',
            'TOTAL STOK',
            'STOK SISA / TERSEDIA',
            'SUPPLIER UTAMA',
        ];
    }

    public function map($row): array
    {
        return [
            $row['no'],
            $row['category'],
            $row['group'],
            $row['sku'],
            $row['name'],
            $row['unit'],
            $row['min_stock'],
            $row['total_stock'],
            $row['sisa_stock'],
            $row['supplier'],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $bulanNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $namaBulan = $bulanNames[$this->month] ?? $this->month;
        $gudangName = $this->warehouse ? $this->warehouse->name : 'Semua Gudang Terdaftar';

        // 1. Header Judul Laporan
        $sheet->mergeCells('A1:J1');
        $sheet->setCellValue('A1', 'PT. ARSIKON CIPTA KARYA');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF1E3A8A'));

        $sheet->mergeCells('A2:J2');
        $sheet->setCellValue('A2', "LAPORAN REKAPITULASI STOK MATERIAL BULANAN (PENGELOMPOKAN BARANG)");
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(11);

        $sheet->mergeCells('A3:J3');
        $sheet->setCellValue('A3', "Periode: Bulan {$namaBulan} {$this->year}  |  Gudang: {$gudangName}");
        $sheet->getStyle('A3')->getFont()->setItalic(true)->setSize(9.5)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF475569'));

        // 2. Table Headings (Row 5)
        $sheet->getStyle('A5:J5')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E40AF']], // Indigo-800
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
        ]);
        $sheet->getRowDimension(5)->setRowHeight(24);

        $totalDataRows = $this->cachedRows ? $this->cachedRows->count() : 0;
        $startDataRow = 6;
        $lastDataRow = $startDataRow + max(1, $totalDataRows) - 1;

        if ($totalDataRows > 0) {
            $dataRange = "A{$startDataRow}:J{$lastDataRow}";
            $sheet->getStyle($dataRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFCBD5E1'));
            $sheet->getStyle("A{$startDataRow}:A{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("D{$startDataRow}:D{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("F{$startDataRow}:F{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("G{$startDataRow}:I{$lastDataRow}")->getNumberFormat()->setFormatCode('#,##0.00');

            // Summary Row
            $summaryRow = $lastDataRow + 1;
            $sheet->mergeCells("A{$summaryRow}:G{$summaryRow}");
            $sheet->setCellValue("A{$summaryRow}", 'TOTAL STOK KESELURUHAN:');
            $sheet->setCellValue("H{$summaryRow}", "=SUM(H{$startDataRow}:H{$lastDataRow})");
            $sheet->setCellValue("I{$summaryRow}", "=SUM(I{$startDataRow}:I{$lastDataRow})");

            $sheet->getStyle("A{$summaryRow}:J{$summaryRow}")->applyFromArray([
                'font' => ['bold' => true],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FEF3C7']], // Amber-100
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
            ]);
            $sheet->getStyle("A{$summaryRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("H{$summaryRow}:I{$summaryRow}")->getNumberFormat()->setFormatCode('#,##0.00');
        }

        return [];
    }
}
