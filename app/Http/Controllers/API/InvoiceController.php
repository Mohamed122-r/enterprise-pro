<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\InvoiceRequest;
use App\Models\Invoice;
use App\Services\InvoiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    protected $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $invoices = $this->invoiceService->getInvoicesWithFilters($request->all());
            
            return response()->json([
                'data' => $invoices->items(),
                'meta' => [
                    'current_page' => $invoices->currentPage(),
                    'last_page' => $invoices->lastPage(),
                    'total' => $invoices->total(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch invoices',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(InvoiceRequest $request): JsonResponse
    {
        try {
            $invoice = $this->invoiceService->createInvoice($request->validated());
            
            return response()->json([
                'message' => 'Invoice created successfully',
                'data' => $invoice
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create invoice',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Invoice $invoice): JsonResponse
    {
        try {
            return response()->json([
                'data' => $invoice->load(['items', 'payments', 'client'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch invoice',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(InvoiceRequest $request, Invoice $invoice): JsonResponse
    {
        try {
            $updatedInvoice = $this->invoiceService->updateInvoice($invoice, $request->validated());
            
            return response()->json([
                'message' => 'Invoice updated successfully',
                'data' => $updatedInvoice
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update invoice',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Invoice $invoice): JsonResponse
    {
        try {
            $invoice->delete();
            
            return response()->json([
                'message' => 'Invoice deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete invoice',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function sendInvoice(Invoice $invoice): JsonResponse
    {
        try {
            $this->invoiceService->sendInvoice($invoice);
            
            return response()->json([
                'message' => 'Invoice sent successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to send invoice',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function recordPayment(Invoice $invoice, Request $request): JsonResponse
    {
        try {
            $request->validate([
                'amount' => 'required|numeric|min:0.01',
                'payment_method' => 'required|string',
                'payment_date' => 'required|date',
                'reference' => 'nullable|string',
                'notes' => 'nullable|string'
            ]);

            $this->invoiceService->recordPayment($invoice, $request->all());
            
            return response()->json([
                'message' => 'Payment recorded successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to record payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}