<?php

namespace App\Http\Controllers;

use App\Models\Notification; 
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    // Fetch unread notifications for the logged-in user
    public function fetchUnreadNotifications()
    {
        $notifications = Notification::where('user_id', auth()->id())
            ->where('is_read', false)
            ->get();

        return response()->json($notifications);
    }

    // Fetch all notifications (read and unread) for the logged-in user
    public function fetchAllNotifications()
    {
        $notifications = Notification::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($notifications);
    }

    // Mark a specific notification as read
    public function markAsRead($notificationId)
    {
        $notification = Notification::find($notificationId);
        if ($notification) {
            // Mark the notification as read
            $notification->is_read = true; // Assuming you have a field 'is_read'
            $notification->save();
            
            return response()->json(['message' => 'Notification marked as read'], 200);
        }
        return response()->json(['message' => 'Notification not found'], 404);
    }
    // Delete a specific notification
    public function deleteNotification($notificationId)
    {
        $notification = Notification::findOrFail($notificationId);
        $notification->delete();

        return response()->json(['message' => 'Notification deleted.']);
    }
}
