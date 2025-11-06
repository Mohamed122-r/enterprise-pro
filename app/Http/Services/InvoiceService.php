<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    public function createInvoice(array $data): Invoice
    {
        return DB::transaction(function () use ($data) {
            $invoice = Invoice::create([
                'invoice_number' => $this->generateInvoiceNumber(),
                'client_id' => $data['client_id'],
                'client_name' => $data['client_name'],
                'client_email' => $data['client_email'],
                'client_phone' => $data['client_phone'] ?? null,
                'issue_date' => $data['issue_date'],
                'due_date' => $data['due_date'],
                'status' => 'draft',
                'tax_rate' => $data['tax_rate'] ?? 0,
                'discount' => $data['discount'] ?? 0,
                'notes' => $data['notes'] ?? null,
                'terms' => $data['terms'] ?? null,
            ]);

            // Create invoice items
            $subtotal = 0;
            foreach ($data['items'] as $item) {
                $invoiceItem = InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total' => $item['quantity'] * $item['unit_price'],
                ]);

                $subtotal += $invoiceItem->total;
            }

            // Calculate totals
            $taxAmount = $subtotal * ($invoice->tax_rate / 100);
            $discountAmount = $subtotal * ($invoice->discount / 100);
            $total = $subtotal + $taxAmount - $discountAmount;

            $invoice->update([
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discountAmount,
                'total' => $total,
            ]);

            return $invoice->load(['items', 'client', 'payments']);
        });
    }

    public function updateInvoice(Invoice $invoice, array $data): Invoice
    {
        return DB::transaction(function () use ($invoice, $data) {
            $invoice->update([
                'client_name' => $data['client_name'],
                'client_email' => $data['client_email'],
                'client_phone' => $data['client_phone'] ?? null,
                'issue_date' => $data['issue_date'],
                'due_date' => $data['due_date'],
                'tax_rate' => $data['tax_rate'] ?? 0,
                'discount' => $data['discount'] ?? 0,
                'notes' => $data['notes'] ?? null,
                'terms' => $data['terms'] ?? null,
            ]);

            // Delete existing items and create new ones
            $invoice->items()->delete();

            $subtotal = 0;
            foreach ($data['items'] as $item) {
                $invoiceItem = InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total' => $item['quantity'] * $item['unit_price'],
                ]);

                $subtotal += $invoiceItem->total;
            }

            // Recalculate totals
            $taxAmount = $subtotal * ($invoice->tax_rate / 100);
            $discountAmount = $subtotal * ($invoice->discount / 100);
            $total = $subtotal + $taxAmount - $discountAmount;

            $invoice->update([
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discountAmount,
                'total' => $total,
            ]);

            return $invoice->load(['items', 'client', 'payments']);
        });
    }

    public function sendInvoice(Invoice $invoice): bool
    {
        return DB::transaction(function () use ($invoice) {
            if ($invoice->status !== 'draft') {
                throw new \Exception('Only draft invoices can be sent');
            }

            $invoice->update([
                'status' => 'sent',
                'sent_at' => now(),
            ]);

            // TODO: Send email to client
            // Mail::to($invoice->client_email)->send(new InvoiceSent($invoice));

            return true;
        });
    }

    public function recordPayment(Invoice $invoice, array $paymentData): bool
    {
        return DB::transaction(function () use ($invoice, $paymentData) {
            $payment = $invoice->payments()->create([
                'amount' => $paymentData['amount'],
                'payment_method' => $paymentData['payment_method'],
                'payment_date' => $paymentData['payment_date'],
                'reference' => $paymentData['reference'] ?? null,
                'notes' => $paymentData['notes'] ?? null,
            ]);

            // Update invoice status based on payments
            $totalPaid = $invoice->payments()->sum('amount');
            
            if ($totalPaid >= $invoice->total) {
                $invoice->update(['status' => 'paid']);
            } elseif ($totalPaid > 0) {
                $invoice->update(['status' => 'partial']);
            }

            return true;
        });
    }

    private function generateInvoiceNumber(): string
    {
        $latest = Invoice::orderBy('id', 'desc')->first();
        $number = $latest ? intval(substr($latest->invoice_number, 3)) + 1 : 1;
        return 'INV' . str_pad($number, 6, '0', STR_PAD_LEFT);
    }

    public function getInvoicesWithFilters(array $filters = [])
    {
        $query = Invoice::with(['client', 'payments']);

        if (isset($filters['status']) && $filters['status']) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['client_id']) && $filters['client_id']) {
            $query->where('client_id', $filters['client_id']);
        }

        if (isset($filters['date_from']) && $filters['date_from']) {
            $query->where('issue_date', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to']) && $filters['date_to']) {
            $query->where('issue_date', '<=', $filters['date_to']);
        }

        if (isset($filters['search']) && $filters['search']) {
            $query->where(function ($q) use ($filters) {
                $q->where('invoice_number', 'like', "%{$filters['search']}%")
                  ->orWhere('client_name', 'like', "%{$filters['search']}%")
                  ->orWhere('client_email', 'like', "%{$filters['search']}%");
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate(15);
    }
}