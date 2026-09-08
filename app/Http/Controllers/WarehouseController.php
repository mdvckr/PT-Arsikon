<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Models\Project;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    public function index()
    {
        $this->authorize('view users');
        $warehouses = Warehouse::withCount(['inventories', 'users'])->latest()->paginate(15);

        return view('warehouses.index', compact('warehouses'));
    }

    public function create()
    {
        $this->authorize('view users');
        $projects = Project::orderBy('name')->get();

        return view('warehouses.create', compact('projects'));
    }

    public function store(Request $request)
    {
        $this->authorize('view users');

        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'code'       => 'required|string|max:20|unique:warehouses,code',
            'type'       => 'required|in:pusat,proyek',
            'project_id' => 'nullable|exists:projects,id',
            'address'    => 'nullable|string',
            'phone'      => 'nullable|string|max:30',
        ]);

        Warehouse::create($validated);

        return redirect()->route('warehouses.index')
            ->with('success', "Gudang '{$validated['name']}' berhasil ditambahkan.");
    }

    public function edit(Warehouse $warehouse)
    {
        $this->authorize('view users');
        $projects = Project::orderBy('name')->get();

        return view('warehouses.edit', compact('warehouse', 'projects'));
    }

    public function update(Request $request, Warehouse $warehouse)
    {
        $this->authorize('view users');

        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'code'       => "required|string|max:20|unique:warehouses,code,{$warehouse->id}",
            'type'       => 'required|in:pusat,proyek',
            'project_id' => 'nullable|exists:projects,id',
            'address'    => 'nullable|string',
            'phone'      => 'nullable|string|max:30',
        ]);

        $warehouse->update($validated);

        return redirect()->route('warehouses.index')
            ->with('success', "Gudang '{$warehouse->name}' berhasil diperbarui.");
    }

    public function destroy(Warehouse $warehouse)
    {
        $this->authorize('view users');
        $name = $warehouse->name;
        $warehouse->delete();

        return redirect()->route('warehouses.index')
            ->with('success', "Gudang '{$name}' berhasil dihapus.");
    }
}

