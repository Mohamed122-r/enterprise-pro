<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\AttendanceRequest;
use App\Models\Attendance;
use App\Services\AttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    protected $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $attendances = $this->attendanceService->getAttendancesWithFilters($request->all());
            
            return response()->json([
                'data' => $attendances->items(),
                'meta' => [
                    'current_page' => $attendances->currentPage(),
                    'last_page' => $attendances->lastPage(),
                    'total' => $attendances->total(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch attendances',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(AttendanceRequest $request): JsonResponse
    {
        try {
            $attendance = $this->attendanceService->createAttendance($request->validated());
            
            return response()->json([
                'message' => 'Attendance recorded successfully',
                'data' => $attendance
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to record attendance',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function checkIn(Request $request): JsonResponse
    {
        try {
            $attendance = $this->attendanceService->checkIn($request->user());
            
            return response()->json([
                'message' => 'Check-in successful',
                'data' => $attendance
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to check in',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function checkOut(Request $request): JsonResponse
    {
        try {
            $attendance = $this->attendanceService->checkOut($request->user());
            
            return response()->json([
                'message' => 'Check-out successful',
                'data' => $attendance
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to check out',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Attendance $attendance): JsonResponse
    {
        try {
            return response()->json([
                'data' => $attendance->load(['user', 'user.department'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch attendance',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}