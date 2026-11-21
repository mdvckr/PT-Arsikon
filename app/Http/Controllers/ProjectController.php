<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeProjectAccess();

        $query = Project::withCount('warehouses');

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $status = $request->input('status');
        if (in_array($status, ['planning', 'active', 'completed', 'on_hold'], true)) {
            $statusValues = match ($status) {
                'active' => ['active', 'ongoing'],
                'on_hold' => ['on_hold', 'suspended'],
                default => [$status],
            };

            $query->whereIn('status', $statusValues);
        }

        $projects = $query->latest()->paginate(15)->withQueryString();

        $stats = [
            'total' => Project::count(),
            'active' => Project::whereIn('status', ['active', 'ongoing'])->count(),
            'planning' => Project::where('status', 'planning')->count(),
            'completed' => Project::where('status', 'completed')->count(),
        ];

        $warehouseCount = Warehouse::count();

        return view('projects.index', compact('projects', 'stats', 'warehouseCount'));
    }

    public function create()
    {
        $this->authorizeProjectAccess();

        return view('projects.create');
    }

    public function store(Request $request)
    {
        $this->authorizeProjectAccess();

        if (! $request->filled('code')) {
            $request->merge(['code' => 'PRJ-'.strtoupper(Str::random(5))]);
        }

        $rawStatus = $request->input('status');
        if (in_array($rawStatus, ['active', 'ongoing', 'berjalan'])) {
            $status = 'active';
        } elseif (in_array($rawStatus, ['planning', 'completed', 'on_hold', 'suspended'])) {
            $status = $rawStatus === 'suspended' ? 'on_hold' : $rawStatus;
        } else {
            $status = 'active';
        }
        $request->merge(['status' => $status]);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:30|unique:projects,code',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:2000',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|in:planning,active,completed,on_hold',
        ]);

        $autoCreateWh = $request->boolean('create_warehouse') || $request->boolean('auto_create_warehouse');
        if ($autoCreateWh) {
            $request->validate([
                'warehouse_code' => 'nullable|string|max:30|unique:warehouses,code',
            ]);
        }

        $project = DB::transaction(function () use ($validated, $request, $autoCreateWh) {
            $project = Project::create([
                'name' => $validated['name'],
                'code' => strtoupper($validated['code']),
                'location' => $validated['location'] ?? null,
                'description' => $validated['description'] ?? null,
                'start_date' => $validated['start_date'] ?? null,
                'end_date' => $validated['end_date'] ?? null,
                'status' => $validated['status'],
            ]);

            if ($autoCreateWh) {
                $whName = $request->filled('warehouse_name')
                    ? trim($request->input('warehouse_name'))
                    : 'Gudang Site '.$project->name;

                $whCode = $request->filled('warehouse_code')
                    ? strtoupper(trim($request->input('warehouse_code')))
                    : 'W-'.$project->code;

                if (Warehouse::where('code', $whCode)->exists()) {
                    $whCode = 'W-'.$project->code.'-'.rand(10, 99);
                }

                Warehouse::create([
                    'name' => $whName,
                    'code' => $whCode,
                    'type' => 'project',
                    'is_central' => false,
                    'is_active' => true,
                    'project_id' => $project->id,
                    'address' => $request->input('warehouse_address') ?: ($validated['location'] ?? null),
                ]);
            }

            return $project;
        });

        $msg = $autoCreateWh
            ? "Proyek '{$project->name}' dan Gudang Site terkait berhasil ditambahkan sekaligus."
            : "Proyek '{$project->name}' berhasil ditambahkan.";

        return redirect()->route('projects.index')
            ->with('success', $msg);
    }

    public function edit(Project $project)
    {
        $this->authorizeProjectAccess();

        return view('projects.edit', compact('project'));
    }

    public function update(Request $request, Project $project)
    {
        $this->authorizeProjectAccess();

        $rawStatus = $request->input('status');
        if (in_array($rawStatus, ['active', 'ongoing', 'berjalan'])) {
            $status = 'active';
        } elseif (in_array($rawStatus, ['planning', 'completed', 'on_hold', 'suspended'])) {
            $status = $rawStatus === 'suspended' ? 'on_hold' : $rawStatus;
        } else {
            $status = 'active';
        }
        $request->merge(['status' => $status]);
        if (! $request->filled('code')) {
            $request->merge(['code' => $project->code]);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => "required|string|max:30|unique:projects,code,{$project->id}",
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:2000',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|in:planning,active,completed,on_hold',
        ]);

        $project->update([
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
            'location' => $validated['location'] ?? null,
            'description' => $validated['description'] ?? null,
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'status' => $validated['status'],
        ]);

        return redirect()->route('projects.index')
            ->with('success', "Proyek '{$project->name}' berhasil diperbarui.");
    }

    public function destroy(Project $project)
    {
        $this->authorizeProjectAccess();
        $name = $project->name;
        $project->delete();

        return redirect()->route('projects.index')
            ->with('success', "Proyek '{$name}' berhasil dihapus.");
    }

    protected function authorizeProjectAccess(): void
    {
        $user = auth()->user();
        if (! $user || (! $user->can('projects.manage') && ! $user->can('view users') && ! $user->hasAnyRole(['Owner', 'Admin Pusat']))) {
            abort(403, 'Akses terbatas untuk Administrator Proyek.');
        }
    }
}
