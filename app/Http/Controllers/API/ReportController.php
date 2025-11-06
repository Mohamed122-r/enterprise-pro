<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    protected $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function salesReport(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
            ]);

            $report = $this->reportService->getSalesReport(
                $request->start_date,
                $request->end_date
            );

            return response()->json([
                'data' => $report,
                'meta' => [
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date,
                    'period' => $this->getPeriodLabel($request->start_date, $request->end_date),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to generate sales report',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function attendanceReport(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'month' => 'required|integer|between:1,12',
                'year' => 'required|integer|min:2020',
            ]);

            $report = $this->reportService->getAttendanceReport(
                $request->month,
                $request->year
            );

            return response()->json([
                'data' => $report,
                'meta' => [
                    'month' => $request->month,
                    'year' => $request->year,
                    'month_name' => date('F', mktime(0, 0, 0, $request->month, 1)),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to generate attendance report',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function userPerformance(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'user_id' => 'required|exists:users,id',
                'start_date' => 'required|date',
                'end_date' => 'required|date',
            ]);

            $performance = [
                'activities_completed' => \App\Models\Activity::where('assigned_to', $request->user_id)
                    ->whereBetween('completed_at', [$request->start_date, $request->end_date])
                    ->count(),
                'opportunities_closed' => \App\Models\Opportunity::where('assigned_to', $request->user_id)
                    ->where('stage', 'closed_won')
                    ->whereBetween('updated_at', [$request->start_date, $request->end_date])
                    ->count(),
                'attendance_rate' => $this->calculateAttendanceRate($request->user_id, $request->start_date, $request->end_date),
                'invoices_generated' => \App\Models\Invoice::where('client_id', $request->user_id)
                    ->whereBetween('issue_date', [$request->start_date, $request->end_date])
                    ->count(),
            ];

            return response()->json([
                'data' => $performance,
                'meta' => [
                    'user_id' => $request->user_id,
                    'period' => $this->getPeriodLabel($request->start_date, $request->end_date),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to generate user performance report',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    protected function getPeriodLabel($startDate, $endDate)
    {
        $start = \Carbon\Carbon::parse($startDate);
        $end = \Carbon\Carbon::parse($endDate);

        if ($start->isSameDay($end)) {
            return $start->format('M j, Y');
        }

        return $start->format('M j, Y') . ' - ' . $end->format('M j, Y');
    }

    protected function calculateAttendanceRate($userId, $startDate, $endDate)
    {
        $totalDays = \Carbon\Carbon::parse($startDate)->diffInDays($endDate) + 1;
        $presentDays = \App\Models\Attendance::where('user_id', $userId)
            ->whereBetween('date', [$startDate, $endDate])
            ->where('status', 'present')
            ->count();

        return $totalDays > 0 ? round(($presentDays / $totalDays) * 100, 2) : 0;
    }
}