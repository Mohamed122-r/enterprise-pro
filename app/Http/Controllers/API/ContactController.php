<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContactRequest;
use App\Models\Contact;
use App\Services\ContactService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    protected $contactService;

    public function __construct(ContactService $contactService)
    {
        $this->contactService = $contactService;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $contacts = $this->contactService->getContactsWithFilters($request->all());
            
            return response()->json([
                'data' => $contacts->items(),
                'meta' => [
                    'current_page' => $contacts->currentPage(),
                    'last_page' => $contacts->lastPage(),
                    'total' => $contacts->total(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch contacts',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(ContactRequest $request): JsonResponse
    {
        try {
            $contact = $this->contactService->createContact($request->validated());
            
            return response()->json([
                'message' => 'Contact created successfully',
                'data' => $contact
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create contact',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Contact $contact): JsonResponse
    {
        try {
            return response()->json([
                'data' => $contact->load(['activities', 'opportunities'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch contact',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(ContactRequest $request, Contact $contact): JsonResponse
    {
        try {
            $updatedContact = $this->contactService->updateContact($contact, $request->validated());
            
            return response()->json([
                'message' => 'Contact updated successfully',
                'data' => $updatedContact
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update contact',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Contact $contact): JsonResponse
    {
        try {
            $this->contactService->deleteContact($contact);
            
            return response()->json([
                'message' => 'Contact deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete contact',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}