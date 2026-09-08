<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view suppliers');

        $query = Supplier::query();

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('phone', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        $suppliers = $query->withCount('goodsReceipts')->latest()->paginate(15)->withQueryString();

        return view('suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        $this->authorize('create suppliers');
        return view('suppliers.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create suppliers');

        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'phone'   => 'nullable|string|max:30',
            'email'   => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'contact_person' => 'nullable|string|max:150',
        ]);

        Supplier::create($validated);

        return redirect()->route('suppliers.index')
            ->with('success', "Supplier '{$validated['name']}' berhasil ditambahkan.");
    }

    public function show(Supplier $supplier)
    {
        $this->authorize('view suppliers');
        $supplier->load(['goodsReceipts' => fn($q) => $q->latest()->limit(10)]);

        return view('suppliers.show', compact('supplier'));
    }

    public function edit(Supplier $supplier)
    {
        $this->authorize('edit suppliers');
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $this->authorize('edit suppliers');

        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'phone'   => 'nullable|string|max:30',
            'email'   => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'contact_person' => 'nullable|string|max:150',
        ]);

        $supplier->update($validated);

        return redirect()->route('suppliers.index')
            ->with('success', "Supplier '{$supplier->name}' berhasil diperbarui.");
    }

    public function destroy(Supplier $supplier)
    {
        $this->authorize('delete suppliers');
        $name = $supplier->name;
        $supplier->delete();

        return redirect()->route('suppliers.index')
            ->with('success', "Supplier '{$name}' berhasil dihapus.");
    }
}
