<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view users');

        $query = User::with('roles');

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        if ($request->role) {
            $query->role($request->role);
        }

        $users = $query->latest()->paginate(15)->withQueryString();
        $roles = Role::all();

        return view('users.index', compact('users', 'roles'));
    }

    public function create()
    {
        $this->authorize('create users');
        $roles      = Role::all();
        $warehouses = Warehouse::orderBy('name')->get();

        return view('users.create', compact('roles', 'warehouses'));
    }

    public function store(Request $request)
    {
        $this->authorize('create users');

        // Normalize roles input from array or single string
        if ($request->has('roles') && !$request->has('role')) {
            $roles = (array) $request->input('roles');
            $request->merge(['role' => $roles[0] ?? null]);
        }

        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'email'           => 'required|email|unique:users,email',
            'password'        => 'required|min:8|confirmed',
            'role'            => 'nullable|exists:roles,name',
            'roles'           => 'nullable|array',
            'roles.*'         => 'exists:roles,name',
            'is_active'       => 'nullable|boolean',
            'warehouse_ids'   => 'nullable|array',
            'warehouse_ids.*' => 'exists:warehouses,id',
        ]);

        $user = User::create([
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'password'  => Hash::make($validated['password']),
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : true,
        ]);

        $rolesToAssign = $request->input('roles', []);
        if (empty($rolesToAssign) && !empty($validated['role'])) {
            $rolesToAssign = [$validated['role']];
        }
        if (!empty($rolesToAssign)) {
            $user->syncRoles($rolesToAssign);
        }

        if (!empty($validated['warehouse_ids'])) {
            $user->warehouses()->sync($validated['warehouse_ids']);
        }

        return redirect()->route('users.index')
            ->with('success', "Pengguna '{$user->name}' berhasil ditambahkan.");
    }

    public function show(User $user)
    {
        $this->authorize('view users');
        $user->load(['roles', 'warehouses']);

        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $this->authorize('edit users');
        $roles      = Role::all();
        $warehouses = Warehouse::orderBy('name')->get();

        return view('users.edit', compact('user', 'roles', 'warehouses'));
    }

    public function update(Request $request, User $user)
    {
        $this->authorize('edit users');

        // Normalize roles input from array or single string
        if ($request->has('roles') && !$request->has('role')) {
            $roles = (array) $request->input('roles');
            $request->merge(['role' => $roles[0] ?? null]);
        }

        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'email'           => "required|email|unique:users,email,{$user->id}",
            'password'        => 'nullable|min:8|confirmed',
            'role'            => 'nullable|exists:roles,name',
            'roles'           => 'nullable|array',
            'roles.*'         => 'exists:roles,name',
            'is_active'       => 'nullable|boolean',
            'warehouse_ids'   => 'nullable|array',
            'warehouse_ids.*' => 'exists:warehouses,id',
        ]);

        $updateData = [
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : $user->is_active,
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        $rolesToAssign = $request->input('roles', []);
        if (empty($rolesToAssign) && !empty($validated['role'])) {
            $rolesToAssign = [$validated['role']];
        }
        if (!empty($rolesToAssign)) {
            $user->syncRoles($rolesToAssign);
        }

        $user->warehouses()->sync($request->input('warehouse_ids', []));

        return redirect()->route('users.index')
            ->with('success', "Pengguna '{$user->name}' berhasil diperbarui.");
    }

    public function destroy(User $user)
    {
        $this->authorize('delete users');

        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak dapat menghapus akun sendiri.');
        }

        $name = $user->name;
        $user->delete();

        return redirect()->route('users.index')
            ->with('success', "Pengguna '{$name}' berhasil dihapus.");
    }
}
