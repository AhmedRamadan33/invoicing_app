@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Invoices</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            @can('invoice_create')
                <a href="{{ route('invoices.create') }}" class="btn btn-primary me-2">
                    <i class="bi bi-plus-circle"></i> New Invoice
                </a>
            @endcan

        </div>
    </div>


    <!-- Search and Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('invoices.index') }}" id="filterForm">
                <div class="row g-3">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control"
                            placeholder="Search by invoice number, client name or email..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-select">
                            <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All Statuses</option>
                            @foreach ($statuses as $status)
                                <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                                    {{ ucfirst($status) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="client_id" class="form-select">
                            <option value="">All Clients</option>
                            @foreach ($clients as $client)
                                <option value="{{ $client->id }}"
                                    {{ request('client_id') == $client->id ? 'selected' : '' }}>
                                    {{ $client->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="date_from" class="form-control" placeholder="From Date"
                            value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="date_to" class="form-control" placeholder="To Date"
                            value="{{ request('date_to') }}">
                    </div>
                </div>
                <div class="row g-3 mt-2">
                    <div class="col-md-8">
                        <div class="btn-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search"></i> Filter
                            </button>
                            @if (request()->hasAny(['search', 'status', 'client_id', 'date_from', 'date_to']))
                                <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary">
                                    Clear Filters
                                </a>
                            @endif

                        </div>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="form-text">
                            Showing {{ $invoices->firstItem() }} - {{ $invoices->lastItem() }} of
                            {{ $invoices->total() }} invoices
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>


    <!-- Invoices Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Invoice #</th>
                            <th>Client</th>
                            @if ($isAdmin ?? false)
                                <th>Created By</th>
                            @endif
                            <th>Date</th>
                            <th>Due Date</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoices as $invoice)
                            <tr>

                                <td>
                                    <a href="{{ route('invoices.show', $invoice) }}" class="fw-bold">
                                        {{ $invoice->invoice_number }}
                                    </a>
                                </td>
                                <td>
                                    <div>{{ $invoice->client->name }}</div>
                                    <small class="text-muted">{{ $invoice->client->email }}</small>
                                </td>
                                @if ($isAdmin ?? false)
                                    <td>
                                        <small>{{ $invoice->user->name ?? 'N/A' }}</small>
                                    </td>
                                @endif
                                <td>
                                    <div>{{ $invoice->invoice_date->format('M d, Y') }}</div>
                                    <small class="text-muted">{{ $invoice->invoice_date->diffForHumans() }}</small>
                                </td>
                                <td>
                                    <div
                                        class="{{ $invoice->due_date->isPast() && !in_array($invoice->status, ['paid', 'cancelled']) ? 'text-danger fw-bold' : '' }}">
                                        {{ $invoice->due_date->format('M d, Y') }}
                                    </div>
                                    <small class="text-muted">{{ $invoice->due_date->diffForHumans() }}</small>
                                </td>
                                <td>
                                    <div class="fw-bold">${{ number_format($invoice->total, 2) }}</div>
                                    <small class="text-muted">Tax:
                                        ${{ number_format($invoice->total - $invoice->total / (1 + $invoice->tax_rate / 100), 2) }}</small>
                                </td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'draft' => 'secondary',
                                            'sent' => 'warning',
                                            'paid' => 'success',
                                            'cancelled' => 'dark',
                                        ];
                                    @endphp
                                    <span class="badge bg-{{ $statusColors[$invoice->status] ?? 'secondary' }}">
                                        {{ ucfirst($invoice->status) }}
                                    </span>

                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-outline-primary"
                                            title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        @can('invoice_edit', $invoice)
                                            <a href="{{ route('invoices.edit', $invoice) }}" class="btn btn-outline-secondary"
                                                title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        @endcan

                                        @can('invoice_download', $invoice)
                                            <a href="{{ route('invoices.download', $invoice) }}"
                                                class="btn btn-outline-success" title="Download PDF">
                                                <i class="bi bi-download"></i>
                                            </a>
                                        @endcan

                                        <div class="btn-group">
                                            <button type="button"
                                                class="btn btn-outline-info dropdown-toggle dropdown-toggle-split"
                                                data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="bi bi-three-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                @can('invoice_duplicate', $invoice)
                                                    <li>
                                                        <a class="dropdown-item"
                                                            href="{{ route('invoices.duplicate', $invoice) }}"
                                                            onclick="return confirm('Duplicate this invoice?')">
                                                            <i class="bi bi-copy"></i> Duplicate
                                                        </a>
                                                    </li>
                                                @endcan

                                                @if ($invoice->status == 'draft')
                                                    <li>
                                                        <form action="{{ route('invoices.send', $invoice) }}"
                                                            method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="dropdown-item"
                                                                onclick="return confirm('Mark as sent?')">
                                                                <i class="bi bi-send"></i> Mark as Sent
                                                            </button>
                                                        </form>
                                                    </li>
                                                @endif

                                                @if ($invoice->status == 'sent')
                                                    <li>
                                                        <form action="{{ route('invoices.mark-as-paid', $invoice) }}"
                                                            method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="dropdown-item"
                                                                onclick="return confirm('Mark as paid?')">
                                                                <i class="bi bi-check-circle"></i> Mark as Paid
                                                            </button>
                                                        </form>
                                                    </li>
                                                @endif


                                                <li>
                                                    <hr class="dropdown-divider">
                                                </li>


                                                @can('invoice_delete', $invoice)
                                                    <li>
                                                        <form action="{{ route('invoices.destroy', $invoice) }}"
                                                            method="POST" class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="dropdown-item text-danger"
                                                                onclick="return confirm('Are you sure you want to delete this invoice?')">
                                                                <i class="bi bi-trash"></i> Delete
                                                            </button>
                                                        </form>
                                                    </li>
                                                @endcan
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $isAdmin ?? false ? '9' : '8' }}" class="text-center py-4">
                                    <i class="bi bi-receipt fs-1 text-muted mb-3"></i>
                                    <p class="text-muted">No invoices found.</p>
                                    @can('invoice_create')
                                        <a href="{{ route('invoices.create') }}" class="btn btn-primary">
                                            <i class="bi bi-plus-circle"></i> Create Your First Invoice
                                        </a>
                                    @endcan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($invoices->hasPages())
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div class="text-muted">
                        Showing {{ $invoices->firstItem() }} to {{ $invoices->lastItem() }} of {{ $invoices->total() }}
                        entries
                    </div>
                    <div>
                        {{ $invoices->withQueryString()->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>

   
@endsection

@section('styles')
    <style>
        .stat-card {
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            transition: transform 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
        }

        .table-danger {
            background-color: rgba(220, 53, 69, 0.05);
        }
    </style>
@endsection
