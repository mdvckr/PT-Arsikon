<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use App\Models\Warehouse;
use App\Models\Category;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(private ReportService $service) {}

    public function index()
    {
        $this->authorize('view reports');
        $warehouses = Warehouse::orderBy('name')->get();
        $categories = Category::orderBy('name')->get();

        return view('reports.index', compact('warehouses', 'categories'));
    }

    public function stockReport(Request $request)
    {
        $this->authorize('view reports');

        $request->validate([
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'category_id'  => 'nullable|exists:categories,id',
        ]);

        $warehouseId = $request->warehouse_id ?? session('active_warehouse_id');
        $data = $this->service->stockReport($warehouseId, $request->category_id);

        return view('reports.stock', compact('data', 'warehouseId'));
    }

    public function mutationReport(Request $request)
    {
        $this->authorize('view reports');

        $request->validate([
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'date_from'    => 'required|date',
            'date_to'      => 'required|date|after_or_equal:date_from',
        ]);

        $warehouseId = $request->warehouse_id ?? session('active_warehouse_id');
        $data = $this->service->mutationReport(
            $warehouseId,
            $request->date_from,
            $request->date_to
        );

        return view('reports.mutation', compact('data', 'warehouseId'));
    }

    public function discrepancyReport(Request $request)
    {
        $this->authorize('view reports');

        $warehouseId = $request->warehouse_id ?? session('active_warehouse_id');
        $data = $this->service->discrepancyReport($warehouseId);

        return view('reports.discrepancy', compact('data', 'warehouseId'));
    }

    public function toolReport(Request $request)
    {
        $this->authorize('view reports');

        $data = $this->service->toolReport();

        return view('reports.tools', compact('data'));
    }
}
