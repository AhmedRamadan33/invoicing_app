@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Dashboard</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        @can('invoice_create')
        <a href="{{ route('invoices.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Create Invoice
        </a>
        @endcan
    </div>
</div>

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-muted">Total Invoices</h6>
                        <h3>{{ number_format($stats['total_invoices']) }}</h3>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-receipt text-primary fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-muted">Total Clients</h6>
                        <h3>{{ number_format($stats['total_clients']) }}</h3>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-people text-success fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-muted">Paid Invoices</h6>
                        <h3>{{ number_format($stats['paid_invoices']) }}</h3>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-check-circle text-success fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-muted">Pending Invoices</h6>
                        <h3>{{ number_format($stats['pending_invoices']) }}</h3>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-clock text-warning fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Additional Stats for Admin -->
@if($isAdmin)
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-muted">Total Users</h6>
                        <h3>{{ number_format($stats['total_users']) }}</h3>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-person-badge text-info fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-muted">Total Revenue</h6>
                        <h3>${{ number_format($stats['total_revenue'], 2) }}</h3>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-currency-dollar text-danger fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-muted">Avg. Invoice</h6>
                        <h3>${{ number_format($stats['average_invoice'], 2) }}</h3>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-graph-up text-primary fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-muted">Overdue Invoices</h6>
                        <h3>{{ number_format($stats['overdue_invoices']) }}</h3>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-exclamation-triangle text-danger fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<div class="row">
    <!-- Recent Invoices -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Invoices</h5>
                <a href="{{ route('invoices.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Invoice #</th>
                                @if($isAdmin)
                                <th>Created By</th>
                                @endif
                                <th>Client</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recent_invoices as $invoice)
                            <tr>
                                <td>
                                    <a href="{{ route('invoices.show', $invoice) }}">
                                        {{ $invoice->invoice_number }}
                                    </a>
                                </td>
                                @if($isAdmin)
                                <td>{{ $invoice->user->name }}</td>
                                @endif
                                <td>{{ $invoice->client->name }}</td>
                                <td>{{ $invoice->invoice_date->format('M d') }}</td>
                                <td>${{ number_format($invoice->total, 2) }}</td>
                                <td>
                                    <span class="badge bg-{{ $invoice->status == 'paid' ? 'success' : ($invoice->status == 'sent' ? 'warning' : 'secondary') }}">
                                        {{ ucfirst($invoice->status) }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="{{ $isAdmin ? '6' : '5' }}" class="text-center">No invoices found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Clients -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Top Clients</h5>
                <a href="{{ route('clients.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                @if($top_clients && $top_clients->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($top_clients as $client)
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">{{ $client->name }}</h6>
                                <small class="text-muted">{{ $client->email }}</small>
                            </div>
                            <div class="text-end">
                                <span class="fw-bold">${{ number_format($client->total_amount ?? 0, 2) }}</span>
                                <div class="small text-muted">Total Spent</div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-people fs-1 text-muted mb-3"></i>
                        <p class="text-muted">No client data available.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Recent Clients (Admin Only) -->
@if($isAdmin && $recent_clients && $recent_clients->count() > 0)
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Clients</h5>
                <a href="{{ route('clients.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Client Name</th>
                                <th>Email</th>
                                <th>Created By</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recent_clients as $client)
                            <tr>
                                <td>{{ $client->name }}</td>
                                <td>{{ $client->email }}</td>
                                <td>{{ $client->user->name }}</td>
                                <td>{{ $client->created_at->format('M d, Y') }}</td>
                                <td>
                                    <a href="{{ route('clients.show', $client) }}" 
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Charts Section -->
<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Invoices by Status</h5>
            </div>
            <div class="card-body">
                <div id="statusChart" style="height: 250px;"></div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Monthly Revenue</h5>
            </div>
            <div class="card-body">
                <div id="revenueChart" style="height: 250px;"></div>
            </div>
        </div>
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
    .stat-card.border-primary { border-left: 4px solid #0d6efd; }
    .stat-card.border-success { border-left: 4px solid #198754; }
    .stat-card.border-warning { border-left: 4px solid #ffc107; }
    .stat-card.border-danger { border-left: 4px solid #dc3545; }
    .stat-card.border-info { border-left: 4px solid #0dcaf0; }
</style>
@endsection

@section('scripts')
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Load data via AJAX
    loadChartsData();
    
    function loadChartsData() {
        // Load invoice statuses chart
        fetch('{{ route("dashboard.invoices-by-status") }}')
            .then(response => response.json())
            .then(data => {
                createStatusChart(data);
            });
        
        // Load monthly revenue chart
        fetch('{{ route("dashboard.monthly-revenue") }}')
            .then(response => response.json())
            .then(data => {
                createRevenueChart(data);
            });
    }
    
    function createStatusChart(data) {
        const ctx = document.getElementById('statusChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: data.labels,
                datasets: [{
                    data: data.data,
                    backgroundColor: data.colors,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }
    
    function createRevenueChart(data) {
        const ctx = document.getElementById('revenueChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.months,
                datasets: [{
                    label: 'Revenue ($)',
                    data: data.revenues,
                    borderColor: '#198754',
                    backgroundColor: 'rgba(25, 135, 84, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value;
                            }
                        }
                    }
                }
            }
        });
    }
    
    // Auto-refresh dashboard data every 5 minutes
    setInterval(loadChartsData, 300000);
});
</script>
@endsection