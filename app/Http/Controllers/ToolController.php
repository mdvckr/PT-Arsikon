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
                  ->orWhere('code', 'like', "%{$request->search}%");
            });
        }

        if ($request->type) {
            $query->where('type', $request->type);
        }

        // Filter by stock status
        if ($request->stock_status) {
            match($request->stock_status) {
                'available' => $query->where('stock_available', '>', 0),
                'empty'     => $query->where('stock_available', '<=', 0),
                'borrowed'  => $query->where('stock_borrowed', '>', 0),
                'damaged'   => $query->where('stock_damaged', '>', 0),
                default     => null,
            };
        }

        $tools = $query->orderBy('type')->orderBy('name')->latest()->paginate(50)->withQueryString();
        $types = Tool::select('type')->whereNotNull('type')->where('type', '!=', '')->distinct()->pluck('type');

        return view('tools.index', compact('tools', 'types'));
    }

    public function create()
    {
        $this->authorize('create tools');
        $types = Tool::select('type')->whereNotNull('type')->where('type', '!=', '')->distinct()->pluck('type');

        return view('tools.create', compact('types'));
    }

    public function store(Request $request)
    {
        $this->authorize('create tools');

        $validated = $request->validate([
            'code'          => 'required|string|max:50|unique:tools,code',
            'name'          => 'required|string|max:255',
            'type'          => 'nullable|string|max:255',
            'brand'         => 'nullable|string|max:100',
            'stock_total'   => 'required|integer|min:0',
        ]);

        $tool = Tool::create([
            'code'           => strtoupper(trim($validated['code'])),
            'name'           => $validated['name'],
            'type'           => $validated['type'] ?? null,
            'brand'          => $validated['brand'] ?? null,
            'stock_total'    => $validated['stock_total'],
            'stock_available'=> $validated['stock_total'],
            'stock_borrowed' => 0,
            'stock_maintenance' => 0,
            'stock_damaged'  => 0,
        ]);

        return redirect()->route('tools.index')
            ->with('success', "Alat '{$tool->name}' berhasil ditambahkan dengan stok {$tool->stock_total} unit.");
    }

    public function addStock(Request $request, Tool $tool)
    {
        $this->authorize('edit tools');

        $request->validate(['quantity' => 'required|integer|min:1|max:500']);

        $qty = (int) $request->quantity;
        $tool->addStock($qty);

        return redirect()->route('tools.edit', $tool)
            ->with('success', "Stok '{$tool->name}' berhasil ditambah {$qty} unit. Total stok sekarang: {$tool->fresh()->stock_total} unit.");
    }

    public function show(Tool $tool)
    {
        $this->authorize('view tools');
        $tool->load(['category']);

        return view('tools.show', compact('tool'));
    }

    public function edit(Tool $tool)
    {
        $this->authorize('edit tools');
        $types = Tool::select('type')->whereNotNull('type')->where('type', '!=', '')->distinct()->pluck('type');

        return view('tools.edit', compact('tool', 'types'));
    }

    public function update(Request $request, Tool $tool)
    {
        $this->authorize('edit tools');

        $validated = $request->validate([
            'code'          => "required|string|max:50|unique:tools,code,{$tool->id}",
            'name'          => 'required|string|max:255',
            'type'          => 'nullable|string|max:255',
            'brand'         => 'nullable|string|max:100',
            'stock_total'   => 'required|integer|min:0',
            'stock_available' => 'required|integer|min:0',
            'stock_maintenance' => 'nullable|integer|min:0',
            'stock_damaged' => 'nullable|integer|min:0',
        ]);

        $tool->update($validated);

        return redirect()->route('tools.index')
            ->with('success', "Alat '{$tool->name}' berhasil diperbarui.");
    }

    public function destroy(Tool $tool)
    {
        $this->authorize('delete tools');

        if ($tool->stock_borrowed > 0) {
            return back()->with('error', 'Tidak dapat menghapus alat yang sedang dipinjam.');
        }

        $name = $tool->name;
        $tool->delete();

        return redirect()->route('tools.index')
            ->with('success', "Alat '{$name}' berhasil dihapus.");
    }
}
