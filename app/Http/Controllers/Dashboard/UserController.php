<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    /**
     * Display a listing of users 
     */
    public function index()
    {
        $this->authorize('viewAny', User::class);

        $users = User::with('roles')
            ->where('id', '!=', auth()->id()) 
            ->latest()
            ->paginate(10);

        return view('dashboard.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user 
     */
    public function create()
    {
        $this->authorize('create', User::class);

        $roles = Role::where('name', '!=', 'admin')
            ->orderBy('name')
            ->get();

        return view('dashboard.users.create', compact('roles'));
    }

    /**
     * Store a newly created user 
     */
    public function store(Request $request)
    {
        $this->authorize('create', User::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'roles' => 'required|array|min:1',
            'roles.*' => 'exists:roles,name',
        ]);

        if (in_array('admin', $validated['roles'])) {
            return back()
                ->withInput()
                ->with('error', 'Cannot assign admin role. Only existing admins can promote users to admin.');
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $user->syncRoles($validated['roles']);

        Log::info('User created by admin', [
            'created_user_id' => $user->id,
            'created_by' => auth()->id(),
            'roles_assigned' => $validated['roles'],
        ]);

        return redirect()->route('users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Display the specified user 
     */
    public function show(User $user)
    {
        $this->authorize('view', $user);

        $user->load('roles.permissions', 'clients', 'invoices');

        return view('dashboard.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified user 
     */
    public function edit(User $user)
    {
        $this->authorize('update', $user);

        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                ->with('error', 'You cannot edit your own profile from here. Use profile settings.');
        }

        $roles = Role::where('name', '!=', 'admin')
            ->orderBy('name')
            ->get();

        $user->load('roles');

        return view('dashboard.users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified user 
     */
    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);

        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                ->with('error', 'You cannot edit your own profile from here. Use profile settings.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'password' => 'nullable|string|min:8|confirmed',
            'roles' => 'required|array|min:1',
            'roles.*' => 'exists:roles,name',
        ]);

        if (in_array('admin', $validated['roles'])) {
            return back()
                ->withInput()
                ->with('error', 'Cannot assign admin role. Only existing admins can promote users to admin.');
        }

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        $user->syncRoles($validated['roles']);

        Log::info('User updated by admin', [
            'updated_user_id' => $user->id,
            'updated_by' => auth()->id(),
            'new_roles' => $validated['roles'],
        ]);

        return redirect()->route('users.show', $user)
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified user 
     */
    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                ->with('error', 'You cannot delete your own account.');
        }

        if ($user->hasRole('admin')) {
            $adminCount = User::role('admin')->count();
            if ($adminCount <= 1) {
                return redirect()->route('users.index')
                    ->with('error', 'Cannot delete the only admin user. At least one admin must remain.');
            }
        }

        if ($user->clients()->exists() || $user->invoices()->exists()) {
            return redirect()->route('users.index')
                ->with('error', 'Cannot delete user with associated data. Please reassign data first.');
        }

        $user->delete();

        Log::info('User deleted by admin', [
            'deleted_user_id' => $user->id,
            'deleted_by' => auth()->id(),
        ]);

        return redirect()->route('users.index')
            ->with('success', 'User deleted successfully.');
    }

    /**
     * Toggle user active status 
     */
    public function toggleStatus(User $user)
    {
        $this->authorize('update', $user);

        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot deactivate your own account.'
            ], 403);
        }

        $user->update([
            'is_active' => !$user->is_active,
        ]);

        $status = $user->is_active ? 'activated' : 'deactivated';

        Log::info('User status toggled', [
            'user_id' => $user->id,
            'status' => $status,
            'changed_by' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => "User {$status} successfully.",
            'is_active' => $user->is_active,
        ]);
    }

    /**
     * Promote user to admin 
     */
    public function promoteToAdmin(User $user)
    {
        $this->authorize('update', $user);

        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                ->with('error', 'You are already an admin.');
        }

        $user->assignRole('admin');

        Log::info('User promoted to admin', [
            'promoted_user_id' => $user->id,
            'promoted_by' => auth()->id(),
        ]);

        return redirect()->route('users.show', $user)
            ->with('success', 'User promoted to admin successfully.');
    }
}