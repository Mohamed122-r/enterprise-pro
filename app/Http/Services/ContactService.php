<?php

namespace App\Services;

use App\Models\Contact;
use Illuminate\Support\Facades\DB;

class ContactService
{
    public function createContact(array $data): Contact
    {
        return DB::transaction(function () use ($data) {
            $contact = Contact::create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'company' => $data['company'] ?? null,
                'position' => $data['position'] ?? null,
                'status' => $data['status'] ?? 'lead',
                'source' => $data['source'] ?? 'website',
                'assigned_to' => $data['assigned_to'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            return $contact->load(['assignedUser', 'activities']);
        });
    }

    public function updateContact(Contact $contact, array $data): Contact
    {
        return DB::transaction(function () use ($contact, $data) {
            $contact->update($data);
            return $contact->load(['assignedUser', 'activities']);
        });
    }

    public function deleteContact(Contact $contact): bool
    {
        return DB::transaction(function () use ($contact) {
            // Delete related activities first
            $contact->activities()->delete();
            return $contact->delete();
        });
    }

    public function getContactsWithFilters(array $filters = [])
    {
        $query = Contact::with(['assignedUser', 'activities']);

        if (isset($filters['search']) && $filters['search']) {
            $query->where(function ($q) use ($filters) {
                $q->where('first_name', 'like', "%{$filters['search']}%")
                  ->orWhere('last_name', 'like', "%{$filters['search']}%")
                  ->orWhere('email', 'like', "%{$filters['search']}%")
                  ->orWhere('company', 'like', "%{$filters['search']}%");
            });
        }

        if (isset($filters['status']) && $filters['status']) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['assigned_to']) && $filters['assigned_to']) {
            $query->where('assigned_to', $filters['assigned_to']);
        }

        if (isset($filters['source']) && $filters['source']) {
            $query->where('source', $filters['source']);
        }

        return $query->orderBy('created_at', 'desc')->paginate(15);
    }
}