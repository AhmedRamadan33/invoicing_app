@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Edit Role: {{ ucfirst($role->name) }}</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('roles.show', $role) }}" class="btn btn-outline-secondary me-2">
            <i class="bi bi-eye"></i> View
        </a>
        <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('roles.update', $role) }}">
    @csrf
    @method('PUT')
    
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Role Information</h5>
                    <span class="badge bg-primary">{{ $role->users->count() }} users</span>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Role Name *</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                               value="{{ old('name', $role->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror" 
                                  rows="3">{{ old('description', $role->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Role Permissions</h5>
                    <small class="text-muted">Current: {{ $role->permissions->count() }} permissions</small>
                </div>
                <div class="card-body">
                    @if($permissions->count() > 0)
                        @foreach($permissions as $category => $categoryPermissions)
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">
                                    <i class="bi bi-folder"></i>
                                    {{ ucfirst($category) }} Permissions
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    @foreach($categoryPermissions as $permission)
                                    <div class="col-md-6 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" 
                                                   name="permissions[]" value="{{ $permission->name }}" 
                                                   id="perm_{{ $permission->id }}"
                                                   {{ in_array($permission->name, old('permissions', $role->permissions->pluck('name')->toArray())) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="perm_{{ $permission->id }}">
                                                <code>{{ $permission->name }}</code>
                                                <br>
                                                <small class="text-muted">
                                                    {{ str_replace('_', ' ', $permission->name) }}
                                                </small>
                                            </label>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i>
                            No permissions available.
                        </div>
                    @endif
                    @error('permissions')
                        <div class="text-danger mt-2">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Role Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Created</label>
                        <div>{{ $role->created_at->format('F d, Y') }}</div>
                        <small class="text-muted">{{ $role->created_at->diffForHumans() }}</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Last Updated</label>
                        <div>{{ $role->updated_at->format('F d, Y') }}</div>
                        <small class="text-muted">{{ $role->updated_at->diffForHumans() }}</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Assigned Users</label>
                        <div class="display-6">{{ $role->users->count() }}</div>
                        <small class="text-muted">
                            @if($role->users->count() > 0)
                                <a href="#" data-bs-toggle="modal" data-bs-target="#usersModal">
                                    View Users
                                </a>
                            @else
                                No users assigned
                            @endif
                        </small>
                    </div>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-save"></i> Update Role
                        </button>
                        <a href="{{ route('roles.show', $role) }}" class="btn btn-secondary">
                            Cancel
                        </a>
                        
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
                        @endif
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Users Modal -->
@if($role->users->count() > 0)
<div class="modal fade" id="usersModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Users with {{ ucfirst($role->name) }} Role</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="list-group">
                    @foreach($role->users as $user)
                    <a href="{{ route('users.show', $user) }}" class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1">{{ $user->name }}</h6>
                            <small>{{ $user->email }}</small>
                        </div>
                        <small class="text-muted">
                            Joined {{ $user->created_at->diffForHumans() }}
                        </small>
                    </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endsection