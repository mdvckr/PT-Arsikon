<?php

namespace App\Http\Controllers;

use App\Services\DashboardMetricService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    protected DashboardMetricService $metricService;

    public function __construct(DashboardMetricService $metricService)
    {
        $this->metricService = $metricService;
    }

    public function index()
    {
        $user = Auth::user();
        $activeWarehouse = $user->activeWarehouse();
        $warehouseId = $activeWarehouse?->id ?? session('active_warehouse_id');
        $accessibleIds = $user->accessibleWarehouseIds();

        // Determine Dashboard Persona
        if ($user->hasAnyRole(['Admin Pengadaan', 'Admin Pengadaan']) && !$user->hasAnyRole(['Owner', 'Super Admin', 'Admin Pusat'])) {
            $persona = 'procurement_center';
            $data = $this->metricService->getProcurementCenterMetrics();
        } elseif ($user->hasAnyRole(['Admin Pengadaan Proyek', 'Admin Pengadaan Proyek', 'Admin Pengadaan Proyek', 'Admin Pengadaan Proyek']) && !$user->hasAnyRole(['Owner', 'Super Admin', 'Admin Pusat', 'Admin Pengadaan', 'Admin Pengadaan'])) {
            $persona = 'site_purchasing';
            $data = $this->metricService->getSitePurchasingMetrics($user);
        } elseif ($user->hasAnyRole(['Owner', 'Super Admin', 'Admin Pusat'])) {
            $persona = 'executive';
            $data = $this->metricService->getExecutiveMetrics();
            $warehouseData = $this->metricService->getWarehouseMetrics($user, $warehouseId, $accessibleIds);
            $procurementData = $this->metricService->getProcurementCenterMetrics();
            $data = array_merge($data, $warehouseData, ['procurement' => $procurementData]);
        } else {
            $persona = 'warehouse';
            $data = $this->metricService->getWarehouseMetrics($user, $warehouseId, $accessibleIds);
        }

        return view('dashboard', array_merge(['persona' => $persona], $data));
    }
}
