@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Clients</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            @can('client_create')
                <a href="{{ route('clients.create') }}" class="btn btn-primary me-2">
                    <i class="bi bi-plus-circle"></i> New Client
                </a>
            @endcan

        </div>
    </div>

    <!-- Search and Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('clients.index') }}">
                <div class="row g-3">
                    <div class="col-md-8">
                        <input type="text" name="search" class="form-control"
                            placeholder="Search clients by name, email, or phone..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i> Search
                        </button>
                        @if (request('search'))
                            <a href="{{ route('clients.index') }}" class="btn btn-outline-secondary w-100 mt-2">
                                Clear Search
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Clients Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Address</th>
                            <th>Invoices</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($clients as $client)
                            <tr>

                                <td>{{ $client->name }}</td>
                                <td>
                                    <a href="mailto:{{ $client->email }}">{{ $client->email }}</a>
                                </td>
                                <td>{{ $client->phone ?? 'N/A' }}</td>
                                <td>{{ Str::limit($client->address, 30) }}</td>
                                <td>
                                    <span class="badge bg-primary">{{ $client->invoices_count }}</span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('clients.show', $client) }}" class="btn btn-outline-primary">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        @can('client_edit', $client)
                                            <a href="{{ route('clients.edit', $client) }}" class="btn btn-outline-secondary">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        @endcan
                                        @can('client_delete', $client)
                                            <form action="{{ route('clients.destroy', $client) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger"
                                                    onclick="return confirm('Are you sure? This will delete all associated invoices.')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>

                                <td colspan="6" class="text-center">No clients found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($clients->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $clients->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
