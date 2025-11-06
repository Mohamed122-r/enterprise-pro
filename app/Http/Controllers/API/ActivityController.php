<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $activities = Activity::with(['assignedUser', 'contact', 'opportunity'])
                ->when($request->user_id, function($q, $userId) {
                    $q->where('assigned_to', $userId);
                })
                ->when($request->type, function($q, $type) {
                    $q->where('type', $type);
                })
                ->when($request->status, function($q, $status) {
                    $q->where('status', $status);
                })
                ->orderBy('due_date', 'asc')
                ->paginate(20);

            return response()->json([
                'data' => $activities->items(),
                'meta' => [
                    'current_page' => $activities->currentPage(),
                    'last_page' => $activities->lastPage(),
                    'total' => $activities->total(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch activities',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'type' => 'required|in:call,meeting,email,task,note',
                'due_date' => 'required|date',
                'contact_id' => 'nullable|exists:contacts,id',
                'opportunity_id' => 'nullable|exists:opportunities,id',
                'assigned_to' => 'required|exists:users,id',
                'priority' => 'required|in:low,medium,high',
            ]);

            $activity = Activity::create($request->all());

            return response()->json([
                'message' => 'Activity created successfully',
                'data' => $activity->load(['assignedUser', 'contact'])
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create activity',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, Activity $activity): JsonResponse
    {
        try {
            $request->validate([
                'title' => 'sometimes|string|max:255',
                'type' => 'sometimes|in:call,meeting,email,task,note',
                'due_date' => 'sometimes|date',
                'status' => 'sometimes|in:pending,in_progress,completed,cancelled',
                'priority' => 'sometimes|in:low,medium,high',
            ]);

            $activity->update($request->all());

            return response()->json([
                'message' => 'Activity updated successfully',
                'data' => $activity->load(['assignedUser', 'contact'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update activity',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function complete(Activity $activity): JsonResponse
    {
        try {
            $activity->update([
                'status' => 'completed',
                'completed_at' => now()
            ]);

            return response()->json([
                'message' => 'Activity marked as completed',
                'data' => $activity
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to complete activity',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}