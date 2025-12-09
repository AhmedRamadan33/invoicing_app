@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Roles Management</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        @can('roles_create')
        <a href="{{ route('roles.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Add Role
        </a>
        @endcan
    </div>
</div>

<div class="alert alert-info">
    <i class="bi bi-info-circle"></i>
    Admin role is hidden and cannot be modified. It has all permissions by default.
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Role Name</th>
                        <th>Permissions</th>
                        <th>Users</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($roles as $role)
                    <tr>
                        <td>
                            <strong>{{ ucfirst($role->name) }}</strong>
                        </td>
                        <td>
                            <span class="badge bg-primary">{{ $role->permissions_count ?? $role->permissions->count() }}</span>
                            <small class="text-muted ms-2">permissions</small>
                        </td>
                        <td>
                            <span class="badge bg-info">{{ $role->users_count ?? $role->users->count() }}</span>
                            <small class="text-muted ms-2">users</small>
                        </td>
                        <td>{{ $role->created_at->format('Y-m-d') }}</td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('roles.show', $role) }}" class="btn btn-outline-primary">
                                    <i class="bi bi-eye"></i>
                                </a>
                                
                                @can('roles_edit')
                                <a href="{{ route('roles.edit', $role) }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @endcan
                                
                                @can('roles_delete')
                                <form action="{{ route('roles.destroy', $role) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger" 
                                            onclick="return confirm('Are you sure? This will permanently delete this role.')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        @if($roles->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $roles->links() }}
        </div>
        @endif
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">System Roles</h5>
            </div>
            <div class="card-body">
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <strong class="text-danger">Admin</strong>
                        <br>
                        <small class="text-muted">Full system access, all permissions</small>
                    </li>
                    <li class="mb-2">
                        <strong class="text-primary">User</strong>
                        <br>
                        <small class="text-muted">Basic access for regular users</small>
                    </li>
                    @foreach($roles->whereNotIn('name', ['user']) as $role)
                        <li class="mb-2">
                            <strong>{{ ucfirst($role->name) }}</strong>
                            <br>
                            <small class="text-muted">{{ $role->permissions_count ?? $role->permissions->count() }} permissions</small>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Quick Links</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    @can('roles_create')
                    <a href="{{ route('roles.create') }}" class="btn btn-outline-primary">
                        <i class="bi bi-plus-circle"></i> Create New Role
                    </a>
                    @endcan
                    
                    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-people"></i> Manage Users
                    </a>
                    
                    @can('roles_view')
                    <button class="btn btn-outline-info" onclick="loadRoleStatistics()">
                        <i class="bi bi-graph-up"></i> View Statistics
                    </button>
                    @endcan
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function loadRoleStatistics() {
    fetch('{{ route("roles.statistics") }}')
        .then(response => response.json())
        .then(data => {
            let message = `📊 Role Statistics:\n\n`;
            message += `• Total Roles: ${data.stats.total_roles}\n`;
            message += `• Total Permissions: ${data.stats.total_permissions}\n`;
            message += `• Roles with Users: ${data.stats.roles_with_users}\n\n`;
            
            message += `👥 User Distribution:\n`;
            data.role_distribution.forEach(role => {
                message += `• ${role.name}: ${role.users_count} users\n`;
            });
            
            alert(message);
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to load statistics.');
        });
}
</script>
@endsection
