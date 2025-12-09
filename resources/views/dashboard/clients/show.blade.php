@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Client Details</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="{{ route('clients.edit', $client) }}" class="btn btn-outline-secondary me-2">
                <i class="bi bi-pencil"></i> Edit
            </a>
            <a href="{{ route('clients.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Client Information -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Client Information</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="30%">Name:</th>
                            <td>{{ $client->name }}</td>
                        </tr>
                        <tr>
                            <th>Email:</th>
                            <td>
                                <a href="mailto:{{ $client->email }}">{{ $client->email }}</a>
                            </td>
                        </tr>
                        <tr>
                            <th>Phone:</th>
                            <td>{{ $client->phone ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Address:</th>
                            <td>{{ $client->address }}</td>
                        </tr>
                        <tr>
                            <th>City:</th>
                            <td>{{ $client->city ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>State:</th>
                            <td>{{ $client->state ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Country:</th>
                            <td>{{ $client->country ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Postal Code:</th>
                            <td>{{ $client->postal_code ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Created:</th>
                            <td>{{ $client->created_at->format('F d, Y') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Client Invoices -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Invoices</h5>
                </div>
                <div class="card-body">
                    @if ($client->invoices->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Invoice #</th>
                                        <th>Date</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($client->invoices as $invoice)
                                        <tr>
                                            <td>
                                                <a href="{{ route('invoices.show', $invoice) }}">
                                                    {{ $invoice->invoice_number }}
                                                </a>
                                            </td>
                                            <td>{{ $invoice->invoice_date->format('M d, Y') }}</td>
                                            <td>${{ number_format($invoice->total, 2) }}</td>
                                            <td>
                                                <span
                                                    class="badge bg-{{ $invoice->status == 'paid' ? 'success' : ($invoice->status == 'sent' ? 'warning' : 'secondary') }}">
                                                    {{ ucfirst($invoice->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                <a href="{{ route('invoices.show', $invoice) }}"
                                                    class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-receipt fs-1 text-muted mb-3"></i>
                            <p class="text-muted">No invoices found for this client.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
