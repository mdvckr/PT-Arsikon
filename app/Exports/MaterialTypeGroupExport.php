<?php

namespace App\Exports;

use App\Models\Category;
use App\Models\Material;
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

class MaterialTypeGroupExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithCustomStartCell, WithTitle
{
    protected ?Category $category;
    protected ?string $typeName;
    protected ?Warehouse $warehouse;
    protected array $filterWarehouseIds;
    protected ?Collection $cachedRows = null;

    public function __construct(?Category $category, ?string $typeName, ?Warehouse $warehouse, array $filterWarehouseIds = [])
    {
        $this->category = $category;
        $this->typeName = $typeName;
        $this->warehouse = $warehouse;
        $this->filterWarehouseIds = $filterWarehouseIds;
    }

    public function title(): string
    {
        $safeName = $this->typeName ? substr(preg_replace('/[^A-Za-z0-9_\-]/', '_', $this->typeName), 0, 25) : 'Kelompok';
        return 'Rekap ' . $safeName;
    }

    public function startCell(): string
    {
        return 'A6';
    }

    public function collection(): Collection
    {
        if ($this->cachedRows !== null) {
            return $this->cachedRows;
        }

        $query = Material::query()->with([
            'unit',
            'supplier',
            'category',
            'inventories' => function ($iq) {
                if (!empty($this->filterWarehouseIds)) {
                    $iq->whereIn('warehouse_id', $this->filterWarehouseIds);
                }
                $iq->with('warehouse');
            },
            'stockMutations' => function ($smq) {
                if (!empty($this->filterWarehouseIds)) {
                    $smq->whereIn('warehouse_id', $this->filterWarehouseIds);
                }
                $smq->latest();
            },
        ]);

        if ($this->category) {
            $query->where('category_id', $this->category->id);
        }

        if (!empty($this->typeName)) {
            if ($this->typeName === 'Umum / Lainnya') {
                $query->where(function ($q) {
                    $q->whereNull('type')->orWhere('type', '')->orWhere('type', 'Umum / Lainnya');
                });
            } else {
                $query->where('type', $this->typeName);
            }
        }

        $materials = $query->orderBy('name')->get();
        $rows = collect();
        $no = 1;

        foreach ($materials as $m) {
            $stockSisa = (float) $m->inventories->sum('quantity');
            $pemakaian = (float) abs($m->stockMutations->where('qty_change', '<', 0)->sum('qty_change'));
            $totalIn   = (float) $m->stockMutations->where('qty_change', '>', 0)->sum('qty_change');
            $totalStock = $totalIn > 0 ? max($totalIn, $stockSisa + $pemakaian) : ($stockSisa + $pemakaian);

            $stages = $m->getDynamicIncomingStages($this->filterWarehouseIds ?: null);
            if ($stages->isEmpty() && !empty($m->incoming_stages)) {
                $stages = collect($m->incoming_stages);
            }

            $stageLines = [];
            foreach ($stages as $stg) {
                $stgRef = $stg['reference'] ?? ($stg['stage'] ?? 'PO/GR');
                $stgDate = !empty($stg['date']) ? Carbon::parse($stg['date'])->format('d/m/Y') : '-';
                $stgQty = (float)($stg['received_qty'] ?? ($stg['qty'] ?? 0));
                $stgLabel = $stg['status_label'] ?? 'Masuk';
                $stageLines[] = "• {$stgRef} (Tgl: {$stgDate}) = " . number_format($stgQty, 2, ',', '.') . " ({$stgLabel})";
            }
            $stageText = !empty($stageLines) ? implode("\n", $stageLines) : '-';

            $rows->push([
                'no'           => $no++,
                'sku'          => $m->sku ?: '-',
                'name'         => $m->name . ($m->size ? " ({$m->size})" : ''),
                'brand'        => $m->brand ?: '-',
                'supplier'     => $m->display_supplier_name ?: '-',
                'unit'         => $m->unit?->code ?? ($m->unit?->name ?? '-'),
                'stages'       => $stageText,
                'total_in'     => $totalStock,
                'pemakaian'    => $pemakaian,
                'sisa_stock'   => $stockSisa,
                'status_stock' => $stockSisa > 0 ? 'TERSEDIA' : 'HABIS',
            ]);
        }

        $this->cachedRows = $rows;
        return $rows;
    }

    public function headings(): array
    {
        return [
            'NO',
            'KODE SKU',
            'NAMA MATERIAL & SPESIFIKASI',
            'MEREK',
            'SUPPLIER',
            'SATUAN',
            'RINCIAN JADWAL & TAHAPAN KEDATANGAN',
            'TOTAL MASUK',
            'PEMAKAIAN',
            'SISA STOK',
            'STATUS STOK',
        ];
    }

    public function map($row): array
    {
        return [
            $row['no'],
            $row['sku'],
            $row['name'],
            $row['brand'],
            $row['supplier'],
            $row['unit'],
            $row['stages'],
            $row['total_in'],
            $row['pemakaian'],
            $row['sisa_stock'],
            $row['status_stock'],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $gudangName = $this->warehouse ? $this->warehouse->name : 'Semua Gudang Terdaftar';
        $kelompokName = $this->typeName ?: 'Umum / Lainnya';
        $kategoriName = $this->category ? $this->category->name : 'Semua Kategori';
        $tglEkspor = now()->translatedFormat('d F Y, H:i') . ' WIB';

        // 1. Kop Header Dokumen Resmi PT Arsikon Cipta Karya
        $sheet->mergeCells('A1:K1');
        $sheet->setCellValue('A1', 'PT. ARSIKON CIPTA KARYA');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF1E3A8A'));

        $sheet->mergeCells('A2:K2');
        $sheet->setCellValue('A2', 'LAPORAN REKAPITULASI MATERIAL PER KELOMPOK BARANG');
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(11);

        $sheet->mergeCells('A3:K3');
        $sheet->setCellValue('A3', "Kelompok: {$kelompokName}  |  Kategori: {$kategoriName}  |  Gudang: {$gudangName}");
        $sheet->getStyle('A3')->getFont()->setBold(true)->setSize(9.5)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF0F172A'));

        $sheet->mergeCells('A4:K4');
        $sheet->setCellValue('A4', "Tanggal Unduh: {$tglEkspor}");
        $sheet->getStyle('A4')->getFont()->setItalic(true)->setSize(9)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF64748B'));

        // 2. Table Headings (Row 6)
        $sheet->getStyle('A6:K6')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E40AF']], // Indigo-800
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
        ]);
        $sheet->getRowDimension(6)->setRowHeight(26);

        $totalDataRows = $this->cachedRows ? $this->cachedRows->count() : 0;
        $startDataRow = 7;
        $lastDataRow = $startDataRow + max(1, $totalDataRows) - 1;

        if ($totalDataRows > 0) {
            $dataRange = "A{$startDataRow}:K{$lastDataRow}";
            $sheet->getStyle($dataRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFCBD5E1'));
            $sheet->getStyle("A{$startDataRow}:A{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("B{$startDataRow}:B{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("F{$startDataRow}:F{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("G{$startDataRow}:G{$lastDataRow}")->getAlignment()->setWrapText(true);
            $sheet->getStyle("H{$startDataRow}:J{$lastDataRow}")->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle("K{$startDataRow}:K{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Summary Row
            $summaryRow = $lastDataRow + 1;
            $sheet->mergeCells("A{$summaryRow}:G{$summaryRow}");
            $sheet->setCellValue("A{$summaryRow}", 'TOTAL REKAPITULASI KELOMPOK:');
            $sheet->setCellValue("H{$summaryRow}", "=SUM(H{$startDataRow}:H{$lastDataRow})");
            $sheet->setCellValue("I{$summaryRow}", "=SUM(I{$startDataRow}:I{$lastDataRow})");
            $sheet->setCellValue("J{$summaryRow}", "=SUM(J{$startDataRow}:J{$lastDataRow})");

            $sheet->getStyle("A{$summaryRow}:K{$summaryRow}")->applyFromArray([
                'font' => ['bold' => true],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FEF3C7']], // Amber-100
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
            ]);
            $sheet->getStyle("A{$summaryRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("H{$summaryRow}:J{$summaryRow}")->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getRowDimension($summaryRow)->setRowHeight(22);
        }

        return [];
    }
}

