<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Invoice;
use App\Models\Client;
use App\Models\InvoiceItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class InvoiceController extends Controller
{
    /**
     * Display a listing of invoices
     */
    public function index(Request $request)
    {
        $query = Invoice::query()
            ->with(['client', 'user'])
            ->select('invoices.*');

        if (!Auth::user()->hasPermissionTo('invoice_view_all')) {
            $query->where('invoices.user_id', Auth::id());
        }

        // البحث
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('client', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->has('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->has('date_from')) {
            $query->whereDate('invoice_date', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('invoice_date', '<=', $request->date_to);
        }

        $query->latest();

        $invoices = $query->paginate(10)
            ->withQueryString();

        $statuses = ['draft', 'sent', 'paid', 'cancelled'];
        $clients = Client::query()
            ->when(!Auth::user()->hasPermissionTo('client_view_all'), function ($q) {
                $q->where('user_id', Auth::id());
            })
            ->orderBy('name')
            ->get();

        return view('dashboard.invoices.index', compact(
            'invoices',
            'statuses',
            'clients',
        ));
    }

    /**
     * Show the form for creating a new invoice
     */
    public function create()
    {
        $this->authorize('create', Invoice::class);

        $clients = Client::query()
            ->when(!Auth::user()->hasPermissionTo('client_view_all'), function ($q) {
                $q->where('user_id', Auth::id());
            })
            ->orderBy('name')
            ->get();

        if ($clients->isEmpty()) {
            return redirect()->route('clients.create')
                ->with('warning', 'Please create a client first before creating an invoice.');
        }

        $defaultData = [
            'invoice_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(30)->format('Y-m-d'),
            'tax_rate' => 0,
            'status' => 'draft',
            'items' => [
                [
                    'description' => '',
                    'quantity' => 1,
                    'unit_price' => 0,
                    'total' => 0,
                ]
            ]
        ];

        return view('dashboard.invoices.create', compact('clients', 'defaultData'));
    }

    /**
     * Store a newly created invoice
     */
    public function store(StoreInvoiceRequest $request)
    {
        try {
            $this->authorize('create', Invoice::class);

            $validated = $request->validated();

            // التحقق من أن العميل يتبع المستخدم (ما لم يكن المستخدم إدمن)
            if (!Auth::user()->hasPermissionTo('client_view_all')) {
                $client = Client::findOrFail($validated['client_id']);
                if ($client->user_id !== Auth::id()) {
                    return back()
                        ->withInput()
                        ->with('error', 'You can only create invoices for your own clients.');
                }
            }

            $validated['user_id'] = Auth::id();
            $validated['invoice_number'] = Invoice::generateInvoiceNumber();

            // حساب الإجمالي المبدئي (سيتغير لاحقاً لكن يجب وجود قيمة)
            $validated['total'] = 0; // <-- هذه هي السطر المفقود!

            $invoice = null;

            DB::transaction(function () use ($validated, &$invoice) {
                // إنشاء الفاتورة
                $invoice = Invoice::create($validated);

                // إنشاء عناصر الفاتورة وحساب الإجمالي
                $subtotal = 0;

                foreach ($validated['items'] as $item) {
                    $quantity = (int) $item['quantity'];
                    $unitPrice = (float) $item['unit_price'];
                    $itemTotal = $quantity * $unitPrice;
                    $subtotal += $itemTotal;

                    $invoice->items()->create([
                        'description' => $item['description'],
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'total' => $itemTotal,
                    ]);
                }

                // حساب الضريبة والإجمالي النهائي
                $tax = $subtotal * ($validated['tax_rate'] / 100);
                $total = $subtotal + $tax;

                // تحديث إجمالي الفاتورة
                $invoice->total = $total;
                $invoice->save();

                // تسجيل النشاط
                Log::info('Invoice created', [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'user_id' => Auth::id(),
                    'client_id' => $invoice->client_id,
                    'total' => $invoice->total,
                ]);
            });

            return redirect()->route('invoices.show', $invoice)
                ->with('success', 'Invoice created successfully.');
        } catch (\Exception $e) {
            Log::error('Invoice creation failed: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'client_id' => $request->client_id ?? null,
                'error_trace' => $e->getTraceAsString(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Failed to create invoice: ' . $e->getMessage());
        }
    }
    /**
     * Display the specified invoice
     */
    public function show(Invoice $invoice)
    {
        $this->authorize('view', $invoice);

        $invoice->load(['client', 'items', 'user']);

        $isOwner = $invoice->user_id === Auth::id();
        $isAdmin = Auth::user()->hasRole('admin');

        Log::info('Invoice viewed', [
            'invoice_id' => $invoice->id,
            'user_id' => Auth::id(),
            'is_owner' => $isOwner,
            'is_admin' => $isAdmin,
        ]);

        return view('dashboard.invoices.show', compact('invoice', 'isOwner', 'isAdmin'));
    }

    /**
     * Show the form for editing the specified invoice
     */
    public function edit(Invoice $invoice)
    {
        $this->authorize('update', $invoice);

        if (in_array($invoice->status, ['paid', 'cancelled'])) {
            return redirect()->route('invoices.show', $invoice)
                ->with('error', 'Cannot edit a paid or cancelled invoice.');
        }

        $clients = Client::query()
            ->when(!Auth::user()->hasPermissionTo('client_view_all'), function ($q) {
                $q->where('user_id', Auth::id());
            })
            ->orderBy('name')
            ->get();

        $invoice->load('items');

        return view('dashboard.invoices.edit', compact('invoice', 'clients'));
    }

    /**
     * Update the specified invoice
     */
    public function update(UpdateInvoiceRequest $request, Invoice $invoice)
    {
        try {

            if (in_array($invoice->status, ['paid', 'cancelled'])) {
                throw new \Exception('Cannot update a paid or cancelled invoice.');
            }

            $validated = $request->validated();

            DB::transaction(function () use ($invoice, $validated) {
                $originalData = $invoice->toArray();

                $subtotal = 0;
                $invoice->items()->delete();

                foreach ($validated['items'] as $item) {
                    $quantity = (int) $item['quantity'];
                    $unitPrice = (float) $item['unit_price'];
                    $itemTotal = $quantity * $unitPrice;
                    $subtotal += $itemTotal;

                    $invoice->items()->create([
                        'description' => $item['description'],
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'total' => $itemTotal,
                    ]);
                }

                $tax = $subtotal * ($validated['tax_rate'] / 100);
                $total = $subtotal + $tax;

                $invoice->update([
                    'client_id' => $validated['client_id'],
                    'invoice_date' => $validated['invoice_date'],
                    'due_date' => $validated['due_date'],
                    'notes' => $validated['notes'] ?? null,
                    'tax_rate' => $validated['tax_rate'],
                    'status' => $validated['status'],
                    'total' => $total,
                ]);

                Log::info('Invoice updated', [
                    'invoice_id' => $invoice->id,
                    'user_id' => Auth::id(),
                    'is_admin' => Auth::user()->hasRole('admin'),
                    'old_client_id' => $originalData['client_id'],
                    'new_client_id' => $validated['client_id'],
                    'old_total' => $originalData['total'],
                    'new_total' => $total,
                ]);
            });

            return redirect()->route('invoices.show', $invoice)
                ->with('success', 'Invoice updated successfully.');
        } catch (\Exception $e) {
            Log::error('Invoice update failed: ' . $e->getMessage(), [
                'invoice_id' => $invoice->id,
                'user_id' => Auth::id(),
                'is_admin' => Auth::user()->hasRole('admin'),
                'error_trace' => $e->getTraceAsString(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Failed to update invoice: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified invoice
     */
    public function destroy(Invoice $invoice)
    {
        $this->authorize('delete', $invoice);

        try {
            $invoiceNumber = $invoice->invoice_number;
            $clientName = $invoice->client->name;

            $invoice->items()->delete();
            $invoice->delete();

            Log::info('Invoice deleted', [
                'invoice_number' => $invoiceNumber,
                'client_name' => $clientName,
                'user_id' => Auth::id(),
            ]);

            return redirect()->route('invoices.index')
                ->with('success', 'Invoice deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Invoice deletion failed: ' . $e->getMessage(), [
                'invoice_id' => $invoice->id,
                'user_id' => Auth::id(),
            ]);

            return back()
                ->with('error', 'Failed to delete invoice: ' . $e->getMessage());
        }
    }

    /**
     * Download PDF version of invoice
     */
    public function downloadPDF(Invoice $invoice)
    {
        $this->authorize('download', $invoice);

        try {
            $invoice->load(['client', 'items', 'user']);

            $pdf = PDF::loadView('dashboard.invoices.pdf', compact('invoice'))
                ->setPaper('a4', 'portrait')
                ->setOptions([
                    'defaultFont' => 'sans-serif',
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled' => true,
                ]);

            Log::info('Invoice PDF downloaded', [
                'invoice_id' => $invoice->id,
                'user_id' => Auth::id(),
            ]);

            return $pdf->download("invoice-{$invoice->invoice_number}.pdf");
        } catch (\Exception $e) {
            Log::error('PDF generation failed: ' . $e->getMessage(), [
                'invoice_id' => $invoice->id,
                'user_id' => Auth::id(),
            ]);

            return back()
                ->with('error', 'Failed to generate PDF: ' . $e->getMessage());
        }
    }

    /**
     * Send invoice (mark as sent)
     */
    public function send(Invoice $invoice)
    {
        $this->authorize('update', $invoice);

        if ($invoice->status !== 'draft') {
            return redirect()->back()
                ->with('error', 'Only draft invoices can be sent.');
        }

        $invoice->update(['status' => 'sent']);

        Log::info('Invoice sent', [
            'invoice_id' => $invoice->id,
            'user_id' => Auth::id(),
        ]);

        return redirect()->back()
            ->with('success', 'Invoice marked as sent.');
    }

    /**
     * Mark invoice as paid
     */
    public function markAsPaid(Invoice $invoice)
    {
        $this->authorize('update', $invoice);

        if ($invoice->status !== 'sent') {
            return redirect()->back()
                ->with('error', 'Only sent invoices can be marked as paid.');
        }

        $invoice->update(['status' => 'paid']);

        Log::info('Invoice marked as paid', [
            'invoice_id' => $invoice->id,
            'user_id' => Auth::id(),
            'amount' => $invoice->total,
        ]);

        return redirect()->back()
            ->with('success', 'Invoice marked as paid.');
    }
}
