<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index()
    {
        $this->authorize('view users');
        $projects = Project::withCount('warehouses')->latest()->paginate(15);

        return view('projects.index', compact('projects'));
    }

    public function create()
    {
        $this->authorize('view users');
        return view('projects.create');
    }

    public function store(Request $request)
    {
        $this->authorize('view users');

        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'code'       => 'required|string|max:20|unique:projects,code',
            'location'   => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
            'status'     => 'required|in:active,completed,on_hold',
            'description'=> 'nullable|string',
        ]);

        Project::create($validated);

        return redirect()->route('projects.index')
            ->with('success', "Proyek '{$validated['name']}' berhasil ditambahkan.");
    }

    public function edit(Project $project)
    {
        $this->authorize('view users');
        return view('projects.edit', compact('project'));
    }

    public function update(Request $request, Project $project)
    {
        $this->authorize('view users');

        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'code'       => "required|string|max:20|unique:projects,code,{$project->id}",
            'location'   => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
            'status'     => 'required|in:active,completed,on_hold',
            'description'=> 'nullable|string',
        ]);

        $project->update($validated);

        return redirect()->route('projects.index')
            ->with('success', "Proyek '{$project->name}' berhasil diperbarui.");
    }

    public function destroy(Project $project)
    {
        $this->authorize('view users');
        $name = $project->name;
        $project->delete();

        return redirect()->route('projects.index')
            ->with('success', "Proyek '{$name}' berhasil dihapus.");
    }
}
