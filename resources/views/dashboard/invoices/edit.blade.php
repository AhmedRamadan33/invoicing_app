@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Edit Invoice #{{ $invoice->invoice_number }}</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-outline-secondary me-2">
            <i class="bi bi-eye"></i> View
        </a>
        <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

<!-- Display Validation Errors -->
@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if(in_array($invoice->status, ['paid', 'cancelled']))
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle"></i> 
        This invoice is {{ $invoice->status }} and cannot be edited.
        @if($invoice->status == 'paid')
            <br><small>To make changes, you need to create a new invoice or contact the administrator.</small>
        @endif
    </div>
@endif

<form method="POST" action="{{ route('invoices.update', $invoice) }}" id="invoiceForm" 
      @if(in_array($invoice->status, ['paid', 'cancelled'])) onsubmit="return confirm('This invoice is {{ $invoice->status }}. Are you sure you want to update it?');" @endif>
    @csrf
    @method('PUT')
    
    <div class="row">
        <div class="col-md-12">
            <!-- Client & Dates -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Invoice Details</h5>
                    <span class="badge bg-{{ $invoice->status == 'paid' ? 'success' : ($invoice->status == 'sent' ? 'warning' : 'secondary') }}">
                        {{ ucfirst($invoice->status) }}
                    </span>
                </div>
                <div class="card-body row">
                    <div class="mb-3 col-md-6">
                        <label class="form-label">Client *</label>
                        <select name="client_id" class="form-select @error('client_id') is-invalid @enderror" required
                                @if(in_array($invoice->status, ['paid', 'cancelled'])) disabled @endif>
                            <option value="">Select Client</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}" 
                                    {{ old('client_id', $invoice->client_id) == $client->id ? 'selected' : '' }}>
                                    {{ $client->name }}
                                    @if($client->email)
                                        - {{ $client->email }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        @error('client_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        @if(in_array($invoice->status, ['paid', 'cancelled']))
                            <input type="hidden" name="client_id" value="{{ $invoice->client_id }}">
                        @endif
                    </div>

                    <div class="mb-3 col-md-6">
                        <label class="form-label">Status *</label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror" required
                                @if(in_array($invoice->status, ['paid', 'cancelled'])) disabled @endif>
                            <option value="draft" {{ old('status', $invoice->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="sent" {{ old('status', $invoice->status) == 'sent' ? 'selected' : '' }}>Sent</option>
                            <option value="paid" {{ old('status', $invoice->status) == 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="cancelled" {{ old('status', $invoice->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        @if(in_array($invoice->status, ['paid', 'cancelled']))
                            <input type="hidden" name="status" value="{{ $invoice->status }}">
                        @endif
                    </div>
                    
                    <div class="mb-3 col-md-4">
                        <label class="form-label">Invoice Date *</label>
                        <input type="date" name="invoice_date" class="form-control @error('invoice_date') is-invalid @enderror" 
                               value="{{ old('invoice_date', $invoice->invoice_date->format('Y-m-d')) }}" required
                               @if(in_array($invoice->status, ['paid', 'cancelled'])) readonly @endif>
                        @error('invoice_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3 col-md-4">
                        <label class="form-label">Due Date *</label>
                        <input type="date" name="due_date" class="form-control @error('due_date') is-invalid @enderror" 
                               value="{{ old('due_date', $invoice->due_date->format('Y-m-d')) }}" required
                               @if(in_array($invoice->status, ['paid', 'cancelled'])) readonly @endif>
                        @error('due_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3 col-md-4">
                        <label class="form-label">Tax Rate (%)</label>
                        <input type="number" step="0.01" name="tax_rate" class="form-control @error('tax_rate') is-invalid @enderror" 
                               value="{{ old('tax_rate', $invoice->tax_rate) }}" min="0" max="100"
                               @if(in_array($invoice->status, ['paid', 'cancelled'])) readonly @endif>
                        @error('tax_rate')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Items Section -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Invoice Items</h5>
                    <span class="badge bg-primary">{{ $invoice->items->count() }} items</span>
                </div>
                <div class="card-body">
                    @if(in_array($invoice->status, ['paid', 'cancelled']))
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> Items cannot be modified for {{ $invoice->status }} invoices.
                        </div>
                    @endif
                    
                    <div id="items-container">
                        @php
                            $oldItems = old('items', $invoice->items->toArray());
                        @endphp
                        
                        @foreach($oldItems as $index => $item)
                            <div class="item-row row g-3 mb-3">
                                <div class="col-md-5">
                                    <input type="text" name="items[{{ $index }}][description]" 
                                           class="form-control @error('items.' . $index . '.description') is-invalid @enderror"
                                           placeholder="Description" 
                                           value="{{ $item['description'] }}" required
                                           @if(in_array($invoice->status, ['paid', 'cancelled'])) readonly @endif>
                                    @error('items.' . $index . '.description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-2">
                                    <input type="number" name="items[{{ $index }}][quantity]" 
                                           class="form-control quantity @error('items.' . $index . '.quantity') is-invalid @enderror"
                                           placeholder="Qty" min="1" 
                                           value="{{ $item['quantity'] }}" required
                                           @if(in_array($invoice->status, ['paid', 'cancelled'])) readonly @endif>
                                    @error('items.' . $index . '.quantity')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-2">
                                    <input type="number" step="0.01" name="items[{{ $index }}][unit_price]" 
                                           class="form-control unit-price @error('items.' . $index . '.unit_price') is-invalid @enderror"
                                           placeholder="Unit Price" min="0" 
                                           value="{{ $item['unit_price'] }}" required
                                           @if(in_array($invoice->status, ['paid', 'cancelled'])) readonly @endif>
                                    @error('items.' . $index . '.unit_price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-2">
                                    @php
                                        $itemTotal = $item['quantity'] * $item['unit_price'];
                                    @endphp
                                    <input type="text" class="form-control item-total" 
                                           value="${{ number_format($itemTotal, 2) }}" readonly>
                                </div>
                                @if(!in_array($invoice->status, ['paid', 'cancelled']) && $index > 0)
                                    <div class="col-md-1">
                                        <button type="button" class="btn btn-danger btn-sm remove-item">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    
                    @if(!in_array($invoice->status, ['paid', 'cancelled']))
                        <button type="button" id="add-item" class="btn btn-outline-primary btn-sm mt-2">
                            <i class="bi bi-plus"></i> Add Item
                        </button>
                    @endif
                    
                    @error('items')
                        <div class="text-danger mt-2">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Notes -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Notes</h5>
                </div>
                <div class="card-body">
                    <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3" 
                              placeholder="Additional notes..."
                              @if(in_array($invoice->status, ['paid', 'cancelled'])) readonly @endif>{{ old('notes', $invoice->notes) }}</textarea>
                    @error('notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <!-- Totals -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Invoice Totals</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal:</span>
                        <span id="subtotal">${{ number_format($invoice->items->sum('total'), 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Tax ({{ number_format($invoice->tax_rate, 2) }}%):</span>
                        <span id="tax">${{ number_format($invoice->items->sum('total') * ($invoice->tax_rate / 100), 2) }}</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-2 fw-bold">
                        <span>Total:</span>
                        <span id="total">${{ number_format($invoice->total, 2) }}</span>
                    </div>
                    @if($invoice->due_date->isPast() && !in_array($invoice->status, ['paid', 'cancelled']))
                        <div class="alert alert-danger mt-3">
                            <i class="bi bi-exclamation-triangle"></i> 
                            This invoice is overdue by {{ $invoice->due_date->diffInDays(now()) }} days.
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        
                        <div class="col-md-3">
                            <a href="{{ route('invoices.download', $invoice) }}" class="btn btn-outline-success w-100">
                                <i class="bi bi-download"></i> Download PDF
                            </a>
                        </div>
                        
                        <div class="col-md-3">
                            <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-outline-secondary w-100">
                                <i class="bi bi-x-circle"></i> Cancel Edit
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Submit Buttons -->
            <div class="d-grid gap-2 col-md-6 mx-auto">
                @if(in_array($invoice->status, ['paid', 'cancelled']))
                    <div class="alert alert-warning text-center">
                        <i class="bi bi-lock"></i> This invoice is locked for editing.
                    </div>
                @else
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-save"></i> Update Invoice
                    </button>
                @endif
                
                <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Invoice
                </a>
            </div>
        </div>
    </div>
</form>

@section('scripts')
<script>
$(document).ready(function() {
    let itemIndex = {{ count(old('items', $invoice->items->toArray())) }};
    const isLocked = {{ in_array($invoice->status, ['paid', 'cancelled']) ? 'true' : 'false' }};
    
    if (!isLocked) {
        // Add new item row
        $('#add-item').click(function() {
            const template = `
                <div class="item-row row g-3 mb-3">
                    <div class="col-md-5">
                        <input type="text" name="items[${itemIndex}][description]" 
                               class="form-control" placeholder="Description" required>
                    </div>
                    <div class="col-md-2">
                        <input type="number" name="items[${itemIndex}][quantity]" 
                               class="form-control quantity" placeholder="Qty" min="1" value="1" required>
                    </div>
                    <div class="col-md-2">
                        <input type="number" step="0.01" name="items[${itemIndex}][unit_price]" 
                               class="form-control unit-price" placeholder="Unit Price" min="0" required>
                    </div>
                    <div class="col-md-2">
                        <input type="text" class="form-control item-total" placeholder="Total" readonly>
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-danger btn-sm remove-item">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            `;
            $('#items-container').append(template);
            itemIndex++;
        });
        
        // Remove item row
        $(document).on('click', '.remove-item', function() {
            if ($('.item-row').length <= 1) {
                alert('At least one item is required.');
                return;
            }
            $(this).closest('.item-row').remove();
            calculateTotals();
        });
        
        // Calculate item total
        $(document).on('input', '.quantity, .unit-price', function() {
            const row = $(this).closest('.item-row');
            const quantity = parseFloat(row.find('.quantity').val()) || 0;
            const unitPrice = parseFloat(row.find('.unit-price').val()) || 0;
            const total = quantity * unitPrice;
            row.find('.item-total').val('$' + total.toFixed(2));
            calculateTotals();
        });
        
        // Calculate all totals
        function calculateTotals() {
            let subtotal = 0;
            
            $('.item-row').each(function() {
                const totalText = $(this).find('.item-total').val();
                if (totalText && totalText.startsWith('$')) {
                    const total = parseFloat(totalText.replace('$', '')) || 0;
                    subtotal += total;
                } else {
                    const quantity = parseFloat($(this).find('.quantity').val()) || 0;
                    const unitPrice = parseFloat($(this).find('.unit-price').val()) || 0;
                    subtotal += quantity * unitPrice;
                }
            });
            
            const taxRate = parseFloat($('input[name="tax_rate"]').val()) || 0;
            const tax = subtotal * (taxRate / 100);
            const total = subtotal + tax;
            
            $('#subtotal').text('$' + subtotal.toFixed(2));
            $('#tax').text('$' + tax.toFixed(2));
            $('#total').text('$' + total.toFixed(2));
        }
        
        // Recalculate when tax rate changes
        $('input[name="tax_rate"]').on('input', calculateTotals);
        
        // Initialize calculation for existing items
        $('.item-row').each(function() {
            const quantity = parseFloat($(this).find('.quantity').val()) || 0;
            const unitPrice = parseFloat($(this).find('.unit-price').val()) || 0;
            if (quantity && unitPrice) {
                const total = quantity * unitPrice;
                $(this).find('.item-total').val('$' + total.toFixed(2));
            }
        });
        
        calculateTotals();
        
        // Form validation before submit
        $('#invoiceForm').on('submit', function(e) {
            let isValid = true;
            let errorMessage = '';
            
            // Check if at least one item has valid data
            let hasValidItem = false;
            $('.item-row').each(function() {
                const description = $(this).find('input[name*="description"]').val().trim();
                const quantity = parseFloat($(this).find('.quantity').val()) || 0;
                const unitPrice = parseFloat($(this).find('.unit-price').val()) || 0;
                
                if (description && quantity > 0 && unitPrice > 0) {
                    hasValidItem = true;
                }
            });
            
            if (!hasValidItem) {
                errorMessage = 'Please add at least one valid item with description, quantity and price.';
                isValid = false;
            }
            
            // Check due date
            const invoiceDate = new Date($('input[name="invoice_date"]').val());
            const dueDate = new Date($('input[name="due_date"]').val());
            if (dueDate < invoiceDate) {
                errorMessage = 'Due date must be on or after invoice date.';
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
                alert(errorMessage);
                return false;
            }
            
            // Confirm if changing from sent to draft
            const oldStatus = '{{ $invoice->status }}';
            const newStatus = $('select[name="status"]').val();
            if (oldStatus === 'sent' && newStatus === 'draft') {
                if (!confirm('Are you sure you want to change this sent invoice back to draft?')) {
                    e.preventDefault();
                    return false;
                }
            }
            
            return true;
        });
    }
});
</script>
@endsection
@endsection