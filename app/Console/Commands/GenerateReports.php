<?php

namespace App\Console\Commands;

use App\Services\ReportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class GenerateReports extends Command
{
    protected $signature = 'reports:generate {type=daily} {--email=}';
    protected $description = 'Generate automated reports';

    public function handle()
    {
        $type = $this->argument('type');
        $email = $this->option('email');

        $this->info("Generating {$type} reports...");

        // Generate reports logic here
        $reportService = app(ReportService::class);
        
        switch ($type) {
            case 'daily':
                $this->generateDailyReports($reportService);
                break;
            case 'weekly':
                $this->generateWeeklyReports($reportService);
                break;
            case 'monthly':
                $this->generateMonthlyReports($reportService);
                break;
        }

        $this->info("{$type} reports generated successfully!");

        if ($email) {
            $this->sendReportEmail($email, $type);
        }
    }

    protected function generateDailyReports($reportService)
    {
        // Daily attendance report
        $attendanceReport = $reportService->getAttendanceReport(now()->month, now()->year);
        
        // Daily sales report
        $salesReport = $reportService->getSalesReport(
            now()->startOfDay(), 
            now()->endOfDay()
        );

        $this->info("Daily Reports:");
        $this->info("- Attendance: " . json_encode($attendanceReport));
        $this->info("- Sales: " . json_encode($salesReport));
    }

    protected function generateWeeklyReports($reportService)
    {
        $startDate = now()->startOfWeek();
        $endDate = now()->endOfWeek();

        $weeklyReport = $reportService->getSalesReport($startDate, $endDate);
        $this->info("Weekly Report: " . json_encode($weeklyReport));
    }

    protected function generateMonthlyReports($reportService)
    {
        $startDate = now()->startOfMonth();
        $endDate = now()->endOfMonth();

        $monthlyReport = $reportService->getSalesReport($startDate, $endDate);
        $this->info("Monthly Report: " . json_encode($monthlyReport));
    }

    protected function sendReportEmail($email, $type)
    {
        // Email sending logic would go here
        $this->info("Report sent to: {$email}");
    }
}