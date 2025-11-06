<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Leave;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeaveController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $leaves = Leave::with(['user', 'approver'])
                ->when($request->user_id, function($q, $userId) {
                    $q->where('user_id', $userId);
                })
                ->when($request->status, function($q, $status) {
                    $q->where('status', $status);
                })
                ->when($request->type, function($q, $type) {
                    $q->where('type', $type);
                })
                ->orderBy('created_at', 'desc')
                ->paginate(15);

            return response()->json([
                'data' => $leaves->items(),
                'meta' => [
                    'current_page' => $leaves->currentPage(),
                    'last_page' => $leaves->lastPage(),
                    'total' => $leaves->total(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch leaves',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'type' => 'required|in:sick,vacation,personal,emergency,other',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'reason' => 'required|string|max:500',
            ]);

            $leave = Leave::create([
                ...$request->all(),
                'user_id' => $request->user()->id,
                'status' => 'pending'
            ]);

            return response()->json([
                'message' => 'Leave request submitted successfully',
                'data' => $leave->load('user')
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to submit leave request',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function approve(Leave $leave, Request $request): JsonResponse
    {
        try {
            if (!$request->user()->hasPermissionTo('leaves.approve')) {
                return response()->json(['message' => 'Forbidden'], 403);
            }

            $leave->update([
                'status' => 'approved',
                'approved_by' => $request->user()->id,
                'approved_at' => now()
            ]);

            return response()->json([
                'message' => 'Leave request approved',
                'data' => $leave->load(['user', 'approver'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to approve leave',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function reject(Leave $leave, Request $request): JsonResponse
    {
        try {
            if (!$request->user()->hasPermissionTo('leaves.approve')) {
                return response()->json(['message' => 'Forbidden'], 403);
            }

            $request->validate(['notes' => 'required|string|max:500']);

            $leave->update([
                'status' => 'rejected',
                'approved_by' => $request->user()->id,
                'approved_at' => now(),
                'notes' => $request->notes
            ]);

            return response()->json([
                'message' => 'Leave request rejected',
                'data' => $leave
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to reject leave',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}