<?php

namespace App\Http\Controllers;

use App\Models\Tool;
use App\Models\Category;
use Illuminate\Http\Request;

class ToolController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view tools');

        $query = Tool::with(['category']);

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('code', 'like', "%{$request->search}%")
                  ->orWhere('serial_number', 'like', "%{$request->search}%");
            });
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        $tools      = $query->latest()->paginate(15)->withQueryString();
        $categories = Category::orderBy('name')->get();

        return view('tools.index', compact('tools', 'categories'));
    }

    public function create()
    {
        $this->authorize('create tools');
        $categories = Category::orderBy('name')->get();

        return view('tools.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $this->authorize('create tools');

        $validated = $request->validate([
            'code'          => 'required|string|max:50|unique:tools,code',
            'name'          => 'required|string|max:255',
            'type'          => 'nullable|string|max:255',
            'category_id'   => 'nullable|exists:categories,id',
            'serial_number' => 'nullable|string|max:100',
            'brand'         => 'nullable|string|max:100',
            'condition'     => 'nullable|string',
            'description'   => 'nullable|string',
        ]);

        $validated['status'] = 'available';
        Tool::create($validated);

        return redirect()->route('tools.index')
            ->with('success', "Alat '{$validated['name']}' berhasil ditambahkan.");
    }

    public function show(Tool $tool)
    {
        $this->authorize('view tools');
        $tool->load([
            'category',
            'toolAssignments.assignee',
            'toolAssignments.warehouse',
            'toolInspections',
            'maintenances',
        ]);

        return view('tools.show', compact('tool'));
    }

    public function edit(Tool $tool)
    {
        $this->authorize('edit tools');
        $categories = Category::orderBy('name')->get();

        return view('tools.edit', compact('tool', 'categories'));
    }

    public function update(Request $request, Tool $tool)
    {
        $this->authorize('edit tools');

        $validated = $request->validate([
            'code'          => "required|string|max:50|unique:tools,code,{$tool->id}",
            'name'          => 'required|string|max:255',
            'type'          => 'nullable|string|max:255',
            'category_id'   => 'nullable|exists:categories,id',
            'serial_number' => 'nullable|string|max:100',
            'brand'         => 'nullable|string|max:100',
            'condition'     => 'nullable|string',
            'description'   => 'nullable|string',
        ]);

        $tool->update($validated);

        return redirect()->route('tools.index')
            ->with('success', "Alat '{$tool->name}' berhasil diperbarui.");
    }

    public function destroy(Tool $tool)
    {
        $this->authorize('delete tools');

        if ($tool->status === 'in_use') {
            return back()->with('error', 'Tidak dapat menghapus alat yang sedang dipinjam.');
        }

        $name = $tool->name;
        $tool->delete();

        return redirect()->route('tools.index')
            ->with('success', "Alat '{$name}' berhasil dihapus.");
    }
}
