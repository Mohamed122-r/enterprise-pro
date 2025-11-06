<?php

namespace Tests\Unit;

use App\Models\Contact;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ContactTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_can_be_created()
    {
        $user = User::factory()->create();

        $contact = Contact::factory()->create([
            'assigned_to' => $user->id,
        ]);

        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'email' => $contact->email,
        ]);
    }

    public function test_contact_has_full_name()
    {
        $contact = Contact::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        $this->assertEquals('John Doe', $contact->full_name);
    }

    public function test_contact_belongs_to_assigned_user()
    {
        $user = User::factory()->create();
        $contact = Contact::factory()->create(['assigned_to' => $user->id]);

        $this->assertInstanceOf(User::class, $contact->assignedUser);
        $this->assertEquals($user->id, $contact->assignedUser->id);
    }

    public function test_contact_has_activities()
    {
        $contact = Contact::factory()->create();
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $contact->activities);
    }

    public function test_contact_has_opportunities()
    {
        $contact = Contact::factory()->create();
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $contact->opportunities);
    }

    public function test_contact_status_validation()
    {
        $contact = Contact::factory()->create(['status' => 'lead']);

        $this->assertContains($contact->status, ['lead', 'prospect', 'customer', 'partner']);
    }

    public function test_contact_source_validation()
    {
        $contact = Contact::factory()->create(['source' => 'website']);

        $this->assertContains($contact->source, [
            'website', 'referral', 'social_media', 'event', 'other'
        ]);
    }

    public function test_contact_email_is_unique()
    {
        Contact::factory()->create(['email' => 'test@example.com']);

        $this->expectException(\Illuminate\Database\QueryException::class);
        
        Contact::factory()->create(['email' => 'test@example.com']);
    }

    public function test_contact_can_be_searched()
    {
        $contact1 = Contact::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'company' => 'ABC Corp'
        ]);

        $contact2 = Contact::factory()->create([
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'company' => 'XYZ Inc'
        ]);

        // Search by first name
        $results = Contact::search('John')->get();
        $this->assertCount(1, $results);
        $this->assertEquals('John Doe', $results->first()->full_name);

        // Search by company
        $results = Contact::search('XYZ')->get();
        $this->assertCount(1, $results);
        $this->assertEquals('XYZ Inc', $results->first()->company);
    }

    public function test_contact_can_be_filtered_by_status()
    {
        Contact::factory()->create(['status' => 'lead']);
        Contact::factory()->create(['status' => 'customer']);

        $leads = Contact::where('status', 'lead')->get();
        $customers = Contact::where('status', 'customer')->get();

        $this->assertCount(1, $leads);
        $this->assertCount(1, $customers);
    }
}