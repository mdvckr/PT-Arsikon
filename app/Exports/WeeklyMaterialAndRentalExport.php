<?php

namespace App\Exports;

use App\Models\MaterialLedger;
use App\Models\Project;
use App\Services\MaterialLedgerService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class WeeklyMaterialAndRentalExport implements WithMultipleSheets
{
    use Exportable;

    protected Collection $ledgers;
    protected Collection $rentals;
    protected ?Project $project;
    protected Carbon $startDate;
    protected Carbon $endDate;

    public function __construct(
        Collection $ledgers,
        Collection $rentals,
        ?Project $project = null,
        ?Carbon $startDate = null,
        ?Carbon $endDate = null
    ) {
        $this->ledgers   = $ledgers;
        $this->rentals   = $rentals;
        $this->project   = $project;
        $this->startDate = $startDate ?? now()->startOfWeek(Carbon::MONDAY);
        $this->endDate   = $endDate ?? now()->endOfWeek(Carbon::SUNDAY);
    }

    public function sheets(): array
    {
        return [
            new MaterialLedgerSheet($this->ledgers, $this->project, $this->startDate, $this->endDate),
            new ToolRentalSheet($this->rentals, $this->project, $this->startDate, $this->endDate),
        ];
    }
}
