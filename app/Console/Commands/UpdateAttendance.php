<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateAttendance extends Command
{
    protected $signature = 'attendance:update 
                            {--date=today : The date to process}
                            {--auto-checkout : Auto checkout users who forgot}';
    
    protected $description = 'Update attendance records and process auto actions';

    public function handle()
    {
        $date = $this->option('date') === 'today' ? now() : $this->option('date');
        $autoCheckout = $this->option('auto-checkout');

        $this->info("Processing attendance for {$date->format('Y-m-d')}...");

        if ($autoCheckout) {
            $this->autoCheckoutUsers($date);
        }

        $this->updateAttendanceStatus($date);
        $this->info("Attendance update completed!");
    }

    protected function autoCheckoutUsers($date)
    {
        $forgotCheckout = Attendance::whereDate('date', $date)
            ->whereNotNull('check_in')
            ->whereNull('check_out')
            ->get();

        foreach ($forgotCheckout as $attendance) {
            // Auto checkout at 6 PM if user forgot
            $checkoutTime = $date->copy()->setHour(18)->setMinute(0);
            
            $attendance->update([
                'check_out' => $checkoutTime,
                'total_hours' => $attendance->check_in->diffInHours($checkoutTime),
                'notes' => 'Auto checked out by system'
            ]);

            $this->info("Auto checked out user: {$attendance->user->name}");
        }
    }

    protected function updateAttendanceStatus($date)
    {
        // Mark absent users
        $presentUserIds = Attendance::whereDate('date', $date)
            ->where('status', 'present')
            ->pluck('user_id');

        $absentUsers = DB::table('users')
            ->whereNotIn('id', $presentUserIds)
            ->where('status', true)
            ->get();

        foreach ($absentUsers as $user) {
            Attendance::create([
                'user_id' => $user->id,
                'date' => $date,
                'status' => 'absent',
                'notes' => 'Automatically marked as absent'
            ]);

            $this->info("Marked absent: {$user->name}");
        }
    }
}