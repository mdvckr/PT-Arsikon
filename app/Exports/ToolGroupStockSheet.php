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

class ToolGroupStockSheet implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithCustomStartCell, WithTitle
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
        return 'Stok Alat Kerja';
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

        $query = Category::where('type', 'tool')
            ->with(['tools' => function ($tq) {
                $tq->with(['inventories']);
                if ($this->warehouse) {
                    $tq->whereHas('inventories', fn($iq) => $iq->where('warehouse_id', $this->warehouse->id));
                }
            }])
            ->orderBy('name');

        $categories = $query->get();
        $rows = collect();
        $rowIndex = 0;

        foreach ($categories as $cat) {
            // Group tools by type (kelompok alat)
            $typeGroups = $cat->tools->groupBy(function ($t) {
                return !empty(trim($t->type ?? '')) ? trim($t->type) : 'Umum / Peralatan Kerja';
            });

            foreach ($typeGroups as $typeName => $tools) {
                foreach ($tools as $tool) {
                    $rowIndex++;

                    $inventories = $tool->inventories;
                    if ($this->warehouse) {
                        $inventories = $inventories->where('warehouse_id', $this->warehouse->id);
                    }

                    $stockTotal       = (int) $inventories->sum('stock_total');
                    $stockAvailable   = (int) $inventories->sum('stock_available');
                    $stockBorrowed    = (int) $inventories->sum('stock_borrowed');
                    $stockMaintenance = (int) $inventories->sum('stock_maintenance');
                    $stockDamaged     = (int) $inventories->sum('stock_damaged');

                    $rows->push([
                        'no'                => $rowIndex,
                        'category'          => $cat->name,
                        'group'             => $typeName,
                        'code'              => $tool->code ?? '-',
                        'name'              => $tool->name,
                        'stock_total'       => $stockTotal,
                        'stock_available'   => $stockAvailable,
                        'stock_borrowed'    => $stockBorrowed,
                        'stock_maintenance' => $stockMaintenance,
                        'stock_damaged'     => $stockDamaged,
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
            'KATEGORI ALAT',
            'KELOMPOK ALAT',
            'KODE ALAT',
            'NAMA ALAT KERJA',
            'TOTAL UNIT',
            'KONDISI BAIK (TERSEDIA)',
            'SEDANG DIPINJAM',
            'DALAM PERAWATAN',
            'KONDISI RUSAK',
        ];
    }

    public function map($row): array
    {
        return [
            $row['no'],
            $row['category'],
            $row['group'],
            $row['code'],
            $row['name'],
            $row['stock_total'],
            $row['stock_available'],
            $row['stock_borrowed'],
            $row['stock_maintenance'],
            $row['stock_damaged'],
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
        $sheet->setCellValue('A2', "LAPORAN REKAPITULASI STOK ALAT KERJA BULANAN (PENGELOMPOKAN ALAT)");
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(11);

        $sheet->mergeCells('A3:J3');
        $sheet->setCellValue('A3', "Periode: Bulan {$namaBulan} {$this->year}  |  Gudang: {$gudangName}");
        $sheet->getStyle('A3')->getFont()->setItalic(true)->setSize(9.5)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF475569'));

        // 2. Table Headings (Row 5)
        $sheet->getStyle('A5:J5')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '047857']], // Emerald-700
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
            $sheet->getStyle("F{$startDataRow}:J{$lastDataRow}")->getNumberFormat()->setFormatCode('#,##0');

            // Summary Row
            $summaryRow = $lastDataRow + 1;
            $sheet->mergeCells("A{$summaryRow}:E{$summaryRow}");
            $sheet->setCellValue("A{$summaryRow}", 'TOTAL UNIT KESELURUHAN:');
            $sheet->setCellValue("F{$summaryRow}", "=SUM(F{$startDataRow}:F{$lastDataRow})");
            $sheet->setCellValue("G{$summaryRow}", "=SUM(G{$startDataRow}:G{$lastDataRow})");
            $sheet->setCellValue("H{$summaryRow}", "=SUM(H{$startDataRow}:H{$lastDataRow})");
            $sheet->setCellValue("I{$summaryRow}", "=SUM(I{$startDataRow}:I{$lastDataRow})");
            $sheet->setCellValue("J{$summaryRow}", "=SUM(J{$startDataRow}:J{$lastDataRow})");

            $sheet->getStyle("A{$summaryRow}:J{$summaryRow}")->applyFromArray([
                'font' => ['bold' => true],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DCFCE7']], // Emerald-100
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
            ]);
            $sheet->getStyle("A{$summaryRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("F{$summaryRow}:J{$summaryRow}")->getNumberFormat()->setFormatCode('#,##0');
        }

        return [];
    }
}
