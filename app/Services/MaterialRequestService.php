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
     * Create Material Request from any source Warehouse to a Project Warehouse.
     */
    public function createRequest(
        Warehouse $toWarehouse,
        ?Warehouse $sourceWarehouse,
        User $requestedBy,
        array $itemsData,
        bool $submitImmediately = true,
        ?string $notes = null,
        ?string $neededAt = null,
        ?string $requestedAs = null
    ): MaterialRequest {
        if ($toWarehouse->is_central) {
            throw new Exception("PO Lapangan hanya dapat ditujukan ke Gudang Proyek.");
        }

        if ($sourceWarehouse && $toWarehouse->id === $sourceWarehouse->id) {
            throw new Exception("Sumber material tidak boleh sama dengan gudang pemohon.");
        }

        if (!$requestedBy->hasAccessToWarehouse($toWarehouse)) {
            throw new Exception("User tidak memiliki akses ke Gudang Proyek ini.");
        }

        if (empty($itemsData)) {
            throw new Exception("Daftar material yang diminta tidak boleh kosong.");
        }

        $finalRequestedAs = trim($requestedAs ?? '') ?: 'Admin Proyek';

        return DB::transaction(function () use ($toWarehouse, $sourceWarehouse, $requestedBy, $itemsData, $submitImmediately, $notes, $neededAt, $finalRequestedAs) {
            $request = MaterialRequest::create([
                'request_number'        => MaterialRequest::generateRequestNumber(),
                'from_warehouse_id'     => $sourceWarehouse ? $sourceWarehouse->id : null,  // gudang asal/sumber material (null = pengadaan vendor)
                'to_warehouse_id'       => $toWarehouse->id,                                 // gudang tujuan (pemohon)
                'requested_by_user_id'  => $requestedBy->id,
                'status'                => $submitImmediately ? 'submitted' : 'draft',
                'needed_at'             => $neededAt,
                'requested_as'          => $finalRequestedAs,
                'notes'                 => $notes,
            ]);

            foreach ($itemsData as $item) {
                $qtyRequested = (float) $item['qty_requested'];
                if ($qtyRequested <= 0) {
                    throw new Exception("Jumlah pengajuan harus > 0.");
                }

                // T14: Item alat (tool) dari master tool terdaftar
                if (!empty($item['tool_id'])) {
                    if (fmod($qtyRequested, 1) != 0) {
                        throw new Exception("Kuantitas alat pada permintaan material harus berupa bilangan bulat.");
                    }
                    $tool = \App\Models\Tool::findOrFail($item['tool_id']);
                    MaterialRequestItem::create([
                        'material_request_id' => $request->id,
                        'item_type'           => 'tool',
                        'tool_id'             => $tool->id,
                        'material_id'         => null,
                        'category_id'         => $tool->category_id,
                        'fulfillment'         => $item['fulfillment'] ?? ($sourceWarehouse ? 'stock' : 'purchase'),
                        'qty_requested'       => (int) $qtyRequested,
                        'qty_approved'        => $submitImmediately ? (int) $qtyRequested : 0,
                        'notes'               => $item['notes'] ?? null,
                        'specification'       => $item['specification'] ?? null,
                        'usage_location'      => $item['usage_location'] ?? null,
                        'usage_date'          => $item['usage_date'] ?? null,
                    ]);
                    continue;
                }

                // Custom manual item (tidak ada di gudang sumber / master)
                if (empty($item['material_id']) && empty($item['tool_id'])) {
                    $customName = trim($item['custom_item_name'] ?? '');
                    if ($customName === '') {
                        throw new Exception("Nama barang custom tidak boleh kosong.");
                    }

                    $itemType = ($item['item_type'] ?? '') === 'tool' ? 'tool' : 'material';

                    $customCategoryId = null;
                    if (!empty($item['category_id'])) {
                        $cat = \App\Models\Category::find($item['category_id']);
                        if (!$cat) {
                            throw new Exception("Kategori custom tidak valid.");
                        }
                        if ($cat->type === 'tool') {
                            $itemType = 'tool';
                        }
                        $customCategoryId = $cat->id;
                    }

                    if ($itemType === 'tool' && fmod($qtyRequested, 1) != 0) {
                        throw new Exception("Kuantitas alat pada permintaan material harus berupa bilangan bulat.");
                    }

                    MaterialRequestItem::create([
                        'material_request_id' => $request->id,
                        'item_type'           => $itemType,
                        'material_id'         => null,
                        'tool_id'             => null,
                        'category_id'         => $customCategoryId,
                        'custom_item_name'    => $customName,
                        'custom_item_type'    => trim($item['custom_item_type'] ?? '') ?: null,
                        'custom_item_unit'    => trim($item['custom_item_unit'] ?? ($itemType === 'tool' ? 'unit' : 'pcs')) ?: 'unit',
                        'fulfillment'         => 'purchase',
                        'qty_requested'       => $itemType === 'tool' ? (int) $qtyRequested : $qtyRequested,
                        'qty_approved'        => $submitImmediately ? ($itemType === 'tool' ? (int) $qtyRequested : $qtyRequested) : 0,
                        'notes'               => $item['notes'] ?? null,
                        'specification'       => $item['specification'] ?? null,
                        'usage_location'      => $item['usage_location'] ?? null,
                        'usage_date'          => $item['usage_date'] ?? null,
                    ]);
                    continue;
                }

                $material = Material::findOrFail($item['material_id']);

                MaterialRequestItem::create([
                    'material_request_id' => $request->id,
                    'material_id'         => $material->id,
                    'category_id'         => $material->category_id, // Server mengisi dari kategori materialnya dan mengabaikan nilai klien
                    'fulfillment'         => $item['fulfillment'] ?? ($sourceWarehouse ? 'stock' : 'purchase'),
                    'qty_requested'       => $qtyRequested,
                    'qty_approved'        => $submitImmediately ? $qtyRequested : 0,
                    'notes'               => $item['notes'] ?? null,
                    'specification'       => $item['specification'] ?? null,
                    'usage_location'      => $item['usage_location'] ?? null,
                    'usage_date'          => $item['usage_date'] ?? null,
                ]);
            }

            $loaded = $request->load('items.material', 'fromWarehouse', 'toWarehouse', 'requestedBy');

            if ($submitImmediately) {
                $sourceName = $sourceWarehouse ? $sourceWarehouse->name : 'Pengadaan / Beli ke Vendor';
                NotificationHelper::notifyCentralWarehouseAdmins(
                    "PO Lapangan: #{$request->request_number}",
                    "PO Lapangan diajukan oleh {$requestedBy->name} dari {$toWarehouse->name} (sumber: {$sourceName}).",
                    "approval_needed",
                    route('material-requests.show', $request)
                );

                // Jika sumber adalah Gudang Proyek lain, beritahu juga admin gudang proyek sumber tersebut
                if ($sourceWarehouse && !$sourceWarehouse->is_central) {
                    $sourceAdmins = User::whereHas('warehouses', fn($q) => $q->where('warehouses.id', $sourceWarehouse->id))
                        ->whereHas('roles', fn($r) => $r->where('name', 'Admin Gudang Proyek'))
                        ->get();
                    foreach ($sourceAdmins as $sAdmin) {
                        if ($sAdmin->id !== $requestedBy->id) {
                            NotificationHelper::notifyUser(
                                $sAdmin,
                                "PO Lapangan Keluar: #{$request->request_number}",
                                "Gudang {$toWarehouse->name} mengajukan PO Lapangan dari {$sourceWarehouse->name}.",
                                "info",
                                route('material-requests.show', $request)
                            );
                        }
                    }
                }

                // Jika terdapat item manual/custom, kirim notifikasi ke Admin PO
                $hasCustomItems = $request->items->contains(fn($it) => empty($it->material_id) || !empty($it->custom_item_name));
                if ($hasCustomItems) {
                    $customCount = $request->items->whereNull('material_id')->count();
                    NotificationHelper::notifyPurchasingAdmins(
                        "Kebutuhan Pengadaan (MR Manual): #{$request->request_number}",
                        "Terdapat {$customCount} item barang manual dari {$toWarehouse->name} yang memerlukan pengadaan / penerbitan PO.",
                        "warning",
                        route('purchase-orders.create', ['from_mr_id' => $request->id])
                    );
                }
            }

            return $loaded;
        });
    }

    /**
     * Update Material Request (PO Lapangan) header and items.
     */
    public function updateRequest(
        MaterialRequest $request,
        Warehouse $toWarehouse,
        ?Warehouse $sourceWarehouse,
        User $user,
        array $itemsData,
        ?string $notes = null,
        ?string $neededAt = null,
        ?string $requestedAs = null
    ): MaterialRequest {
        if (!in_array($request->status, ['submitted', 'draft'])) {
            throw new Exception("Hanya pengajuan dengan status draft atau menunggu persetujuan yang dapat diubah.");
        }

        if ($request->requested_by_user_id !== $user->id && !$user->hasAnyRole(['Owner', 'Admin Pusat'])) {
            throw new Exception("Anda tidak memiliki izin untuk mengubah pengajuan ini.");
        }

        if ($toWarehouse->is_central) {
            throw new Exception("PO Lapangan hanya dapat ditujukan ke Gudang Proyek.");
        }

        if ($sourceWarehouse && $toWarehouse->id === $sourceWarehouse->id) {
            throw new Exception("Sumber material tidak boleh sama dengan gudang pemohon.");
        }

        if (!$user->hasAccessToWarehouse($toWarehouse)) {
            throw new Exception("User tidak memiliki akses ke Gudang Proyek ini.");
        }

        if (empty($itemsData)) {
            throw new Exception("Daftar material yang diminta tidak boleh kosong.");
        }

        $finalRequestedAs = trim($requestedAs ?? '') ?: ($request->requested_as ?: 'Admin Proyek');

        return DB::transaction(function () use ($request, $toWarehouse, $sourceWarehouse, $itemsData, $notes, $neededAt, $finalRequestedAs) {
            $request->update([
                'to_warehouse_id'   => $toWarehouse->id,
                'from_warehouse_id' => $sourceWarehouse ? $sourceWarehouse->id : null,
                'needed_at'         => $neededAt,
                'requested_as'      => $finalRequestedAs,
                'notes'             => $notes,
            ]);

            // Sinkronisasi item: update item yang ada tanpa mengubah qty_approved, buat item baru, dan hapus yang dihilangkan
            $existingItems = $request->items()->get();
            $handledItemIds = [];

            foreach ($itemsData as $item) {
                $qtyRequested = (float) $item['qty_requested'];
                if ($qtyRequested <= 0) continue;

                if (empty($item['material_id']) && empty($item['tool_id'])) {
                    $customName = trim($item['custom_item_name'] ?? '');
                    if ($customName === '') continue;

                    $itemType = ($item['item_type'] ?? '') === 'tool' ? 'tool' : 'material';

                    $customCategoryId = null;
                    if (!empty($item['category_id'])) {
                        $cat = \App\Models\Category::find($item['category_id']);
                        if (!$cat) {
                            throw new Exception("Kategori custom tidak valid.");
                        }
                        if ($cat->type === 'tool') {
                            $itemType = 'tool';
                        }
                        $customCategoryId = $cat->id;
                    }

                    if ($itemType === 'tool' && fmod($qtyRequested, 1) != 0) {
                        throw new Exception("Kuantitas alat pada permintaan material harus berupa bilangan bulat.");
                    }

                    $matchCustom = $existingItems->first(function ($it) use ($customName, $handledItemIds) {
                        return empty($it->material_id) && empty($it->tool_id) &&
                               !in_array($it->id, $handledItemIds) &&
                               strtolower(trim((string)$it->custom_item_name)) === strtolower($customName);
                    });

                    if ($matchCustom) {
                        $matchCustom->update([
                            'item_type'        => $itemType,
                            'category_id'      => $customCategoryId,
                            'custom_item_type' => trim($item['custom_item_type'] ?? '') ?: null,
                            'custom_item_unit' => trim($item['custom_item_unit'] ?? ($itemType === 'tool' ? 'unit' : 'pcs')) ?: 'unit',
                            'fulfillment'      => 'purchase',
                            'qty_requested'    => $itemType === 'tool' ? (int) $qtyRequested : $qtyRequested,
                            'notes'            => $item['notes'] ?? null,
                            'specification'    => $item['specification'] ?? null,
                            'usage_location'   => $item['usage_location'] ?? null,
                            'usage_date'       => $item['usage_date'] ?? null,
                        ]);
                        $handledItemIds[] = $matchCustom->id;
                    } else {
                        $newItem = MaterialRequestItem::create([
                            'material_request_id' => $request->id,
                            'item_type'           => $itemType,
                            'material_id'         => null,
                            'tool_id'             => null,
                            'category_id'         => $customCategoryId,
                            'custom_item_name'    => $customName,
                            'custom_item_type'    => trim($item['custom_item_type'] ?? '') ?: null,
                            'custom_item_unit'    => trim($item['custom_item_unit'] ?? ($itemType === 'tool' ? 'unit' : 'pcs')) ?: 'unit',
                            'fulfillment'         => 'purchase',
                            'qty_requested'       => $itemType === 'tool' ? (int) $qtyRequested : $qtyRequested,
                            'qty_approved'        => 0,
                            'notes'               => $item['notes'] ?? null,
                            'specification'       => $item['specification'] ?? null,
                            'usage_location'      => $item['usage_location'] ?? null,
                            'usage_date'          => $item['usage_date'] ?? null,
                        ]);
                        $handledItemIds[] = $newItem->id;
                    }
                } else {
                    $material = Material::findOrFail($item['material_id']);

                    $matchMat = $existingItems->first(function ($it) use ($material, $handledItemIds) {
                        return (int)$it->material_id === (int)$material->id && !in_array($it->id, $handledItemIds);
                    });

                    if ($matchMat) {
                        $matchMat->update([
                            'category_id'    => $material->category_id,
                            'fulfillment'    => $matchMat->fulfillment ?: ($sourceWarehouse ? 'stock' : 'purchase'),
                            'qty_requested'  => $qtyRequested,
                            'notes'          => $item['notes'] ?? null,
                            'specification'  => $item['specification'] ?? null,
                            'usage_location' => $item['usage_location'] ?? null,
                            'usage_date'     => $item['usage_date'] ?? null,
                        ]);
                        $handledItemIds[] = $matchMat->id;
                    } else {
                        $newItem = MaterialRequestItem::create([
                            'material_request_id' => $request->id,
                            'item_type'           => 'material',
                            'material_id'         => $material->id,
                            'category_id'         => $material->category_id,
                            'custom_item_name'    => null,
                            'custom_item_unit'    => null,
                            'fulfillment'         => $sourceWarehouse ? 'stock' : 'purchase',
                            'qty_requested'       => $qtyRequested,
                            'qty_approved'        => 0,
                            'notes'               => $item['notes'] ?? null,
                            'specification'       => $item['specification'] ?? null,
                            'usage_location'      => $item['usage_location'] ?? null,
                            'usage_date'          => $item['usage_date'] ?? null,
                        ]);
                        $handledItemIds[] = $newItem->id;
                    }
                }
            }

            // Hapus item lama yang tidak ada di data yang diupdate
            $itemsToDelete = $existingItems->whereNotIn('id', $handledItemIds);
            foreach ($itemsToDelete as $delItem) {
                $delItem->delete();
            }

            return $request->fresh(['items.material', 'fromWarehouse', 'toWarehouse', 'requestedBy']);
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

        if ($request->requested_by_user_id === $approvedBy->id) {
            throw new Exception("Pembuat permintaan tidak dapat menyetujui pengajuannya sendiri.");
        }

        return DB::transaction(function () use ($request, $approvedBy, $approvedItemsQty) {
            foreach ($request->items as $item) {
                $qtyApproved = isset($approvedItemsQty[$item->id])
                    ? (float) $approvedItemsQty[$item->id]
                    : (float) $item->qty_requested;

                if ($qtyApproved < 0) {
                    throw new Exception("Kuantitas persetujuan tidak boleh negatif.");
                }

                $updates = ['qty_approved' => $qtyApproved];

                if (empty($item->fulfillment)) {
                    $updates['fulfillment'] = ($request->from_warehouse_id === null || $item->isCustom()) ? 'purchase' : 'stock';
                }

                // Jika item custom dan belum tertaut material_id/tool_id, coba tautkan ke master yang ada
                if (empty($item->material_id) && empty($item->tool_id) && !empty($item->custom_item_name)) {
                    $cleanName = trim($item->custom_item_name);
                    $matchedMat = Material::whereRaw('LOWER(TRIM(name)) = ?', [strtolower($cleanName)])->first();
                    if ($matchedMat) {
                        $updates['material_id'] = $matchedMat->id;
                        if (empty($item->category_id) && $matchedMat->category_id) {
                            $updates['category_id'] = $matchedMat->category_id;
                        }
                    } else {
                        $matchedTool = Tool::whereRaw('LOWER(TRIM(name)) = ?', [strtolower($cleanName)])->first();
                        if ($matchedTool) {
                            $updates['tool_id'] = $matchedTool->id;
                            if (empty($item->category_id) && $matchedTool->category_id) {
                                $updates['category_id'] = $matchedTool->category_id;
                            }
                        }
                    }
                }

                $item->update($updates);
            }

            $request->update([
                'status' => 'approved',
                'approved_by_user_id' => $approvedBy->id,
            ]);

            // Notify requestedBy directly and then other warehouse admins (excluding regular karyawan)
            if ($request->requestedBy) {
                NotificationHelper::notifyUser(
                    $request->requestedBy,
                    "PO Lapangan Disetujui: #{$request->request_number}",
                    "PO Lapangan #{$request->request_number} dari {$request->fromWarehouse?->name} telah disetujui oleh {$approvedBy->name}.",
                    "success",
                    route('material-requests.show', $request)
                );
            }
            $projectAdmins = User::whereHas('roles', fn($r) => $r->where('name', 'Admin Gudang Proyek'))
                ->whereHas('warehouses', fn($q) => $q->where('warehouses.id', $request->from_warehouse_id))
                ->whereDoesntHave('roles', fn($r) => $r->where('name', 'Karyawan'))
                ->get();
            $allTargets = collect($projectAdmins)->merge($request->requestedBy ? [$request->requestedBy] : [])->unique('id');
            foreach ($allTargets as $targetUser) {
                if ($request->requestedBy && $targetUser->id === $request->requestedBy->id) {
                    continue; // already notified
                }
                NotificationHelper::notifyUser(
                    $targetUser,
                    "PO Lapangan Disetujui: #{$request->request_number}",
                    "PO Lapangan #{$request->request_number} dari {$request->fromWarehouse?->name} telah disetujui oleh {$approvedBy->name}.",
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
                "PO Lapangan Ditolak: #{$request->request_number}",
                "PO Lapangan Anda ditolak oleh {$rejectedBy->name}. Alasan: {$rejectionReason}",
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
        if ($user->hasAnyRole(['Owner', 'Admin Pusat'])) {
            if ($activeWarehouseId) {
                $query->where(function ($q) use ($activeWarehouseId) {
                    $q->where('from_warehouse_id', $activeWarehouseId)
                      ->orWhere('to_warehouse_id', $activeWarehouseId);
                });
            }
            return $query;
        }

        // Admin Gudang Pusat: dapat melihat seluruh pengajuan untuk keperluan approval dan monitoring
        if ($user->hasRole('Admin Gudang Pusat')) {
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
