<?php

namespace Tests\Unit;

use App\Models\Invoice;
use App\Models\Contact;
use App\Models\InvoiceItem;
use App\Models\Payment;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_can_be_created()
    {
        $contact = Contact::factory()->create();

        $invoice = Invoice::factory()->create([
            'client_id' => $contact->id,
        ]);

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
        ]);
    }

    public function test_invoice_belongs_to_client()
    {
        $contact = Contact::factory()->create();
        $invoice = Invoice::factory()->create(['client_id' => $contact->id]);

        $this->assertInstanceOf(Contact::class, $invoice->client);
        $this->assertEquals($contact->id, $invoice->client->id);
    }

    public function test_invoice_has_items()
    {
        $invoice = Invoice::factory()->create();
        $item = InvoiceItem::factory()->create(['invoice_id' => $invoice->id]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $invoice->items);
        $this->assertCount(1, $invoice->items);
        $this->assertEquals($item->id, $invoice->items->first()->id);
    }

    public function test_invoice_has_payments()
    {
        $invoice = Invoice::factory()->create();
        $payment = Payment::factory()->create(['invoice_id' => $invoice->id]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $invoice->payments);
        $this->assertCount(1, $invoice->payments);
        $this->assertEquals($payment->id, $invoice->payments->first()->id);
    }

    public function test_invoice_calculates_total_paid()
    {
        $invoice = Invoice::factory()->create(['total' => 1000]);
        
        Payment::factory()->create([
            'invoice_id' => $invoice->id,
            'amount' => 300
        ]);
        
        Payment::factory()->create([
            'invoice_id' => $invoice->id,
            'amount' => 200
        ]);

        $this->assertEquals(500, $invoice->total_paid);
    }

    public function test_invoice_calculates_balance()
    {
        $invoice = Invoice::factory()->create(['total' => 1000]);
        
        Payment::factory()->create([
            'invoice_id' => $invoice->id,
            'amount' => 300
        ]);

        $this->assertEquals(700, $invoice->balance);
    }

    public function test_invoice_status_is_updated_based_on_payments()
    {
        $invoice = Invoice::factory()->create([
            'total' => 1000,
            'status' => 'sent'
        ]);

        // Partial payment
        Payment::factory()->create([
            'invoice_id' => $invoice->id,
            'amount' => 500
        ]);

        $invoice->refresh();
        $this->assertEquals('partial', $invoice->status);

        // Full payment
        Payment::factory()->create([
            'invoice_id' => $invoice->id,
            'amount' => 500
        ]);

        $invoice->refresh();
        $this->assertEquals('paid', $invoice->status);
    }

    public function test_invoice_number_is_generated()
    {
        $invoice1 = Invoice::factory()->create();
        $invoice2 = Invoice::factory()->create();

        $this->assertStringStartsWith('INV', $invoice1->invoice_number);
        $this->assertStringStartsWith('INV', $invoice2->invoice_number);
        $this->assertNotEquals($invoice1->invoice_number, $invoice2->invoice_number);
    }

    public function test_invoice_totals_are_calculated_correctly()
    {
        $invoice = Invoice::factory()->create([
            'subtotal' => 1000,
            'tax_rate' => 10,
            'discount' => 5
        ]);

        $taxAmount = 1000 * (10 / 100); // 100
        $discountAmount = 1000 * (5 / 100); // 50
        $expectedTotal = 1000 + $taxAmount - $discountAmount; // 1050

        $this->assertEquals($expectedTotal, $invoice->total);
        $this->assertEquals($taxAmount, $invoice->tax_amount);
        $this->assertEquals($discountAmount, $invoice->discount_amount);
    }

    public function test_overdue_invoices_can_be_queried()
    {
        $overdueInvoice = Invoice::factory()->create([
            'due_date' => now()->subDays(10),
            'status' => 'sent'
        ]);

        $currentInvoice = Invoice::factory()->create([
            'due_date' => now()->addDays(10),
            'status' => 'sent'
        ]);

        $paidInvoice = Invoice::factory()->create([
            'due_date' => now()->subDays(10),
            'status' => 'paid'
        ]);

        $overdueInvoices = Invoice::overdue()->get();

        $this->assertCount(1, $overdueInvoices);
        $this->assertEquals($overdueInvoice->id, $overdueInvoices->first()->id);
    }
}