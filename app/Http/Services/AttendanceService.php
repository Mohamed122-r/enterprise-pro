<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    public function checkIn(User $user): Attendance
    {
        return DB::transaction(function () use ($user) {
            // Check if user already checked in today
            $existingAttendance = Attendance::where('user_id', $user->id)
                ->whereDate('check_in', today())
                ->first();

            if ($existingAttendance) {
                throw new \Exception('You have already checked in today');
            }

            $attendance = Attendance::create([
                'user_id' => $user->id,
                'check_in' => now(),
                'date' => today(),
                'status' => 'present'
            ]);

            return $attendance->load('user');
        });
    }

    public function checkOut(User $user): Attendance
    {
        return DB::transaction(function () use ($user) {
            $attendance = Attendance::where('user_id', $user->id)
                ->whereDate('check_in', today())
                ->whereNull('check_out')
                ->first();

            if (!$attendance) {
                throw new \Exception('No check-in record found for today');
            }

            $attendance->update([
                'check_out' => now(),
                'total_hours' => $attendance->check_in->diffInHours(now())
            ]);

            return $attendance->load('user');
        });
    }

    public function createAttendance(array $data): Attendance
    {
        return DB::transaction(function () use ($data) {
            $attendance = Attendance::create([
                'user_id' => $data['user_id'],
                'date' => $data['date'],
                'check_in' => $data['check_in'] ?? null,
                'check_out' => $data['check_out'] ?? null,
                'status' => $data['status'] ?? 'present',
                'notes' => $data['notes'] ?? null,
            ]);

            if ($attendance->check_in && $attendance->check_out) {
                $attendance->update([
                    'total_hours' => $attendance->check_in->diffInHours($attendance->check_out)
                ]);
            }

            return $attendance->load('user');
        });
    }

    public function getAttendancesWithFilters(array $filters = [])
    {
        $query = Attendance::with(['user', 'user.department']);

        if (isset($filters['user_id']) && $filters['user_id']) {
            $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['date_from']) && $filters['date_from']) {
            $query->where('date', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to']) && $filters['date_to']) {
            $query->where('date', '<=', $filters['date_to']);
        }

        if (isset($filters['status']) && $filters['status']) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['department_id']) && $filters['department_id']) {
            $query->whereHas('user', function ($q) use ($filters) {
                $q->where('department_id', $filters['department_id']);
            });
        }

        return $query->orderBy('date', 'desc')->paginate(20);
    }

    public function getMonthlyReport($userId, $month, $year)
    {
        return Attendance::where('user_id', $userId)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->get()
            ->groupBy('status')
            ->map->count();
    }
}