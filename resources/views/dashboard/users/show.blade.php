@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">User Details</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            @can('users_edit')
                <a href="{{ route('users.edit', $user) }}" class="btn btn-outline-secondary me-2">
                    <i class="bi bi-pencil"></i> Edit
                </a>
            @endcan
            <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
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
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                    </div>
                    <h4>{{ $user->name }}</h4>
                    <p class="text-muted">{{ $user->email }}</p>

                    <div class="mb-3">
                        @if ($user->is_active)
                            <span class="badge bg-success">
                                <i class="bi bi-check-circle"></i> Active
                            </span>
                        @else
                            <span class="badge bg-danger">
                                <i class="bi bi-x-circle"></i> Inactive
                            </span>
                        @endif

                        @if ($user->hasRole('admin'))
                            <span class="badge bg-danger ms-1">
                                <i class="bi bi-shield-check"></i> Admin
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Account Information</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th>Member Since:</th>
                            <td>{{ $user->created_at->format('F d, Y') }}</td>
                        </tr>
                        <tr>
                            <th>Last Login:</th>
                            <td>
                                @if ($user->last_login_at)
                                    {{ $user->last_login_at->format('F d, Y H:i') }}
                                @else
                                    <span class="text-muted">Never</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Last Updated:</th>
                            <td>{{ $user->updated_at->format('F d, Y') }}</td>
                        </tr>
                        <tr>
                            <th>Email Verified:</th>
                            <td>
                                @if ($user->email_verified_at)
                                    <span class="badge bg-success">Yes</span>
                                @else
                                    <span class="badge bg-warning">No</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Assigned Roles</h5>
                </div>
                <div class="card-body">
                    @if ($user->roles->count() > 0)
                        <div class="row">
                            @foreach ($user->roles as $role)
                                <div class="col-md-6 mb-3">
                                    <div class="card border-{{ $role->name == 'admin' ? 'danger' : 'primary' }}">
                                        <div class="card-body">
                                            <h6 class="card-title">
                                                <strong>{{ ucfirst($role->name) }}</strong>
                                                @if ($role->name == 'admin')
                                                    <span class="badge bg-danger float-end">Admin</span>
                                                @endif
                                            </h6>
                                            <p class="card-text small text-muted">
                                                {{ $role->permissions->count() }} permissions
                                            </p>
                                            <a href="#"
                                                class="btn btn-sm btn-outline-{{ $role->name == 'admin' ? 'danger' : 'primary' }}"
                                                data-bs-toggle="modal"
                                                data-bs-target="#permissionsModal{{ $role->id }}">
                                                View Permissions
                                            </a>
                                        </div>
                                    </div>

                                    <!-- Permissions Modal -->
                                    <div class="modal fade" id="permissionsModal{{ $role->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">{{ ucfirst($role->name) }} Permissions</h5>
                                                    <button type="button" class="btn-close"
                                                        data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row">
                                                        @foreach ($role->permissions->chunk(2) as $chunk)
                                                            @foreach ($chunk as $permission)
                                                                <div class="col-md-6 mb-2">
                                                                    <span class="badge bg-secondary">
                                                                        {{ $permission->name }}
                                                                    </span>
                                                                </div>
                                                            @endforeach
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            This user has no roles assigned.
                        </div>
                    @endif
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">User Statistics</h5>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-6">
                                    <div class="display-6">{{ $user->clients->count() }}</div>
                                    <small class="text-muted">Clients</small>
                                </div>
                                <div class="col-6">
                                    <div class="display-6">{{ $user->invoices->count() }}</div>
                                    <small class="text-muted">Invoices</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                @can('users_edit')
                                    <a href="{{ route('users.edit', $user) }}" class="btn btn-primary">
                                        <i class="bi bi-pencil"></i> Edit User
                                    </a>
                                @endcan

                                @if ($user->id !== auth()->id())
                                    @if ($user->is_active)
                                        <button type="button" class="btn btn-outline-warning"
                                            onclick="toggleUserStatus({{ $user->id }}, true)">
                                            <i class="bi bi-power"></i> Deactivate User
                                        </button>
                                    @else
                                        <button type="button" class="btn btn-outline-success"
                                            onclick="toggleUserStatus({{ $user->id }}, false)">
                                            <i class="bi bi-power"></i> Activate User
                                        </button>
                                    @endif

                                    @if (!$user->hasRole('admin') && auth()->user()->can('users_edit'))
                                        <form action="{{ route('users.promote-to-admin', $user) }}" method="POST"
                                            class="d-grid">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-danger"
                                                onclick="return confirm('Are you sure you want to promote this user to admin?')">
                                                <i class="bi bi-shield-check"></i> Promote to Admin
                                            </button>
                                        </form>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
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
