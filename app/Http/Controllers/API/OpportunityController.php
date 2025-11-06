<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Opportunity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OpportunityController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $opportunities = Opportunity::with(['contact', 'assignedUser', 'activities'])
                ->when($request->stage, function($q, $stage) {
                    $q->where('stage', $stage);
                })
                ->when($request->assigned_to, function($q, $assignedTo) {
                    $q->where('assigned_to', $assignedTo);
                })
                ->orderBy('created_at', 'desc')
                ->paginate(15);

            return response()->json([
                'data' => $opportunities->items(),
                'meta' => [
                    'current_page' => $opportunities->currentPage(),
                    'last_page' => $opportunities->lastPage(),
                    'total' => $opportunities->total(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch opportunities',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'contact_id' => 'required|exists:contacts,id',
                'value' => 'required|numeric|min:0',
                'stage' => 'required|in:prospect,qualification,proposal,negotiation,closed_won,closed_lost',
                'probability' => 'required|integer|min:0|max:100',
                'expected_close_date' => 'required|date',
                'assigned_to' => 'required|exists:users,id',
            ]);

            $opportunity = Opportunity::create($request->all());

            return response()->json([
                'message' => 'Opportunity created successfully',
                'data' => $opportunity->load(['contact', 'assignedUser'])
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create opportunity',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, Opportunity $opportunity): JsonResponse
    {
        try {
            $request->validate([
                'title' => 'sometimes|string|max:255',
                'value' => 'sometimes|numeric|min:0',
                'stage' => 'sometimes|in:prospect,qualification,proposal,negotiation,closed_won,closed_lost',
                'probability' => 'sometimes|integer|min:0|max:100',
                'expected_close_date' => 'sometimes|date',
            ]);

            $opportunity->update($request->all());

            return response()->json([
                'message' => 'Opportunity updated successfully',
                'data' => $opportunity->load(['contact', 'assignedUser'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update opportunity',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Opportunity $opportunity): JsonResponse
    {
        try {
            $opportunity->delete();
            
            return response()->json([
                'message' => 'Opportunity deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete opportunity',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}