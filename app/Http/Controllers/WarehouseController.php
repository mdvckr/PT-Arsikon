<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class WarehouseController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('warehouses.manage');

        $query = Warehouse::with(['project'])
            ->withCount([
                'inventories',
                'inventories as active_stock_count' => fn ($q) => $q->where('quantity', '>', 0),
                'inventories as low_stock_count' => fn ($q) => $q
                    ->whereColumn('quantity', '<=', 'min_stock')
                    ->where('min_stock', '>', 0),
                'users',
            ]);

        if ($request->filled('search')) {
            $s = trim((string) $request->input('search'));
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('code', 'like', "%{$s}%")
                    ->orWhere('address', 'like', "%{$s}%")
                    ->orWhereHas('project', fn ($p) => $p->where('name', 'like', "%{$s}%"));
            });
        }

        if ($request->filled('type')) {
            if (in_array($request->type, ['main', 'central', 'pusat'], true)) {
                $query->central();
            } elseif ($request->type === 'project') {
                $query->project();
            }
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $warehouses = $query->latest()->paginate(15)->withQueryString();

        $warehouseCount = Warehouse::count();
        $projectCount = Project::count();

        return view('warehouses.index', compact('warehouses', 'warehouseCount', 'projectCount'));
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

        $rawType = $request->input('type');
        $type = in_array($rawType, ['central', 'main', 'pusat'], true) ? 'central' : 'project';

        $request->merge([
            'type' => $type,
            'address' => $request->input('address') ?? $request->input('location'),
        ]);

        if (! $request->filled('code')) {
            $request->merge(['code' => 'W-'.strtoupper(Str::random(5))]);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:30|unique:warehouses,code',
            'type' => 'required|in:central,project',
            'project_id' => 'nullable|exists:projects,id',
            'project_name' => [
                Rule::requiredIf(fn () => $type === 'project' && ! $request->filled('project_id')),
                'nullable',
                'string',
                'max:255',
            ],
            'address' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
        ]);

        $projectId = $validated['project_id'] ?? null;

        if ($type === 'project' && $request->filled('project_name')) {
            $project = Project::firstOrCreate(
                ['name' => trim((string) $request->input('project_name'))],
                [
                    'code' => 'PRJ-'.strtoupper(Str::random(5)),
                    'location' => $validated['address'] ?? null,
                    'status' => 'active',
                    'start_date' => date('Y-m-d'),
                ]
            );
            $projectId = $project->id;
        }

        $warehouse = Warehouse::create([
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
            'type' => $type,
            'is_central' => $type === 'central',
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : true,
            'project_id' => $type === 'central' ? null : $projectId,
            'address' => $validated['address'] ?? null,
        ]);

        return redirect()->route('warehouses.index')
            ->with('success', "Gudang '{$warehouse->name}' berhasil ditambahkan.");
    }

    public function edit(Warehouse $warehouse)
    {
        $this->authorize('warehouses.manage');
        $warehouse->loadCount(['inventories', 'users']);
        $projects = Project::orderBy('name')->get();

        return view('warehouses.edit', compact('warehouse', 'projects'));
    }

    public function update(Request $request, Warehouse $warehouse)
    {
        $this->authorize('warehouses.manage');

        $rawType = $request->input('type');
        $type = in_array($rawType, ['central', 'main', 'pusat'], true) ? 'central' : 'project';

        $request->merge([
            'type' => $type,
            'address' => $request->input('address') ?? $request->input('location'),
            'code' => $request->filled('code') ? $request->input('code') : $warehouse->code,
        ]);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => "required|string|max:30|unique:warehouses,code,{$warehouse->id}",
            'type' => 'required|in:central,project',
            'project_id' => 'nullable|exists:projects,id',
            'project_name' => [
                Rule::requiredIf(fn () => $type === 'project'
                    && ! $request->filled('project_id')
                    && ! $warehouse->project_id),
                'nullable',
                'string',
                'max:255',
            ],
            'address' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
        ]);

        $projectId = $validated['project_id'] ?? $warehouse->project_id;

        if ($type === 'project' && $request->filled('project_name')) {
            $project = Project::firstOrCreate(
                ['name' => trim((string) $request->input('project_name'))],
                [
                    'code' => 'PRJ-'.strtoupper(Str::random(5)),
                    'location' => $validated['address'] ?? null,
                    'status' => 'active',
                    'start_date' => date('Y-m-d'),
                ]
            );
            $projectId = $project->id;
        }

        $warehouse->update([
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
            'type' => $type,
            'is_central' => $type === 'central',
            'is_active' => $request->has('is_active')
                ? $request->boolean('is_active')
                : $warehouse->isAdministrativelyActive(),
            'project_id' => $type === 'central' ? null : $projectId,
            'address' => $validated['address'] ?? null,
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
