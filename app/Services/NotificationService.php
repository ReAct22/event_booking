<?php

namespace App\Services;

use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class NotificationService
{
    /*
    |--------------------------------------------------------------------------
    | Create Notification
    |--------------------------------------------------------------------------
    */
    public function create(int $userId, string $message)
    {
        return Notification::create([
            'user_id' => $userId,
            'message' => $message,
            'is_read' => false
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Get My Notifications
    |--------------------------------------------------------------------------
    */
    public function myNotifications()
    {
        return Notification::where('user_id', Auth::id())
            ->latest()
            ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | Get Unread Count
    |--------------------------------------------------------------------------
    */
    public function unreadCount()
    {
        return Notification::where('user_id', Auth::id())
            ->unread()
            ->count();
    }

    /*
    |--------------------------------------------------------------------------
    | Mark As Read
    |--------------------------------------------------------------------------
    */
    public function markAsRead(int $notificationId)
    {
        $notification = Notification::findOrFail($notificationId);

        if ($notification->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $notification->update(['is_read' => true]);

        return ['message' => 'Notification marked as read'];
    }

    /*
    |--------------------------------------------------------------------------
    | Mark All As Read
    |--------------------------------------------------------------------------
    */
    public function markAllAsRead()
    {
        Notification::where('user_id', Auth::id())
            ->update(['is_read' => true]);

        return ['message' => 'All notifications marked as read'];
    }
}
