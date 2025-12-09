<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Invoice;
use Carbon\Carbon;

class StoreInvoiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Invoice::class);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'client_id' => 'required|exists:clients,id',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'notes' => 'nullable|string|max:1000',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'status' => 'required|in:draft,sent,paid,cancelled',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (!$this->user()->hasPermissionTo('client_view_all')) {
                $this->validateClientOwnership($validator);
            }

            $this->validateDates($validator);
        });
    }

    /**
     * Validate client ownership
     */
    private function validateClientOwnership($validator): void
    {
        if ($this->user()->hasPermissionTo('client_view_all')) {
            return;
        }

        if ($this->filled('client_id')) {
            $client = \App\Models\Client::find($this->client_id);

            if (!$client) {
                $validator->errors()->add(
                    'client_id',
                    'The selected client does not exist.'
                );
            } elseif ($client->user_id !== $this->user()->id) {
                $validator->errors()->add(
                    'client_id',
                    'The selected client does not belong to your account.'
                );
            }
        }
    }

    /**
     * Validate dates
     */
    private function validateDates($validator): void
    {
        if ($this->filled(['invoice_date', 'due_date'])) {
            $invoiceDate = Carbon::parse($this->invoice_date);
            $dueDate = Carbon::parse($this->due_date);

            if ($invoiceDate->isFuture()) {
                $validator->errors()->add(
                    'invoice_date',
                    'Invoice date cannot be in the future.'
                );
            }

            if ($dueDate->diffInDays($invoiceDate) > 365) {
                $validator->errors()->add(
                    'due_date',
                    'Due date cannot be more than 1 year after invoice date.'
                );
            }
        }
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'tax_rate' => (float) ($this->tax_rate ?? 0),
        ]);
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'client_id.required' => 'Please select a client.',
            'client_id.exists' => 'The selected client does not exist.',
            'invoice_date.required' => 'Invoice date is required.',
            'due_date.required' => 'Due date is required.',
            'due_date.after_or_equal' => 'Due date must be on or after invoice date.',
            'items.required' => 'At least one item is required.',
            'items.*.description.required' => 'Item description is required.',
            'items.*.quantity.min' => 'Quantity must be at least 1.',
            'items.*.unit_price.min' => 'Unit price must be a positive number.',
            'status.required' => 'Status is required.',
            'status.in' => 'Invalid status selected.',
        ];
    }
}
