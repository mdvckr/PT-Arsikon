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

    public function destroyCategory(Request $request, Category $category)
    {
        $user = auth()->user();
        if (!$user->can('delete categories') && !$user->hasAnyRole(['Owner', 'Admin', 'Admin Gudang Pusat'])) {
            abort(403, 'Anda tidak memiliki hak akses untuk menghapus kategori.');
        }

        $materialsCount = $category->materials()->count();
        $toolsCount = $category->tools()->count();
        $totalItems = $materialsCount + $toolsCount;

        if ($totalItems > 0) {
            // Opsi 1: Pindahkan seluruh item ke kategori lain lalu hapus
            if ($request->filled('transfer_to_category_id')) {
                $target = Category::find($request->transfer_to_category_id);
                if (!$target || $target->id === $category->id) {
                    $errorMsg = 'Kategori tujuan pemindahan tidak valid.';
                    if ($request->expectsJson() || $request->ajax()) {
                        return response()->json(['success' => false, 'message' => $errorMsg], 422);
                    }
                    return back()->with('error', $errorMsg);
                }

                $category->materials()->update(['category_id' => $target->id]);
                $category->tools()->update(['category_id' => $target->id]);

                $catName = $category->name;
                $category->delete();

                $msg = "Kategori '{$catName}' berhasil dihapus. {$totalItems} item berhasil dipindahkan ke kategori '{$target->name}'.";
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json(['success' => true, 'message' => $msg]);
                }
                return back()->with('success', $msg);
            }

            // Opsi 2: Hapus kategori beserta isinya jika item tidak memiliki mutasi stok
            if ($request->boolean('force_delete_items')) {
                if (!$user->hasAnyRole(['Owner', 'Admin'])) {
                    $errorMsg = 'Hanya Admin atau Owner yang dapat menghapus kategori beserta isinya.';
                    if ($request->expectsJson() || $request->ajax()) {
                        return response()->json(['success' => false, 'message' => $errorMsg], 403);
                    }
                    return back()->with('error', $errorMsg);
                }

                $hasMutations = false;
                foreach ($category->materials as $mat) {
                    if ($mat->stockMutations()->exists() || $mat->inventories()->where('quantity', '>', 0)->exists()) {
                        $hasMutations = true;
                        break;
                    }
                }

                if ($hasMutations) {
                    $errorMsg = "Kategori '{$category->name}' memiliki material dengan riwayat stok aktif. Silakan pilih kategori tujuan untuk memindahkan item terlebih dahulu.";
                    if ($request->expectsJson() || $request->ajax()) {
                        return response()->json(['success' => false, 'message' => $errorMsg], 422);
                    }
                    return back()->with('error', $errorMsg);
                }

                $category->materials()->delete();
                $category->tools()->delete();

                $catName = $category->name;
                $category->delete();

                $msg = "Kategori '{$catName}' beserta seluruh item di dalamnya berhasil dihapus.";
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json(['success' => true, 'message' => $msg]);
                }
                return back()->with('success', $msg);
            }

            $errorMsg = "Kategori '{$category->name}' masih memiliki {$materialsCount} material dan {$toolsCount} alat. Silakan pilih kategori tujuan untuk memindahkan item sebelum menghapus.";
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMsg,
                    'requires_transfer' => true,
                    'materials_count' => $materialsCount,
                    'tools_count' => $toolsCount,
                ], 422);
            }
            return back()->with('error', $errorMsg);
        }

        $categoryName = $category->name;
        $category->delete();

        $msg = "Kategori '{$categoryName}' berhasil dihapus.";
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => $msg]);
        }
        return back()->with('success', $msg);
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
