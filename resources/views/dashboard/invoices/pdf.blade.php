<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.6;
            color: #333;
        }
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ddd;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 30px;
        }
        .company-info {
            flex: 1;
        }
        .invoice-info {
            text-align: right;
        }
        .title {
            font-size: 24px;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 10px;
        }
        .section {
            margin-bottom: 20px;
        }
        .section-title {
            font-weight: bold;
            border-bottom: 2px solid #667eea;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }
        .client-info, .company-info {
            line-height: 1.8;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table th {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 10px;
            text-align: left;
            font-weight: bold;
        }
        table td {
            border: 1px solid #dee2e6;
            padding: 10px;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .totals {
            width: 300px;
            margin-left: auto;
        }
        .totals tr td {
            border: none;
            padding: 5px 10px;
        }
        .totals tr:last-child td {
            font-weight: bold;
            font-size: 16px;
            border-top: 2px solid #333;
        }
        .status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-paid { background-color: #d4edda; color: #155724; }
        .status-sent { background-color: #fff3cd; color: #856404; }
        .status-draft { background-color: #e2e3e5; color: #383d41; }
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 11px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Header -->
        <div class="header">
            <div class="company-info">
                <div class="title">INVOICE</div>
                <div>{{ Auth::user()->name }}</div>
                <div>{{ Auth::user()->email }}</div>
                <div>Generated on: {{ now()->format('F d, Y') }}</div>
            </div>
            <div class="invoice-info">
                <div><strong>Invoice #:</strong> {{ $invoice->invoice_number }}</div>
                <div><strong>Date:</strong> {{ $invoice->invoice_date->format('F d, Y') }}</div>
                <div><strong>Due Date:</strong> {{ $invoice->due_date->format('F d, Y') }}</div>
                <div><strong>Status:</strong> 
                    <span class="status status-{{ $invoice->status }}">
                        {{ strtoupper($invoice->status) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Client Information -->
        <div class="section">
            <div class="section-title">BILL TO</div>
            <div class="client-info">
                <div><strong>{{ $invoice->client->name }}</strong></div>
                <div>{{ $invoice->client->email }}</div>
                <div>{{ $invoice->client->phone }}</div>
                <div>{{ $invoice->client->address }}</div>
                <div>{{ $invoice->client->city }}, {{ $invoice->client->state }} {{ $invoice->client->postal_code }}</div>
                <div>{{ $invoice->client->country }}</div>
            </div>
        </div>

        <!-- Invoice Items -->
        <div class="section">
            <div class="section-title">INVOICE ITEMS</div>
            <table>
                <thead>
                    <tr>
                        <th>Description</th>
                        <th width="80">Quantity</th>
                        <th width="100">Unit Price</th>
                        <th width="100">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->items as $item)
                    <tr>
                        <td>{{ $item->description }}</td>
                        <td class="text-center">{{ $item->quantity }}</td>
                        <td class="text-right">${{ number_format($item->unit_price, 2) }}</td>
                        <td class="text-right">${{ number_format($item->total, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Totals -->
        <div class="section">
            <table class="totals">
                <tr>
                    <td>Subtotal:</td>
                    <td class="text-right">${{ number_format($invoice->items->sum('total'), 2) }}</td>
                </tr>
                <tr>
                    <td>Tax ({{ number_format($invoice->tax_rate, 2) }}%):</td>
                    <td class="text-right">${{ number_format($invoice->items->sum('total') * ($invoice->tax_rate / 100), 2) }}</td>
                </tr>
                <tr>
                    <td><strong>Total Amount:</strong></td>
                    <td class="text-right"><strong>${{ number_format($invoice->total, 2) }}</strong></td>
                </tr>
            </table>
        </div>

        <!-- Notes -->
        @if($invoice->notes)
        <div class="section">
            <div class="section-title">NOTES</div>
            <div>{{ $invoice->notes }}</div>
        </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <div>Thank you for your business!</div>
            <div>This is a computer-generated invoice. No signature required.</div>
        </div>
    </div>
</body>
</html>