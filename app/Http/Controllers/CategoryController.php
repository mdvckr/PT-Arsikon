<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Unit;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view categories');

        $categories = Category::withCount(['materials', 'tools'])->latest()->paginate(20);
        $units      = Unit::withCount('materials')->latest()->paginate(20);

        return view('categories.index', compact('categories', 'units'));
    }

    // ---- CATEGORY CRUD ----
    public function store(Request $request)
    {
        $this->authorize('create categories');

        $request->validate(['name' => 'required|string|max:100|unique:categories,name']);
        Category::create($request->only('name', 'description'));

        return back()->with('success', "Kategori '{$request->name}' ditambahkan.");
    }

    public function updateCategory(Request $request, Category $category)
    {
        $this->authorize('edit categories');
        $request->validate(['name' => "required|string|max:100|unique:categories,name,{$category->id}"]);
        $category->update($request->only('name', 'description'));

        return back()->with('success', 'Kategori diperbarui.');
    }

    public function destroyCategory(Category $category)
    {
        $this->authorize('delete categories');
        if ($category->materials()->exists() || $category->tools()->exists()) {
            return back()->with('error', 'Kategori masih digunakan oleh material atau alat.');
        }
        $category->delete();
        return back()->with('success', 'Kategori dihapus.');
    }

    // ---- UNIT CRUD ----
    public function storeUnit(Request $request)
    {
        $this->authorize('create categories');
        $request->validate(['name' => 'required|string|max:50|unique:units,name']);
        Unit::create($request->only('name', 'abbreviation'));

        return back()->with('success', "Satuan '{$request->name}' ditambahkan.");
    }

    public function updateUnit(Request $request, Unit $unit)
    {
        $this->authorize('edit categories');
        $request->validate(['name' => "required|string|max:50|unique:units,name,{$unit->id}"]);
        $unit->update($request->only('name', 'abbreviation'));

        return back()->with('success', 'Satuan diperbarui.');
    }

    public function destroyUnit(Unit $unit)
    {
        $this->authorize('delete categories');
        if ($unit->materials()->exists()) {
            return back()->with('error', 'Satuan masih digunakan oleh material.');
        }
        $unit->delete();
        return back()->with('success', 'Satuan dihapus.');
    }
}
