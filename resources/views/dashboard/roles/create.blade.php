@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Create New Role</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
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

    <form method="POST" action="{{ route('roles.store') }}">
        @csrf

        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Role Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Role Name *</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name') }}" required placeholder="e.g., manager, accountant, sales">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Use lowercase letters and underscores (e.g.,
                                'content_manager')</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3"
                                placeholder="Brief description of this role's responsibilities">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Assign Permissions</h5>
                        <small class="text-muted">Select permissions for this role</small>
                    </div>
                    <div class="card-body">
                        @if ($permissions->count() > 0)
                            @foreach ($permissions as $category => $categoryPermissions)
                                <div class="card mb-3">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">
                                            <i class="bi bi-folder"></i>
                                            {{ ucfirst($category) }} Permissions
                                            <small class="text-muted">({{ count($categoryPermissions) }})</small>
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            @foreach ($categoryPermissions as $permission)
                                                <div class="col-md-6 mb-2">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="permissions[]"
                                                            value="{{ $permission->name }}" id="perm_{{ $permission->id }}"
                                                            {{ in_array($permission->name, old('permissions', [])) ? 'checked' : '' }}>
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
                        <h5 class="mb-0">Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-save"></i> Create Role
                            </button>
                            <a href="{{ route('roles.index') }}" class="btn btn-secondary">
                                Cancel
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Permission Categories</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled small">
                            @foreach ($permissions as $category => $categoryPermissions)
                                <li class="mb-2">
                                    <strong>{{ ucfirst($category) }}</strong>
                                    <span class="badge bg-secondary float-end">{{ count($categoryPermissions) }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">Notes</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled small text-muted">
                            <li><i class="bi bi-exclamation-circle"></i> Role name 'admin' is reserved</li>
                            <li><i class="bi bi-shield-check"></i> Select relevant permissions only</li>
                            <li><i class="bi bi-people"></i> Roles can be assigned to multiple users</li>
                            <li><i class="bi bi-lock"></i> Permissions define user capabilities</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@section('styles')
    <style>
        .form-check-label code {
            font-size: 0.85rem;
            background-color: #f8f9fa;
            padding: 2px 6px;
            border-radius: 4px;
        }
    </style>
@endsection
