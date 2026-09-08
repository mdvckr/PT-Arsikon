<?php

namespace App\Services;

use App\Models\Material;
use App\Models\MaterialRequest;
use App\Models\MaterialRequestItem;
use App\Models\User;
use App\Models\Warehouse;
use Exception;
use Illuminate\Support\Facades\DB;

class MaterialRequestService
{
    /**
     * Create Material Request from Project Warehouse to Central Warehouse.
     */
    public function createRequest(
        Warehouse $fromWarehouse,
        User $requestedBy,
        array $itemsData,
        bool $submitImmediately = true,
        ?string $notes = null
    ): MaterialRequest {
        if ($fromWarehouse->is_central) {
            throw new Exception("Request pengajuan barang hanya boleh dibuat oleh Gudang Proyek.");
        }

        if (!$requestedBy->hasAccessToWarehouse($fromWarehouse)) {
            throw new Exception("User tidak memiliki akses ke Gudang Proyek ini.");
        }

        if (empty($itemsData)) {
            throw new Exception("Daftar material yang diminta tidak boleh kosong.");
        }

        $centralWarehouse = Warehouse::where('is_central', true)->firstOrFail();

        return DB::transaction(function () use ($fromWarehouse, $centralWarehouse, $requestedBy, $itemsData, $submitImmediately, $notes) {
            $request = MaterialRequest::create([
                'request_number' => MaterialRequest::generateRequestNumber(),
                'from_warehouse_id' => $fromWarehouse->id,
                'to_warehouse_id' => $centralWarehouse->id,
                'requested_by_user_id' => $requestedBy->id,
                'status' => $submitImmediately ? 'submitted' : 'draft',
                'notes' => $notes,
            ]);

            foreach ($itemsData as $item) {
                $material = Material::findOrFail($item['material_id']);
                $qtyRequested = (float) $item['qty_requested'];

                if ($qtyRequested <= 0) {
                    throw new Exception("Jumlah pengajuan untuk material {$material->name} harus > 0.");
                }

                MaterialRequestItem::create([
                    'material_request_id' => $request->id,
                    'material_id' => $material->id,
                    'qty_requested' => $qtyRequested,
                    'qty_approved' => $submitImmediately ? $qtyRequested : 0,
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            return $request->load('items.material', 'fromWarehouse', 'toWarehouse', 'requestedBy');
        });
    }

    /**
     * Approve Material Request by Central Warehouse Admin.
     */
    public function approveRequest(
        MaterialRequest $request,
        User $approvedBy,
        ?array $approvedItemsQty = null
    ): MaterialRequest {
        if ($request->status !== 'submitted') {
            throw new Exception("Hanya pengajuan berstatus 'submitted' yang dapat disetujui.");
        }

        return DB::transaction(function () use ($request, $approvedBy, $approvedItemsQty) {
            foreach ($request->items as $item) {
                $qtyApproved = isset($approvedItemsQty[$item->id])
                    ? (float) $approvedItemsQty[$item->id]
                    : (float) $item->qty_requested;

                if ($qtyApproved < 0) {
                    throw new Exception("Kuantitas persetujuan tidak boleh negatif.");
                }

                $item->update([
                    'qty_approved' => $qtyApproved,
                ]);
            }

            $request->update([
                'status' => 'approved',
                'approved_by_user_id' => $approvedBy->id,
            ]);

            return $request->fresh('items.material');
        });
    }

    /**
     * Reject Material Request by Central Warehouse Admin.
     */
    public function rejectRequest(
        MaterialRequest $request,
        User $rejectedBy,
        string $rejectionReason
    ): MaterialRequest {
        if ($request->status !== 'submitted') {
            throw new Exception("Hanya pengajuan berstatus 'submitted' yang dapat ditolak.");
        }

        $request->update([
            'status' => 'rejected',
            'approved_by_user_id' => $rejectedBy->id,
            'rejection_reason' => $rejectionReason,
        ]);

        return $request->fresh();
    }

    /**
     * Cancel Material Request by User.
     */
    public function cancelRequest(MaterialRequest $request, User $user): MaterialRequest
    {
        if (!in_array($request->status, ['draft', 'submitted'])) {
            throw new Exception("Pengajuan berstatus '{$request->status}' tidak dapat dibatalkan.");
        }

        $request->update([
            'status' => 'cancelled',
        ]);

        return $request->fresh();
    }
}
