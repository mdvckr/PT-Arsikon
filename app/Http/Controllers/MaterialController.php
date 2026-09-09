<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\Category;
use App\Models\Unit;
use Illuminate\Http\Request;

class MaterialController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view materials');

        $query = Material::with(['category', 'unit', 'inventories']);

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('sku', 'like', "%{$request->search}%");
            });
        }

        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        $materials  = $query->latest()->paginate(15)->withQueryString();
        $categories = Category::query()->where('type', 'material')->orderBy('name')->get();

        return view('materials.index', compact('materials', 'categories'));
    }

    public function create()
    {
        $this->authorize('create materials');
        $categories = Category::query()->where('type', 'material')->orderBy('name')->get();
        $units      = Unit::orderBy('name')->get();

        return view('materials.create', compact('categories', 'units'));
    }

    public function store(Request $request)
    {
        $this->authorize('create materials');

        $validated = $request->validate([
            'sku'         => 'required|string|max:50|unique:materials,sku',
            'name'        => 'required|string|max:255',
            'type'        => 'nullable|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'new_category'=> 'nullable|string|max:255',
            'unit_id'     => 'required|exists:units,id',
            'description' => 'nullable|string',
        ]);

        $category = $this->resolveCategory($request);
        $validated['category_id'] = $category?->id;

        Material::create($validated);

        return redirect()->route('materials.index')
            ->with('success', "Material '{$validated['name']}' berhasil ditambahkan.");
    }

    public function show(Material $material)
    {
        $this->authorize('view materials');
        $material->load(['category', 'unit', 'inventories.warehouse', 'stockMutations' => fn($q) => $q->latest()->limit(20)]);

        return view('materials.show', compact('material'));
    }

    public function edit(Material $material)
    {
        $this->authorize('edit materials');
        $categories = Category::query()->where('type', 'material')->orderBy('name')->get();
        $units      = Unit::orderBy('name')->get();

        return view('materials.edit', compact('material', 'categories', 'units'));
    }

    public function update(Request $request, Material $material)
    {
        $this->authorize('edit materials');

        $validated = $request->validate([
            'sku'         => "required|string|max:50|unique:materials,sku,{$material->id}",
            'name'        => 'required|string|max:255',
            'type'        => 'nullable|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'new_category'=> 'nullable|string|max:255',
            'unit_id'     => 'required|exists:units,id',
            'description' => 'nullable|string',
        ]);

        $category = $this->resolveCategory($request);
        $validated['category_id'] = $category?->id;

        $material->update($validated);

        return redirect()->route('materials.index')
            ->with('success', "Material '{$material->name}' berhasil diperbarui.");
    }

    public function destroy(Material $material)
    {
        $this->authorize('delete materials');

        if ($material->inventories()->where('quantity', '>', 0)->exists()) {
            return back()->with('error', 'Tidak dapat menghapus material yang masih memiliki stok aktif.');
        }

        $name = $material->name;
        $material->delete();

        return redirect()->route('materials.index')
            ->with('success', "Material '{$name}' berhasil dihapus.");
    }

    /**
     * Selesaikan kategori terpilih. Jika user mengisi kategori baru (new_category),
     * otomatis buat kategori material baru dan kembalikan instance-nya.
     */
    protected function resolveCategory(Request $request): ?Category
    {
        if ($request->filled('new_category')) {
            $name = trim($request->new_category);

            $category = Category::where('type', 'material')->where('name', $name)->first();

            if (! $category) {
                $code = 'MAT-' . strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $name), 0, 8));
                $base  = $code;
                $i     = 1;
                while (Category::where('code', $code)->exists()) {
                    $code = $base . '-' . $i++;
                }

                $category = Category::create([
                    'code' => $code,
                    'name' => $name,
                    'type' => 'material',
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
