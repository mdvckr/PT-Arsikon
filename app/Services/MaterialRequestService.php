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
                $qtyRequested = (float) $item['qty_requested'];
                if ($qtyRequested <= 0) {
                    throw new Exception("Jumlah pengajuan harus > 0.");
                }

                // Custom manual item (tidak ada di Gudang Pusat)
                if (empty($item['material_id'])) {
                    $customName = trim($item['custom_item_name'] ?? '');
                    if ($customName === '') {
                        throw new Exception("Nama barang custom tidak boleh kosong.");
                    }
                    MaterialRequestItem::create([
                        'material_request_id' => $request->id,
                        'material_id' => null,
                        'custom_item_name' => $customName,
                        'custom_item_unit' => trim($item['custom_item_unit'] ?? 'unit') ?: 'unit',
                        'qty_requested' => $qtyRequested,
                        'qty_approved' => $submitImmediately ? $qtyRequested : 0,
                        'notes' => $item['notes'] ?? null,
                    ]);
                    continue;
                }

                $material = Material::findOrFail($item['material_id']);

                MaterialRequestItem::create([
                    'material_request_id' => $request->id,
                    'material_id' => $material->id,
                    'qty_requested' => $qtyRequested,
                    'qty_approved' => $submitImmediately ? $qtyRequested : 0,
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            $loaded = $request->load('items.material', 'fromWarehouse', 'toWarehouse', 'requestedBy');

            if ($submitImmediately) {
                NotificationHelper::notifyApprovers(
                    "Permintaan Material: #{$request->request_number}",
                    "Permintaan material diajukan oleh {$requestedBy->name} dari {$fromWarehouse->name}.",
                    "approval_needed",
                    route('material-requests.show', $request),
                    $fromWarehouse->id
                );
            }

            return $loaded;
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

            // Notify requestedBy directly and then other warehouse users
            if ($request->requestedBy) {
                NotificationHelper::notifyUser(
                    $request->requestedBy,
                    "Permintaan Material Disetujui: #{$request->request_number}",
                    "Permintaan material #{$request->request_number} dari {$request->fromWarehouse?->name} telah disetujui oleh {$approvedBy->name}.",
                    "success",
                    route('material-requests.show', $request)
                );
            }
            $projectUsers = User::whereHas('warehouses', fn($q) => $q->where('warehouses.id', $request->from_warehouse_id))->get();
            $allTargets = collect($projectUsers)->merge($request->requestedBy ? [$request->requestedBy] : [])->unique('id');
            foreach ($allTargets as $targetUser) {
                if ($request->requestedBy && $targetUser->id === $request->requestedBy->id) {
                    continue; // already notified
                }
                NotificationHelper::notifyUser(
                    $targetUser,
                    "Permintaan Material Disetujui: #{$request->request_number}",
                    "Permintaan material #{$request->request_number} dari {$request->fromWarehouse?->name} telah disetujui oleh {$approvedBy->name}.",
                    "info",
                    route('material-requests.show', $request)
                );
            }

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

        if ($request->requestedBy) {
            NotificationHelper::notifyUser(
                $request->requestedBy,
                "Permintaan Material Ditolak: #{$request->request_number}",
                "Permintaan material Anda ditolak oleh {$rejectedBy->name}. Alasan: {$rejectionReason}",
                "danger",
                route('material-requests.show', $request)
            );
        }

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

    /**
     * Get Eloquent query for MaterialRequest scoped to the user's role and warehouse access.
     */
    public function getScopedRequestsQuery(User $user, ?int $activeWarehouseId = null)
    {
        $query = MaterialRequest::query()->with(['requestedBy', 'fromWarehouse', 'toWarehouse', 'approvedBy', 'items.material.unit']);

        // Owner & Admin can view everything, optionally filtered by active warehouse
        if ($user->hasAnyRole(['Owner', 'Admin'])) {
            if ($activeWarehouseId) {
                $query->where(function ($q) use ($activeWarehouseId) {
                    $q->where('from_warehouse_id', $activeWarehouseId)
                      ->orWhere('to_warehouse_id', $activeWarehouseId);
                });
            }
            return $query;
        }

        // Admin Gudang Pusat: hanya melihat pengajuan yang melibatkan Gudang Pusat
        if ($user->hasRole('Admin Gudang Pusat')) {
            $centralWarehouseIds = Warehouse::where('is_central', true)->pluck('id')->toArray();
            $query->where(function ($q) use ($centralWarehouseIds) {
                $q->whereIn('to_warehouse_id', $centralWarehouseIds)
                  ->orWhereIn('from_warehouse_id', $centralWarehouseIds);
            });
            return $query;
        }

        // Gudang Proyek / Karyawan / Admin Proyek: hanya melihat data gudang proyek mereka
        $userWarehouseIds = $user->warehouses->pluck('id')->toArray();
        if ($activeWarehouseId && in_array($activeWarehouseId, $userWarehouseIds)) {
            $query->where(function ($q) use ($activeWarehouseId) {
                $q->where('from_warehouse_id', $activeWarehouseId)
                  ->orWhere('to_warehouse_id', $activeWarehouseId);
            });
        } else {
            $query->where(function ($q) use ($userWarehouseIds) {
                $q->whereIn('from_warehouse_id', $userWarehouseIds)
                  ->orWhereIn('to_warehouse_id', $userWarehouseIds);
            });
        }

        return $query;
    }
}
