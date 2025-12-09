<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Invoice;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    /**
     * Display dashboard with stats and recent invoices
     */
    public function index()
    {
        $user = Auth::user();
        
        $isAdmin = $user->hasRole('admin');
        
        $stats = $this->getDashboardStats($user, $isAdmin);
        
        $recent_invoices = $this->getRecentInvoices($user, $isAdmin);
        
        $recent_clients = $isAdmin ? $this->getRecentClients() : null;
        
        $top_clients = $this->getTopClients($user, $isAdmin);
        
        $invoice_statuses = $this->getInvoiceStatuses($user, $isAdmin);
        
        return view('dashboard', compact(
            'stats', 
            'recent_invoices', 
            'recent_clients',
            'top_clients',
            'invoice_statuses',
            'isAdmin'
        ));
    }
    
    /**
     * Get dashboard statistics based on user role
     */
    private function getDashboardStats(User $user, bool $isAdmin): array
    {
        if ($isAdmin) {
            return [
                'total_invoices' => Invoice::count(),
                'total_clients' => Client::count(),
                'total_users' => User::count(),
                'paid_invoices' => Invoice::where('status', 'paid')->count(),
                'pending_invoices' => Invoice::whereIn('status', ['sent', 'draft'])->count(),
                'overdue_invoices' => Invoice::where('status', 'overdue')->count(),
                'total_revenue' => Invoice::where('status', 'paid')->sum('total'),
                'average_invoice' => Invoice::where('status', 'paid')->avg('total') ?? 0,
            ];
        } else {
            return [
                'total_invoices' => $user->invoices()->count(),
                'total_clients' => $user->clients()->count(),
                'paid_invoices' => $user->invoices()->where('status', 'paid')->count(),
                'pending_invoices' => $user->invoices()->where('status', 'sent')->count(),
                'overdue_invoices' => $user->invoices()->where('status', 'overdue')->count(),
                'total_revenue' => $user->invoices()->where('status', 'paid')->sum('total'),
                'average_invoice' => $user->invoices()->where('status', 'paid')->avg('total') ?? 0,
            ];
        }
    }
    
    /**
     * Get recent invoices based on user role
     */
    private function getRecentInvoices(User $user, bool $isAdmin)
    {
        $query = $isAdmin ? Invoice::query() : $user->invoices();
        
        return $query->with(['client', 'user'])
            ->latest()
            ->take(5)
            ->get();
    }
    
    /**
     * Get recent clients (admin only)
     */
    private function getRecentClients()
    {
        return Client::with('user')
            ->latest()
            ->take(5)
            ->get();
    }
    
    /**
     * Get top clients based on invoice totals
     */
    private function getTopClients(User $user, bool $isAdmin)
    {
        if ($isAdmin) {
            return Client::withSum(['invoices as total_amount' => function($query) {
                $query->where('status', 'paid');
            }], 'total')
                ->whereHas('invoices', function($query) {
                    $query->where('status', 'paid');
                })
                ->orderByDesc('total_amount')
                ->take(5)
                ->get();
        } else {
            return $user->clients()
                ->withSum(['invoices as total_amount' => function($query) use ($user) {
                    $query->where('status', 'paid')->where('user_id', $user->id);
                }], 'total')
                ->whereHas('invoices', function($query) use ($user) {
                    $query->where('status', 'paid')->where('user_id', $user->id);
                })
                ->orderByDesc('total_amount')
                ->take(5)
                ->get();
        }
    }
    
    /**
     * Get invoice status distribution
     */
    private function getInvoiceStatuses(User $user, bool $isAdmin): array
    {
        $statuses = ['draft', 'sent', 'paid', 'overdue', 'cancelled'];
        $result = [];
        
        foreach ($statuses as $status) {
            $query = $isAdmin ? Invoice::query() : $user->invoices();
            $count = $query->where('status', $status)->count();
            
            if ($count > 0) {
                $result[$status] = $count;
            }
        }
        
        return $result;
    }
    
    /**
     * Get dashboard data for API/AJAX requests
     */
    public function getDashboardData(Request $request)
    {
        $user = Auth::user();
        $isAdmin = $user->hasRole('admin');
        
        $data = [
            'stats' => $this->getDashboardStats($user, $isAdmin),
            'recent_invoices' => $this->getRecentInvoices($user, $isAdmin),
            'invoice_statuses' => $this->getInvoiceStatuses($user, $isAdmin),
        ];
        
        if ($isAdmin) {
            $data['recent_clients'] = $this->getRecentClients();
            $data['recent_users'] = User::latest()->take(5)->get();
        }
        
        return response()->json($data);
    }
    
    /**
     * Get monthly revenue data for charts
     */
    public function getMonthlyRevenue(Request $request)
    {
        $user = Auth::user();
        $isAdmin = $user->hasRole('admin');
        
        $months = [];
        $revenues = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthName = $month->format('M');
            $startDate = $month->copy()->startOfMonth();
            $endDate = $month->copy()->endOfMonth();
            
            $query = $isAdmin ? Invoice::query() : $user->invoices();
            $revenue = $query->where('status', 'paid')
                ->whereBetween('invoice_date', [$startDate, $endDate])
                ->sum('total');
            
            $months[] = $monthName;
            $revenues[] = (float) $revenue;
        }
        
        return response()->json([
            'months' => $months,
            'revenues' => $revenues,
        ]);
    }
    
    /**
     * Get invoices by status for charts
     */
    public function getInvoicesByStatus()
    {
        $user = Auth::user();
        $isAdmin = $user->hasRole('admin');
        
        $statuses = ['draft', 'sent', 'paid', 'overdue'];
        $labels = [];
        $data = [];
        $colors = ['#6c757d', '#ffc107', '#198754', '#dc3545'];
        
        foreach ($statuses as $index => $status) {
            $query = $isAdmin ? Invoice::query() : $user->invoices();
            $count = $query->where('status', $status)->count();
            
            if ($count > 0) {
                $labels[] = ucfirst($status);
                $data[] = $count;
            }
        }
        
        return response()->json([
            'labels' => $labels,
            'data' => $data,
            'colors' => array_slice($colors, 0, count($labels)),
        ]);
    }
}