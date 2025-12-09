<?php

namespace App\Http\Controllers\Dashboard;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class RoleController extends Controller
{
    /**
     * Display a listing of roles 
     */
    public function index()
    {
        $this->authorize('viewAny', Role::class);

        $roles = Role::where('name', '!=', 'admin')
            ->with('permissions')
            ->withCount('users')
            ->orderBy('name')
            ->paginate(10);

        return view('dashboard.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new role 
     */
    public function create()
    {
        $this->authorize('create', Role::class);

        $permissions = Permission::all()->groupBy(function ($permission) {
            $parts = explode('_', $permission->name);
            return $parts[0];
        });

        return view('dashboard.roles.create', compact('permissions'));
    }

    /**
     * Store a newly created role 
     */
    public function store(Request $request)
    {
        $this->authorize('create', Role::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'description' => 'nullable|string|max:500',
            'permissions' => 'required|array|min:1',
            'permissions.*' => 'exists:permissions,name',
        ]);

        if (strtolower($validated['name']) === 'admin') {
            return back()
                ->withInput()
                ->with('error', 'Cannot create role with name "admin". This is a reserved role.');
        }

        $role = Role::create([
            'name' => $validated['name'],
        ]);

        $role->syncPermissions($validated['permissions']);

        Log::info('Role created', [
            'role_id' => $role->id,
            'role_name' => $role->name,
            'created_by' => auth()->id(),
            'permissions_count' => count($validated['permissions']),
        ]);

        return redirect()->route('roles.index')
            ->with('success', 'Role created successfully.');
    }

    /**
     * Display the specified role 
     */
    public function show(Role $role)
    {
        $this->authorize('view', $role);

        if ($role->name === 'admin') {
            abort(403, 'Access to admin role is restricted.');
        }

        $role->load('permissions', 'users');

        return view('dashboard.roles.show', compact('role'));
    }

    /**
     * Show the form for editing the specified role 
     */
    public function edit(Role $role)
    {
        $this->authorize('update', $role);

        if ($role->name === 'admin') {
            abort(403, 'Cannot edit admin role.');
        }

        $permissions = Permission::all()->groupBy(function ($permission) {
            $parts = explode('_', $permission->name);
            return $parts[0];
        });

        $role->load('permissions');

        return view('dashboard.roles.edit', compact('role', 'permissions'));
    }

    /**
     * Update the specified role 
     */
    public function update(Request $request, Role $role)
    {
        $this->authorize('update', $role);

        if ($role->name === 'admin') {
            abort(403, 'Cannot update admin role.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'description' => 'nullable|string|max:500',
            'permissions' => 'required|array|min:1',
            'permissions.*' => 'exists:permissions,name',
        ]);

        if (strtolower($validated['name']) === 'admin') {
            return back()
                ->withInput()
                ->with('error', 'Cannot rename role to "admin". This is a reserved role name.');
        }

        $oldPermissions = $role->permissions->pluck('name')->toArray();

        $role->update([
            'name' => $validated['name'],
        ]);

        $role->syncPermissions($validated['permissions']);

        Log::info('Role updated', [
            'role_id' => $role->id,
            'role_name' => $role->name,
            'updated_by' => auth()->id(),
            'old_permissions' => $oldPermissions,
            'new_permissions' => $validated['permissions'],
        ]);

        return redirect()->route('roles.show', $role)
            ->with('success', 'Role updated successfully.');
    }

    /**
     * Remove the specified role 
     */
    public function destroy(Role $role)
    {
        $this->authorize('delete', $role);

        if ($role->name === 'admin') {
            abort(403, 'Cannot delete admin role.');
        }

        if ($role->users()->count() > 0) {
            return redirect()->route('roles.index')
                ->with('error', 'Cannot delete role with assigned users. Please reassign users first.');
        }

        $roleName = $role->name;
        $role->delete();

        Log::info('Role deleted', [
            'role_name' => $roleName,
            'deleted_by' => auth()->id(),
        ]);

        return redirect()->route('roles.index')
            ->with('success', 'Role deleted successfully.');
    }

    /**
     * Get role statistics
     */

    public function statistics()
    {
        $this->authorize('viewAny', Role::class);

        $stats = [
            'total_roles' => Role::where('name', '!=', 'admin')->count(),
            'total_permissions' => Permission::count(),
            'roles_with_users' => Role::where('name', '!=', 'admin')
                ->has('users')
                ->count(),
        ];

        // توزيع المستخدمين حسب الأدوار
        $roleDistribution = Role::where('name', '!=', 'admin')
            ->withCount('users')
            ->orderByDesc('users_count')
            ->get()
            ->map(function ($role) {
                return [
                    'name' => $role->name,
                    'users_count' => $role->users_count,
                ];
            });

        return response()->json([
            'stats' => $stats,
            'role_distribution' => $roleDistribution,
        ]);
    }
}
