@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Edit User: {{ $user->name }}</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="{{ route('users.show', $user) }}" class="btn btn-outline-secondary me-2">
                <i class="bi bi-eye"></i> View
            </a>
            <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
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

    <form method="POST" action="{{ route('users.update', $user) }}">
        @csrf
        @method('PUT')

        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">User Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Full Name *</label>
                                <input type="text" name="name"
                                    class="form-control @error('name') is-invalid @enderror"
                                    value="{{ old('name', $user->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email Address *</label>
                                <input type="email" name="email"
                                    class="form-control @error('email') is-invalid @enderror"
                                    value="{{ old('email', $user->email) }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="password"
                                    class="form-control @error('password') is-invalid @enderror"
                                    placeholder="Leave blank to keep current">
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Minimum 8 characters</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Confirm Password</label>
                                <input type="password" name="password_confirmation" class="form-control"
                                    placeholder="Leave blank to keep current">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Assign Roles</h5>
                        @if ($user->hasRole('admin'))
                            <span class="badge bg-danger">Admin User</span>
                        @endif
                    </div>
                    <div class="card-body">
                        @if ($roles->count() > 0)
                            <div class="row">
                                @foreach ($roles as $role)
                                    <div class="col-md-4 mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="roles[]"
                                                value="{{ $role->name }}" id="role_{{ $role->id }}"
                                                {{ in_array($role->name, old('roles', $user->roles->pluck('name')->toArray())) ? 'checked' : '' }}
                                                {{ $user->hasRole('admin') ? 'disabled' : '' }}>
                                            <label class="form-check-label" for="role_{{ $role->id }}">
                                                <strong>{{ ucfirst($role->name) }}</strong>
                                                <br>
                                                <small class="text-muted">
                                                    {{ $role->permissions->count() }} permissions
                                                </small>
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @if ($user->hasRole('admin'))
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle"></i>
                                    Admin users cannot have other roles. To change roles, first remove admin role.
                                </div>
                            @endif
                        @else
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle"></i>
                                No roles available.
                            </div>
                        @endif
                        @error('roles')
                            <div class="text-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">User Status</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Account Status</label>
                            <div>
                                @if ($user->is_active)
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle"></i> Active
                                    </span>
                                @else
                                    <span class="badge bg-danger">
                                        <i class="bi bi-x-circle"></i> Inactive
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Member Since</label>
                            <div>{{ $user->created_at->format('F d, Y') }}</div>
                            <small class="text-muted">{{ $user->created_at->diffForHumans() }}</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Last Updated</label>
                            <div>{{ $user->updated_at->format('F d, Y') }}</div>
                            <small class="text-muted">{{ $user->updated_at->diffForHumans() }}</small>
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
                                <i class="bi bi-save"></i> Update User
                            </button>
                            <a href="{{ route('users.show', $user) }}" class="btn btn-secondary">
                                Cancel
                            </a>

                            @if (!$user->hasRole('admin') && auth()->user()->can('users_edit'))
                                <form action="{{ route('users.promote-to-admin', $user) }}" method="POST" class="d-grid">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-danger"
                                        onclick="return confirm('Are you sure you want to promote this user to admin?')">
                                        <i class="bi bi-shield-check"></i> Promote to Admin
                                    </button>
                                </form>
                            @endif

                            @if ($user->id !== auth()->id())
                                <button type="button" class="btn btn-outline-warning"
                                    onclick="toggleUserStatus({{ $user->id }}, {{ $user->is_active ? 'true' : 'false' }})">
                                    <i class="bi bi-power"></i>
                                    {{ $user->is_active ? 'Deactivate' : 'Activate' }} User
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@section('scripts')
    <script>
        function toggleUserStatus(userId, isActive) {
            const action = isActive ? 'deactivate' : 'activate';

            if (!confirm(`Are you sure you want to ${action} this user?`)) {
                return;
            }

            fetch(`/users/${userId}/toggle-status`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                });
        }
    </script>
@endsection
