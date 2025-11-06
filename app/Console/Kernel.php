<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        Commands\GenerateReports::class,
        Commands\SendNotifications::class,
        Commands\UpdateAttendance::class,
    ];

    protected function schedule(Schedule $schedule)
    {
        // Daily at 8 PM - Generate daily reports
        $schedule->command('reports:generate daily')
                 ->dailyAt('20:00')
                 ->timezone('Asia/Riyadh');

        // Daily at 9 PM - Auto checkout users
        $schedule->command('attendance:update --auto-checkout')
                 ->dailyAt('21:00')
                 ->timezone('Asia/Riyadh');

        // Weekly on Sunday at 9 AM - Generate weekly reports
        $schedule->command('reports:generate weekly')
                 ->sundays()
                 ->at('09:00')
                 ->timezone('Asia/Riyadh');

        // Monthly on 1st at 8 AM - Generate monthly reports
        $schedule->command('reports:generate monthly')
                 ->monthlyOn(1, '08:00')
                 ->timezone('Asia/Riyadh');

        // Cleanup old notifications monthly
        $schedule->command('model:prune', [
            '--model' => [\App\Models\Notification::class],
        ])->monthly();
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}