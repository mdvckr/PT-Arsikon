<?php

namespace App\Exports;

use App\Models\Warehouse;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class MonthlyStockGroupExport implements WithMultipleSheets
{
    use Exportable;

    protected int $month;
    protected int $year;
    protected ?Warehouse $warehouse;

    public function __construct(int $month, int $year, ?Warehouse $warehouse = null)
    {
        $this->month     = $month;
        $this->year      = $year;
        $this->warehouse = $warehouse;
    }

    public function sheets(): array
    {
        return [
            new MaterialGroupStockSheet($this->month, $this->year, $this->warehouse),
            new ToolGroupStockSheet($this->month, $this->year, $this->warehouse),
        ];
    }
}
