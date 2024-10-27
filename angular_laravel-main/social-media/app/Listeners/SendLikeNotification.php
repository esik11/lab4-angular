<?php

namespace App\Listeners;

use App\Events\LikeAdded;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\Notification;
use App\Models\User;
class SendLikeNotification implements ShouldQueue
{
    public function __construct()
    {
        //
    }

    public function handle(LikeAdded $event)
    {
        // Get the post owner's user ID
        $postOwnerId = $event->like->post->user_id; // Ensure the post relationship is set correctly
    
        // Log the user and post IDs for debugging
        \Log::info('Creating Notification', [
            'user_id' => $postOwnerId,
            'post_id' => $event->like->post_id,
            'like_id' => $event->like->id,
        ]);
    
        // Check if the post owner exists
        if (User::find($postOwnerId)) {
            Notification::create([
                'user_id' => $postOwnerId,  // Notify the owner of the post
                'post_id' => $event->like->post_id,
                'like_id' => $event->like->id, // If you want to store which like caused the notification
                'type' => 'like',
                'is_read' => false,
            ]);
        } else {
            \Log::error('User ID not found for notification', ['postOwnerId' => $postOwnerId]);
        }
    }
}