<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index()
    {
        $this->authorizeProjectAccess();
        $projects = Project::withCount('warehouses')->latest()->paginate(15);

        return view('projects.index', compact('projects'));
    }

    public function create()
    {
        $this->authorizeProjectAccess();
        return view('projects.create');
    }

    public function store(Request $request)
    {
        $this->authorizeProjectAccess();

        if (!$request->filled('code')) {
            $request->merge(['code' => 'PRJ-' . strtoupper(\Illuminate\Support\Str::random(5))]);
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
            'name'       => 'required|string|max:255',
            'code'       => 'required|string|max:30|unique:projects,code',
            'location'   => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
            'status'     => 'required|in:planning,active,ongoing,completed,on_hold,suspended',
        ]);

        $project = Project::create([
            'name'       => $validated['name'],
            'code'       => strtoupper($validated['code']),
            'location'   => $validated['location'] ?? null,
            'start_date' => $validated['start_date'] ?? null,
            'end_date'   => $validated['end_date'] ?? null,
            'status'     => $validated['status'],
        ]);

        return redirect()->route('projects.index')
            ->with('success', "Proyek '{$project->name}' berhasil ditambahkan.");
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
        if (!$request->filled('code')) {
            $request->merge(['code' => $project->code]);
        }

        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'code'       => "required|string|max:30|unique:projects,code,{$project->id}",
            'location'   => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
            'status'     => 'required|in:planning,active,ongoing,completed,on_hold,suspended',
        ]);

        $project->update([
            'name'       => $validated['name'],
            'code'       => strtoupper($validated['code']),
            'location'   => $validated['location'] ?? null,
            'start_date' => $validated['start_date'] ?? null,
            'end_date'   => $validated['end_date'] ?? null,
            'status'     => $validated['status'],
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
        if (!$user || (!$user->can('projects.manage') && !$user->can('view users') && !$user->hasAnyRole(['Owner', 'Admin Pusat', 'Admin']))) {
            abort(403, 'Akses terbatas untuk Administrator Proyek.');
        }
    }
}
