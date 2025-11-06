<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Invoice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $payments = Payment::with(['invoice', 'invoice.client'])
                ->when($request->invoice_id, function($q, $invoiceId) {
                    $q->where('invoice_id', $invoiceId);
                })
                ->when($request->payment_method, function($q, $method) {
                    $q->where('payment_method', $method);
                })
                ->when($request->date_from, function($q, $dateFrom) {
                    $q->where('payment_date', '>=', $dateFrom);
                })
                ->when($request->date_to, function($q, $dateTo) {
                    $q->where('payment_date', '<=', $dateTo);
                })
                ->orderBy('payment_date', 'desc')
                ->paginate(20);

            return response()->json([
                'data' => $payments->items(),
                'meta' => [
                    'current_page' => $payments->currentPage(),
                    'last_page' => $payments->lastPage(),
                    'total' => $payments->total(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch payments',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'invoice_id' => 'required|exists:invoices,id',
                'amount' => 'required|numeric|min:0.01',
                'payment_method' => 'required|in:cash,card,bank_transfer,check,digital',
                'payment_date' => 'required|date',
                'reference' => 'nullable|string|max:255',
                'notes' => 'nullable|string',
            ]);

            $invoice = Invoice::findOrFail($request->invoice_id);

            // Check if payment amount exceeds invoice balance
            $totalPaid = $invoice->payments()->sum('amount');
            $remainingBalance = $invoice->total - $totalPaid;

            if ($request->amount > $remainingBalance) {
                return response()->json([
                    'message' => 'Payment amount exceeds invoice balance',
                    'remaining_balance' => $remainingBalance
                ], 422);
            }

            $payment = Payment::create($request->all());

            // Update invoice status
            $newTotalPaid = $totalPaid + $request->amount;
            
            if ($newTotalPaid >= $invoice->total) {
                $invoice->update([
                    'status' => 'paid',
                    'paid_at' => now()
                ]);
            } elseif ($newTotalPaid > 0) {
                $invoice->update(['status' => 'partial']);
            }

            return response()->json([
                'message' => 'Payment recorded successfully',
                'data' => $payment->load(['invoice', 'invoice.client'])
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to record payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Payment $payment): JsonResponse
    {
        try {
            return response()->json([
                'data' => $payment->load(['invoice', 'invoice.client', 'invoice.items'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Payment $payment): JsonResponse
    {
        try {
            $invoice = $payment->invoice;
            
            $payment->delete();

            // Recalculate invoice status
            $totalPaid = $invoice->payments()->sum('amount');
            
            if ($totalPaid >= $invoice->total) {
                $invoice->update(['status' => 'paid']);
            } elseif ($totalPaid > 0) {
                $invoice->update(['status' => 'partial']);
            } else {
                $invoice->update(['status' => 'sent']);
            }

            return response()->json([
                'message' => 'Payment deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}