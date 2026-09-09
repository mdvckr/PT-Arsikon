<?php

namespace App\Http\Controllers;

use App\Models\Tool;
use App\Models\Category;
use App\Models\ToolAssignment;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class ToolController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view tools');

        // Kategori beserta alat di dalamnya (untuk tabel yang dikelompokkan per kategori)
        $categories = Category::query()
            ->where('type', 'tool')
            ->with(['tools' => function ($q) use ($request) {
                $q->orderBy('name');

                if ($request->search) {
                    $q->where(function ($qq) use ($request) {
                        $qq->where('name', 'like', "%{$request->search}%")
                           ->orWhere('code', 'like', "%{$request->search}%");
                    });
                }

                if ($request->category_id) {
                    $q->where('category_id', $request->category_id);
                }
            }])
            ->orderBy('name')
            ->get()
            ->filter(function ($cat) {
                return $cat->tools->isNotEmpty();
            })
            ->values();

        // Kategori untuk dropdown filter
        $filterCategories = Category::query()->where('type', 'tool')->orderBy('name')->get();

        return view('tools.index', compact('categories', 'filterCategories'));
    }

    public function create(Request $request)
    {
        $this->authorize('create tools');
        $categories = Category::query()->where('type', 'tool')->orderBy('name')->get();

        // Kategori yang dipilih lewat query (mis. klik "Tambah Alat" pada baris kategori)
        $selectedCategoryId = $request->query('category_id');
        if ($selectedCategoryId && $categories->contains('id', (int) $selectedCategoryId)) {
            $selectedCategoryId = (int) $selectedCategoryId;
        } else {
            $selectedCategoryId = null;
        }

        return view('tools.create', compact('categories', 'selectedCategoryId'));
    }

    public function store(Request $request)
    {
        $this->authorize('create tools');

        $validated = $request->validate([
            'code'          => 'required|string|max:50|unique:tools,code',
            'name'          => 'required|string|max:255',
            'category_id'   => 'nullable|integer|exists:categories,id',
            'new_category'  => 'nullable|string|max:255',
            'brand'         => 'nullable|string|max:100',
            'stock_total'   => 'required|integer|min:0',
        ]);

        $category = $this->resolveCategory($request);

        $tool = Tool::create([
            'code'           => strtoupper(trim($validated['code'])),
            'name'           => $validated['name'],
            'category_id'    => $category?->id,
            'type'           => $category?->name,
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
        $categories = Category::query()->where('type', 'tool')->orderBy('name')->get();

        return view('tools.edit', compact('tool', 'categories'));
    }

    public function update(Request $request, Tool $tool)
    {
        $this->authorize('edit tools');

        $validated = $request->validate([
            'code'          => "required|string|max:50|unique:tools,code,{$tool->id}",
            'name'          => 'required|string|max:255',
            'category_id'   => 'nullable|integer|exists:categories,id',
            'new_category'  => 'nullable|string|max:255',
            'brand'         => 'nullable|string|max:100',
            'stock_total'   => 'required|integer|min:0',
            'stock_available' => 'required|integer|min:0',
            'stock_maintenance' => 'nullable|integer|min:0',
            'stock_damaged' => 'nullable|integer|min:0',
        ]);

        $category = $this->resolveCategory($request);

        $tool->update([
            'code'             => strtoupper(trim($validated['code'])),
            'name'             => $validated['name'],
            'category_id'      => $category?->id,
            'type'             => $category?->name,
            'brand'            => $validated['brand'] ?? null,
            'stock_total'      => $validated['stock_total'],
            'stock_available'  => $validated['stock_available'],
            'stock_maintenance'=> $validated['stock_maintenance'] ?? 0,
            'stock_damaged'    => $validated['stock_damaged'] ?? 0,
        ]);

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

    /**
     * Selesaikan kategori terpilih. Jika user mengisi kategori baru (new_category),
     * otomatis buat kategori tool baru dan kembalikan instance-nya.
     */
    protected function resolveCategory(Request $request): ?Category
    {
        if ($request->filled('new_category')) {
            $name = trim($request->new_category);

            $category = Category::where('type', 'tool')->where('name', $name)->first();

            if (! $category) {
                $code = 'TOOL-' . strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $name), 0, 8));
                $base  = $code;
                $i     = 1;
                while (Category::where('code', $code)->exists()) {
                    $code = $base . '-' . $i++;
                }

                $category = Category::create([
                    'code' => $code,
                    'name' => $name,
                    'type' => 'tool',
                ]);
            }

            return $category;
        }

        if ($request->filled('category_id')) {
            return Category::find($request->category_id);
        }

        return null;
    }
}
