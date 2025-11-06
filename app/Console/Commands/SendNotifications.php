<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class SendNotifications extends Command
{
    protected $signature = 'notifications:send 
                            {message : The notification message}
                            {--type=info : Notification type}
                            {--users=all : Target users}';
    
    protected $description = 'Send notifications to users';

    public function handle()
    {
        $message = $this->argument('message');
        $type = $this->option('type');
        $usersOption = $this->option('users');

        $users = $this->getTargetUsers($usersOption);
        $notificationService = app(NotificationService::class);

        $this->info("Sending {$type} notification to {$users->count()} users...");

        $notificationService->sendToMultipleUsers(
            $users, 
            "System Notification", 
            $message,
            ['type' => $type]
        );

        $this->info("Notifications sent successfully!");
    }

    protected function getTargetUsers($usersOption)
    {
        if ($usersOption === 'all') {
            return User::where('status', true)->get();
        }

        if ($usersOption === 'admins') {
            return User::whereHas('role', function($query) {
                $query->where('name', 'admin');
            })->where('status', true)->get();
        }

        // Assume it's a comma-separated list of user IDs
        $userIds = explode(',', $usersOption);
        return User::whereIn('id', $userIds)->where('status', true)->get();
    }
}