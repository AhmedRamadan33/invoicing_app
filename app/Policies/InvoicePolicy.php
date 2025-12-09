<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Invoice;

class InvoicePolicy
{
    public function viewAny(User $user): bool
    {
        // check if the user has permission to view invoices
        return $user->hasPermissionTo('invoice_view');
    }

    public function view(User $user, Invoice $invoice): bool
    {
        // the admin can view any invoice
        if ($user->hasPermissionTo('invoice_view_all')) {
            return true;
        }

        // the regular user can only view their own invoices
        return $user->id === $invoice->user_id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('invoice_create');
    }

    public function update(User $user, Invoice $invoice): bool
    {
        // the admin can edit any invoice
        if ($user->hasPermissionTo('invoice_view_all')) {
            return $user->hasPermissionTo('invoice_edit');
        }

        // the regular user can only edit their own invoices
        return $user->id === $invoice->user_id && $user->hasPermissionTo('invoice_edit');
    }

    public function delete(User $user, Invoice $invoice): bool
    {
        // the admin can delete any invoice
        if ($user->hasPermissionTo('invoice_view_all')) {
            return $user->hasPermissionTo('invoice_delete');
        }

        // the regular user can only delete their own invoices
        return $user->id === $invoice->user_id && $user->hasPermissionTo('invoice_delete');
    }

    public function download(User $user, Invoice $invoice): bool
    {
        // the admin can download any invoice
        if ($user->hasPermissionTo('invoice_view_all')) {
            return $user->hasPermissionTo('invoice_pdf_generate');
        }

        // the regular user can only download their own invoices
        return $user->id === $invoice->user_id && $user->hasPermissionTo('invoice_pdf_generate');
    }
}
