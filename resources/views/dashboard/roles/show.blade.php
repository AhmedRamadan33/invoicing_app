@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Role Details: {{ ucfirst($role->name) }}</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        @can('roles_edit')
        <a href="{{ route('roles.edit', $role) }}" class="btn btn-outline-secondary me-2">
            <i class="bi bi-pencil"></i> Edit
        </a>
        @endcan
        <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-body text-center">
                <div class="avatar mb-3">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto" 
                         style="width: 80px; height: 80px; font-size: 2rem;">
                        {{ strtoupper(substr($role->name, 0, 1)) }}
                    </div>
                </div>
                <h4>{{ ucfirst($role->name) }}</h4>
                
                <div class="mb-3">
                    <span class="badge bg-primary">{{ $role->permissions->count() }} permissions</span>
                    <span class="badge bg-info ms-1">{{ $role->users->count() }} users</span>
                </div>
                
                @if($role->description)
                <p class="text-muted">{{ $role->description }}</p>
                @endif
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Role Information</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th>Created:</th>
                        <td>{{ $role->created_at->format('F d, Y') }}</td>
                    </tr>
                    <tr>
                        <th>Last Updated:</th>
                        <td>{{ $role->updated_at->format('F d, Y') }}</td>
                    </tr>
                    <tr>
                        <th>Users:</th>
                        <td>{{ $role->users->count() }}</td>
                    </tr>
                    <tr>
                        <th>Permissions:</th>
                        <td>{{ $role->permissions->count() }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Assigned Permissions</h5>
            </div>
            <div class="card-body">
                @if($role->permissions->count() > 0)
                    <div class="row">
                        @foreach($role->permissions->chunk(ceil($role->permissions->count() / 3)) as $chunk)
                        <div class="col-md-4">
                            @foreach($chunk as $permission)
                            <div class="mb-2">
                                <span class="badge bg-secondary">
                                    {{ $permission->name }}
                                </span>
                                <br>
                                <small class="text-muted">
                                    {{ str_replace('_', ' ', $permission->name) }}
                                </small>
                            </div>
                            @endforeach
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        This role has no permissions assigned.
                    </div>
                @endif
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Users with this Role</h5>
            </div>
            <div class="card-body">
                @if($role->users->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($role->users as $user)
                                <tr>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        @if($user->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-danger">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('users.show', $user) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i>
                        No users are assigned to this role.
                    </div>
                @endif
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2 col-md-8 mx-auto">
                    @can('roles_edit')
                    <a href="{{ route('roles.edit', $role) }}" class="btn btn-primary">
                        <i class="bi bi-pencil"></i> Edit Role
                    </a>
                    @endcan
                    
                    @can('roles_delete')
                    @if($role->users->count() === 0)
                        <form action="{{ route('roles.destroy', $role) }}" method="POST" class="d-grid">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger" 
                                    onclick="return confirm('Are you sure? This will permanently delete this role.')">
                                <i class="bi bi-trash"></i> Delete Role
                            </button>
                        </form>
                    @else
                        <button class="btn btn-outline-secondary" disabled>
                            <i class="bi bi-trash"></i> Cannot Delete (Has Users)
                        </button>
                    @endif
                    @endcan
                    
                    <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-list-ul"></i> View All Roles
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection