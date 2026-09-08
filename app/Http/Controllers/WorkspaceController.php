<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WorkspaceController extends Controller
{
    /**
     * Switch active warehouse in session.
     */
    public function switch(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'warehouse_id' => ['required', 'integer', 'exists:warehouses,id'],
        ]);

        $warehouse = Warehouse::findOrFail($validated['warehouse_id']);
        $user = $request->user();

        if (!$user->hasAccessToWarehouse($warehouse)) {
            return back()->with('error', 'Anda tidak memiliki akses ke workspace / gudang ini.');
        }

        session(['active_warehouse_id' => $warehouse->id]);

        return back()->with('success', 'Workspace berhasil diubah ke: ' . $warehouse->name);
    }
}
