<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\Notification;
use App\Models\User;

class NotificationService
{
    /**
     * Create a notification for a user, and CC all admins.
     */
    public static function notify(
        User $user,
        string $title,
        string $message,
        string $type,
        ?string $actionUrl = null
    ): Notification {
        // 1. Create primary notification
        $primaryNotification = Notification::create([
            'user_id' => $user->id,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'action_url' => $actionUrl,
            'is_read' => false,
        ]);

        // 2. CC all system admins if the primary target is not an admin
        if ($user->role !== UserRole::ADMIN) {
            $admins = User::where('role', UserRole::ADMIN)->get();
            foreach ($admins as $admin) {
                $adminUrl = $actionUrl;
                if ($actionUrl) {
                    if (str_contains($actionUrl, 'appointment')) {
                        $adminUrl = '/admin/appointments';
                    } elseif (str_contains($actionUrl, 'review')) {
                        $adminUrl = '/admin/reviews';
                    }
                }

                Notification::create([
                    'user_id' => $admin->id,
                    'title' => $title." ({$user->name})",
                    'message' => $message,
                    'type' => $type,
                    'action_url' => $adminUrl,
                    'is_read' => false,
                ]);
            }
        }

        return $primaryNotification;
    }

    /**
     * Mark a notification as read.
     */
    public static function markAsRead(Notification $notification): Notification
    {
        $notification->update([
            'is_read' => true,
        ]);

        return $notification;
    }

    /**
     * Mark all notifications as read.
     */
    public static function markAllAsRead(User $user): void
    {
        $user->notifications()
            ->where('is_read', false)
            ->update([
                'is_read' => true,
            ]);
    }
}
