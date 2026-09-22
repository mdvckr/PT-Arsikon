<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Models\Project;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    public function index()
    {
        $this->authorize('warehouses.manage');
        $warehouses = Warehouse::withCount(['inventories', 'users'])->latest()->paginate(15);

        return view('warehouses.index', compact('warehouses'));
    }

    public function create()
    {
        $this->authorize('warehouses.manage');
        $projects = Project::orderBy('name')->get();

        return view('warehouses.create', compact('projects'));
    }

    public function store(Request $request)
    {
        $this->authorize('warehouses.manage');

        // Normalize input
        $rawType = $request->input('type');
        $type = in_array($rawType, ['central', 'main', 'pusat']) ? 'central' : 'project';
        $request->merge([
            'type'    => $type,
            'address' => $request->input('address') ?? $request->input('location'),
        ]);

        if (!$request->filled('code')) {
            $request->merge(['code' => 'W-' . strtoupper(\Illuminate\Support\Str::random(5))]);
        }

        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'code'       => 'required|string|max:30|unique:warehouses,code',
            'type'       => 'required|in:central,project',
            'project_id' => 'nullable|exists:projects,id',
            'address'    => 'nullable|string',
            'is_active'  => 'nullable|boolean',
        ]);

        $isCentral = ($type === 'central');

        $warehouse = Warehouse::create([
            'name'       => $validated['name'],
            'code'       => strtoupper($validated['code']),
            'type'       => $type,
            'is_central' => $isCentral,
            'is_active'  => $request->has('is_active') ? $request->boolean('is_active') : true,
            'project_id' => $isCentral ? null : ($validated['project_id'] ?? null),
            'address'    => $validated['address'] ?? null,
        ]);

        return redirect()->route('warehouses.index')
            ->with('success', "Gudang '{$warehouse->name}' berhasil ditambahkan.");
    }

    public function edit(Warehouse $warehouse)
    {
        $this->authorize('warehouses.manage');
        $projects = Project::orderBy('name')->get();

        return view('warehouses.edit', compact('warehouse', 'projects'));
    }

    public function update(Request $request, Warehouse $warehouse)
    {
        $this->authorize('warehouses.manage');

        // Normalize input
        $rawType = $request->input('type');
        $type = in_array($rawType, ['central', 'main', 'pusat']) ? 'central' : 'project';
        $request->merge([
            'type'    => $type,
            'address' => $request->input('address') ?? $request->input('location'),
            'code'    => $request->filled('code') ? $request->input('code') : $warehouse->code,
        ]);

        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'code'       => "required|string|max:30|unique:warehouses,code,{$warehouse->id}",
            'type'       => 'required|in:central,project',
            'project_id' => 'nullable|exists:projects,id',
            'address'    => 'nullable|string',
            'is_active'  => 'nullable|boolean',
        ]);

        $isCentral = ($type === 'central');

        $warehouse->update([
            'name'       => $validated['name'],
            'code'       => strtoupper($validated['code']),
            'type'       => $type,
            'is_central' => $isCentral,
            'is_active'  => $request->has('is_active') ? $request->boolean('is_active') : $warehouse->is_active,
            'project_id' => $isCentral ? null : ($validated['project_id'] ?? null),
            'address'    => $validated['address'] ?? null,
        ]);

        return redirect()->route('warehouses.index')
            ->with('success', "Gudang '{$warehouse->name}' berhasil diperbarui.");
    }

    public function destroy(Warehouse $warehouse)
    {
        $this->authorize('warehouses.manage');

        // Cek apakah gudang masih memiliki inventory atau transaksi aktif
        $inventoryCount = $warehouse->inventories()->where('quantity', '>', 0)->count();
        if ($inventoryCount > 0) {
            return redirect()->route('warehouses.index')
                ->with('error', "Gudang '{$warehouse->name}' tidak dapat dihapus karena masih memiliki {$inventoryCount} item stok aktif.");
        }

        $userCount = $warehouse->users()->count();
        if ($userCount > 0) {
            return redirect()->route('warehouses.index')
                ->with('error', "Gudang '{$warehouse->name}' tidak dapat dihapus karena masih memiliki {$userCount} user yang ditugaskan.");
        }

        $name = $warehouse->name;
        $warehouse->delete();

        return redirect()->route('warehouses.index')
            ->with('success', "Gudang '{$name}' berhasil dihapus.");
    }
}
