@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Create New Invoice</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
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

    <form method="POST" action="{{ route('invoices.store') }}" id="invoiceForm">
        @csrf

        <div class="row">
            <div class="col-md-12">
                <!-- Client & Dates -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Invoice Details</h5>
                    </div>
                    <div class="card-body row">
                        <div class="mb-3 col-md-6">
                            <label class="form-label">Client *</label>
                            <select name="client_id" class="form-select @error('client_id') is-invalid @enderror" required>
                                <option value="">Select Client</option>
                                @foreach ($clients as $client)
                                    <option value="{{ $client->id }}"
                                        {{ old('client_id') == $client->id ? 'selected' : '' }}>
                                        {{ $client->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('client_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3 col-md-6">
                            <label class="form-label">Status *</label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="sent" {{ old('status') == 'sent' ? 'selected' : '' }}>Sent</option>
                                <option value="paid" {{ old('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                                <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3 col-md-4">
                            <label class="form-label">Invoice Date *</label>
                            <input type="date" name="invoice_date" class="form-control @error('invoice_date') is-invalid @enderror"
                                value="{{ old('invoice_date', date('Y-m-d')) }}" required>
                            @error('invoice_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3 col-md-4">
                            <label class="form-label">Due Date *</label>
                            <input type="date" name="due_date" class="form-control @error('due_date') is-invalid @enderror"
                                value="{{ old('due_date', date('Y-m-d', strtotime('+30 days'))) }}" required>
                            @error('due_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3 col-md-4">
                            <label class="form-label">Tax Rate (%)</label>
                            <input type="number" step="0.01" name="tax_rate" class="form-control @error('tax_rate') is-invalid @enderror"
                                value="{{ old('tax_rate', 0) }}" min="0" max="100">
                            @error('tax_rate')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>
                </div>

            </div>
            <div class="col-md-12">
                <!-- Items Section -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Invoice Items</h5>
                    </div>
                    <div class="card-body">
                        <div id="items-container">
                            @php
                                $oldItems = old('items', [['description' => '', 'quantity' => 1, 'unit_price' => 0]]);
                            @endphp
                            
                            @foreach($oldItems as $index => $item)
                                <div class="item-row row g-3 mb-3">
                                    <div class="col-md-5">
                                        <input type="text" name="items[{{ $index }}][description]" 
                                               class="form-control @error('items.' . $index . '.description') is-invalid @enderror"
                                               placeholder="Description" 
                                               value="{{ $item['description'] }}" required>
                                        @error('items.' . $index . '.description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-2">
                                        <input type="number" name="items[{{ $index }}][quantity]" 
                                               class="form-control quantity @error('items.' . $index . '.quantity') is-invalid @enderror"
                                               placeholder="Qty" min="1" 
                                               value="{{ $item['quantity'] }}" required>
                                        @error('items.' . $index . '.quantity')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-2">
                                        <input type="number" step="0.01" name="items[{{ $index }}][unit_price]" 
                                               class="form-control unit-price @error('items.' . $index . '.unit_price') is-invalid @enderror"
                                               placeholder="Unit Price" min="0" 
                                               value="{{ $item['unit_price'] }}" required>
                                        @error('items.' . $index . '.unit_price')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-2">
                                        <input type="text" class="form-control item-total" placeholder="Total" readonly>
                                    </div>
                                    @if($index > 0)
                                        <div class="col-md-1">
                                            <button type="button" class="btn btn-danger btn-sm remove-item">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        <button type="button" id="add-item" class="btn btn-outline-primary btn-sm mt-2">
                            <i class="bi bi-plus"></i> Add Item
                        </button>
                        
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
                                  placeholder="Additional notes...">{{ old('notes') }}</textarea>
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
                            <span id="subtotal">$0.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Tax:</span>
                            <span id="tax">$0.00</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-2 fw-bold">
                            <span>Total:</span>
                            <span id="total">$0.00</span>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Submit -->
            <div class="d-grid gap-2 col-md-4 mx-auto">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="bi bi-save"></i> Create Invoice
                </button>
                <a href="{{ route('invoices.index') }}" class="btn btn-secondary">
                    Cancel
                </a>
            </div>
        </div>
    </form>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            let itemIndex = {{ count(old('items', [])) }};

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
                    const quantity = parseFloat($(this).find('.quantity').val()) || 0;
                    const unitPrice = parseFloat($(this).find('.unit-price').val()) || 0;
                    subtotal += quantity * unitPrice;
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

            // Initialize calculation with old values
            $('.item-row').each(function() {
                const quantity = parseFloat($(this).find('.quantity').val()) || 0;
                const unitPrice = parseFloat($(this).find('.unit-price').val()) || 0;
                if (quantity > 0 && unitPrice > 0) {
                    const total = quantity * unitPrice;
                    $(this).find('.item-total').val('$' + total.toFixed(2));
                }
            });
            
            calculateTotals();
            
            // Form submission validation
            $('#invoiceForm').on('submit', function(e) {
                let isValid = true;
                let errorMessage = '';
                
                // Check if at least one item has description
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
                }
            });
        });
    </script>
@endsection