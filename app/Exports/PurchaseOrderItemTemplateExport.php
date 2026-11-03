<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PurchaseOrderItemTemplateExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles
{
    public function headings(): array
    {
        return [
            'Nama Barang / SKU',
            'Jumlah (Qty)',
            'Satuan',
            'Harga Satuan (Rp)',
            'Catatan / Keterangan',
        ];
    }

    public function array(): array
    {
        return [
            [
                'Semen Gresik 50kg',
                50,
                'sak',
                68000,
                'Tipe I SNI untuk pengecoran balok',
            ],
            [
                'Besi Beton Ulir D16',
                100,
                'batang',
                145000,
                'Standar SNI Panjang 12 meter',
            ],
            [
                'Paku Seng Ulir 7cm',
                10,
                'dus',
                45000,
                'Contoh item belanja proyek / non-katalog',
            ],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold'  => true,
                    'color' => ['rgb' => '0F172A'],
                ],
                'fill' => [
                    'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E2E8F0'],
                ],
            ],
        ];
    }
}

