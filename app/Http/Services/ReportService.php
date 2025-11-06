<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class ReportService
{
    public function getDashboardStats(User $user)
    {
        $stats = [];

        // User-specific stats
        if ($user->hasPermission('reports.view')) {
            $stats = [
                'total_users' => User::count(),
                'active_users' => User::where('status', true)->count(),
                'total_contacts' => \App\Models\Contact::count(),
                'total_invoices' => \App\Models\Invoice::count(),
                'revenue' => \App\Models\Invoice::where('status', 'paid')->sum('total'),
                'pending_invoices' => \App\Models\Invoice::whereIn('status', ['sent', 'partial'])->count(),
            ];
        } else {
            // Limited stats for regular users
            $stats = [
                'my_contacts' => \App\Models\Contact::where('assigned_to', $user->id)->count(),
                'my_invoices' => \App\Models\Invoice::where('client_id', $user->id)->count(),
                'attendance_days' => \App\Models\Attendance::where('user_id', $user->id)->count(),
                'pending_tasks' => \App\Models\Activity::where('assigned_to', $user->id)
                    ->where('status', '!=', 'completed')
                    ->count(),
            ];
        }

        return $stats;
    }

    public function getRecentActivities(User $user)
    {
        $query = \App\Models\Activity::with(['assignedUser', 'contact'])
            ->orderBy('created_at', 'desc')
            ->limit(10);

        if (!$user->hasPermission('activities.read.all')) {
            $query->where('assigned_to', $user->id);
        }

        return $query->get();
    }

    public function getSalesReport($startDate, $endDate)
    {
        return \App\Models\Invoice::whereBetween('issue_date', [$startDate, $endDate])
            ->select(
                DB::raw('SUM(total) as revenue'),
                DB::raw('COUNT(*) as invoice_count'),
                DB::raw('AVG(total) as average_invoice'),
                DB::raw('SUM(CASE WHEN status = "paid" THEN total ELSE 0 END) as paid_amount'),
                DB::raw('SUM(CASE WHEN status = "pending" THEN total ELSE 0 END) as pending_amount')
            )
            ->first();
    }

    public function getAttendanceReport($month, $year)
    {
        return \App\Models\Attendance::whereYear('date', $year)
            ->whereMonth('date', $month)
            ->select(
                'status',
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status');
    }
}