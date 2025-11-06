<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class NotificationService
{
    public function sendToUser(User $user, string $title, string $message, array $data = [])
    {
        // Store in database
        $user->notifications()->create([
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'read_at' => null,
        ]);

        // TODO: Implement real-time notification (WebSocket/Pusher)
        // broadcast(new NotificationSent($user, $title, $message, $data));
    }

    public function sendToMultipleUsers($users, string $title, string $message, array $data = [])
    {
        DB::transaction(function () use ($users, $title, $message, $data) {
            $notifications = [];
            foreach ($users as $user) {
                $notifications[] = [
                    'user_id' => $user->id,
                    'title' => $title,
                    'message' => $message,
                    'data' => json_encode($data),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            \App\Models\Notification::insert($notifications);
        });

        // TODO: Broadcast to multiple users
    }

    public function markAsRead($notificationId, User $user)
    {
        $notification = $user->notifications()->findOrFail($notificationId);
        $notification->update(['read_at' => now()]);
        
        return $notification;
    }

    public function getUserNotifications(User $user, $unreadOnly = false)
    {
        $query = $user->notifications()->orderBy('created_at', 'desc');

        if ($unreadOnly) {
            $query->whereNull('read_at');
        }

        return $query->paginate(20);
    }
}