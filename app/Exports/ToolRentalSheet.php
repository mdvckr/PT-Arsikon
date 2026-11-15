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
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ToolRentalSheet implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithCustomStartCell, WithTitle
{
    protected Collection $rentals;
    protected ?Project $project;
    protected Carbon $startDate;
    protected Carbon $endDate;

    public function __construct(Collection $rentals, ?Project $project = null, ?Carbon $startDate = null, ?Carbon $endDate = null)
    {
        $this->rentals   = $rentals;
        $this->project   = $project;
        $this->startDate = $startDate ?? now()->startOfWeek(Carbon::MONDAY);
        $this->endDate   = $endDate ?? now()->endOfWeek(Carbon::SUNDAY);
    }

    public function title(): string
    {
        return 'Sewa Alat Proyek';
    }

    public function startCell(): string
    {
        return 'A5';
    }

    public function collection(): Collection
    {
        $rows = collect();
        $no = 1;

        foreach ($this->rentals as $rental) {
            $tglMulai = $rental->start_date ? $rental->start_date->format('d/m/Y') : '-';
            $tglSelesai = $rental->end_date ? $rental->end_date->format('d/m/Y') : '-';

            $rows->push([
                'no'           => $no++,
                'tool_name'    => $rental->tool_name,
                'vendor_name'  => $rental->vendor_name,
                'docket_number'=> $rental->docket_number ?: '-',
                'start_date'   => $tglMulai,
                'end_date'     => $tglSelesai,
                'duration'     => (float) $rental->rental_duration,
                'unit'         => $rental->duration_unit ?: 'Hari',
                'rate'         => (float) $rental->rate_per_unit,
                'deposit'      => (float) $rental->deposit_amount,
                'total_cost'   => (float) $rental->total_rental_cost,
                'status'       => strtoupper($rental->status),
                'notes'        => $rental->notes ?: '-',
            ]);
        }

        return $rows;
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Alat Kerja / Armada',
            'Vendor Penyedia',
            'No. Bukti / Surat Sewa',
            'Tanggal Mulai',
            'Tanggal Selesai',
            'Durasi',
            'Satuan',
            'Tarif / Satuan (Rp)',
            'Deposit (Rp)',
            'Total Biaya Sewa (Rp)',
            'Status',
            'Keterangan',
        ];
    }

    public function map($row): array
    {
        return [
            $row['no'],
            $row['tool_name'],
            $row['vendor_name'],
            $row['docket_number'],
            $row['start_date'],
            $row['end_date'],
            $row['duration'],
            $row['unit'],
            $row['rate'],
            $row['deposit'],
            $row['total_cost'],
            $row['status'],
            $row['notes'],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // 1. Title Block
        $sheet->mergeCells('A1:M1');
        $sheet->setCellValue('A1', 'PT. ARSIKON CIPTA KARYA');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF0F172A'));
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A2:M2');
        $sheet->setCellValue('A2', 'REKAPITULASI SEWA ALAT KERJA PROYEK');
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(11)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF1E293B'));
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A3:M3');
        $projectName = $this->project ? $this->project->name : 'Semua Proyek';
        $sheet->setCellValue('A3', "Proyek: {$projectName} | Periode: " . $this->startDate->translatedFormat('d F Y') . ' s/d ' . $this->endDate->translatedFormat('d F Y'));
        $sheet->getStyle('A3')->getFont()->setSize(10)->setItalic(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF64748B'));
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // 2. Table Headers (Row 5)
        $headerRange = 'A5:M5';
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1E293B'],
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

        // 3. Data Rows
        $count = $this->rentals->count();
        $startDataRow = 6;
        $lastDataRow = 5 + $count;

        if ($count > 0) {
            $dataRange = "A{$startDataRow}:M{$lastDataRow}";
            $sheet->getStyle($dataRange)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle("A{$startDataRow}:A{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("D{$startDataRow}:F{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("G{$startDataRow}:G{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("H{$startDataRow}:H{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("L{$startDataRow}:L{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Number formatting
            $sheet->getStyle("G{$startDataRow}:G{$lastDataRow}")->getNumberFormat()->setFormatCode('#,##0.##');
            $sheet->getStyle("I{$startDataRow}:K{$lastDataRow}")->getNumberFormat()->setFormatCode('#,##0');

            // Borders on data
            $sheet->getStyle($dataRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFCBD5E1'));

            // Total Row
            $summaryRow = $lastDataRow + 1;
            $sheet->mergeCells("A{$summaryRow}:J{$summaryRow}");
            $sheet->setCellValue("A{$summaryRow}", 'TOTAL BIAYA SEWA ALAT:');
            $sheet->setCellValue("K{$summaryRow}", "=SUM(K{$startDataRow}:K{$lastDataRow})");

            $summaryRange = "A{$summaryRow}:M{$summaryRow}";
            $sheet->getStyle($summaryRange)->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => '000000']],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FEF3C7'],
                ],
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']],
                ],
            ]);
            $sheet->getStyle("A{$summaryRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("K{$summaryRow}")->getNumberFormat()->setFormatCode('#,##0');

            // Signatures
            $signDateRow  = $summaryRow + 2;
            $signTitleRow = $summaryRow + 4;
            $signNameRow  = $signTitleRow + 4;
            $signRoleRow  = $signNameRow + 1;

            $sheet->mergeCells("J{$signDateRow}:M{$signDateRow}");
            $tglCetak = "Yogyakarta, " . $this->endDate->translatedFormat('d F Y');
            $sheet->setCellValue("J{$signDateRow}", $tglCetak);
            $sheet->getStyle("J{$signDateRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("J{$signDateRow}")->getFont()->setSize(10)->setItalic(true);

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
            $sheet->setCellValue("B{$signRoleRow}", "Staf Logistik Proyek");
            $sheet->getStyle("B{$signRoleRow}")->getFont()->setSize(9)->setItalic(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF64748B'));
            $sheet->getStyle("B{$signRoleRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Crosscheck
            $sheet->mergeCells("F{$signTitleRow}:H{$signTitleRow}");
            $sheet->setCellValue("F{$signTitleRow}", "Meng-crosscheck,");
            $sheet->getStyle("F{$signTitleRow}")->getFont()->setBold(true)->setSize(10);
            $sheet->getStyle("F{$signTitleRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $sheet->mergeCells("F{$signNameRow}:H{$signNameRow}");
            $sheet->setCellValue("F{$signNameRow}", "(  ________________________  )");
            $sheet->getStyle("F{$signNameRow}")->getFont()->setBold(true)->setSize(10);
            $sheet->getStyle("F{$signNameRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $sheet->mergeCells("F{$signRoleRow}:H{$signRoleRow}");
            $sheet->setCellValue("F{$signRoleRow}", "Site Manager / Pengawas");
            $sheet->getStyle("F{$signRoleRow}")->getFont()->setSize(9)->setItalic(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF64748B'));
            $sheet->getStyle("F{$signRoleRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Mengetahui
            $sheet->mergeCells("J{$signTitleRow}:M{$signTitleRow}");
            $sheet->setCellValue("J{$signTitleRow}", "Mengetahui,");
            $sheet->getStyle("J{$signTitleRow}")->getFont()->setBold(true)->setSize(10);
            $sheet->getStyle("J{$signTitleRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $sheet->mergeCells("J{$signNameRow}:M{$signNameRow}");
            $sheet->setCellValue("J{$signNameRow}", "(  ________________________  )");
            $sheet->getStyle("J{$signNameRow}")->getFont()->setBold(true)->setSize(10);
            $sheet->getStyle("J{$signNameRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $sheet->mergeCells("J{$signRoleRow}:M{$signRoleRow}");
            $sheet->setCellValue("J{$signRoleRow}", "Project Manager / Direksi");
            $sheet->getStyle("J{$signRoleRow}")->getFont()->setSize(9)->setItalic(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF64748B'));
            $sheet->getStyle("J{$signRoleRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        return [];
    }
}
