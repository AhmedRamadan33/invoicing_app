@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Invoice Details</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="{{ route('invoices.download', $invoice) }}" class="btn btn-outline-success">
                <i class="bi bi-download"></i> Download PDF
            </a>
            <a href="{{ route('invoices.edit', $invoice) }}" class="btn btn-outline-secondary">
                <i class="bi bi-pencil"></i> Edit
            </a>
        </div>
        <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="row">
    <!-- Invoice Header -->
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5>From:</h5>
                        <address class="mb-0">
                            <strong>{{ Auth::user()->name }}</strong><br>
                            {{ Auth::user()->email }}
                        </address>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <h5>Invoice #: {{ $invoice->invoice_number }}</h5>
                        <div class="mb-2">
                            <span class="badge bg-{{ $invoice->status == 'paid' ? 'success' : ($invoice->status == 'sent' ? 'warning' : 'secondary') }} fs-6">
                                {{ ucfirst($invoice->status) }}
                            </span>
                        </div>
                        <div><strong>Date:</strong> {{ $invoice->invoice_date->format('F d, Y') }}</div>
                        <div><strong>Due Date:</strong> {{ $invoice->due_date->format('F d, Y') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Client Information -->
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Bill To</h5>
            </div>
            <div class="card-body">
                <address class="mb-0">
                    <strong>{{ $invoice->client->name }}</strong><br>
                    {{ $invoice->client->email }}<br>
                    {{ $invoice->client->phone }}<br>
                    {{ $invoice->client->address }}<br>
                    {{ $invoice->client->city }}, {{ $invoice->client->state }} {{ $invoice->client->postal_code }}<br>
                    {{ $invoice->client->country }}
                </address>
            </div>
        </div>
    </div>

    <!-- Status Actions -->
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Invoice Actions</h5>
            </div>
            <div class="card-body">
                @if($invoice->status == 'draft')
                    <form action="{{ route('invoices.send', $invoice) }}" method="POST" class="mb-3">
                        @csrf
                        <button type="submit" class="btn btn-warning w-100">
                            <i class="bi bi-send"></i> Mark as Sent
                        </button>
                    </form>
                @endif

                @if($invoice->status == 'sent')
                    <form action="{{ route('invoices.mark-as-paid', $invoice) }}" method="POST" class="mb-3">
                        @csrf
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-check-circle"></i> Mark as Paid
                        </button>
                    </form>
                @endif

                <div class="d-grid gap-2">
                    <a href="{{ route('invoices.download', $invoice) }}" class="btn btn-outline-primary">
                        <i class="bi bi-file-pdf"></i> Download PDF
                    </a>
                    <a href="{{ route('invoices.edit', $invoice) }}" class="btn btn-outline-secondary">
                        <i class="bi bi-pencil"></i> Edit Invoice
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Invoice Items -->
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Invoice Items</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Description</th>
                                <th class="text-center">Quantity</th>
                                <th class="text-end">Unit Price</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoice->items as $item)
                            <tr>
                                <td>{{ $item->description }}</td>
                                <td class="text-center">{{ $item->quantity }}</td>
                                <td class="text-end">${{ number_format($item->unit_price, 2) }}</td>
                                <td class="text-end">${{ number_format($item->total, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-end"><strong>Subtotal:</strong></td>
                                <td class="text-end">${{ number_format($invoice->items->sum('total'), 2) }}</td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-end"><strong>Tax ({{ number_format($invoice->tax_rate, 2) }}%):</strong></td>
                                <td class="text-end">${{ number_format($invoice->items->sum('total') * ($invoice->tax_rate / 100), 2) }}</td>
                            </tr>
                            <tr class="table-active">
                                <td colspan="3" class="text-end"><strong>Total Amount:</strong></td>
                                <td class="text-end"><strong>${{ number_format($invoice->total, 2) }}</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Notes -->
    @if($invoice->notes)
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Notes</h5>
            </div>
            <div class="card-body">
                <p class="mb-0">{{ $invoice->notes }}</p>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Action Buttons -->
<div class="mt-4 pt-3 border-top">
    <div class="d-flex justify-content-between">
        <div>
            <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Invoices
            </a>
        </div>
        <div class="btn-group">
            <a href="{{ route('invoices.edit', $invoice) }}" class="btn btn-secondary">
                <i class="bi bi-pencil"></i> Edit
            </a>
            <form action="{{ route('invoices.destroy', $invoice) }}" method="POST" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger" 
                        onclick="return confirm('Are you sure you want to delete this invoice?')">
                    <i class="bi bi-trash"></i> Delete
                </button>
            </form>
        </div>
    </div>
</div>
@endsection