<?php

namespace App\Events;

use App\Models\Like;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class LikeAdded implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public $like;
    public $user;

    public function __construct(User $user, Like $like)
    {
        $this->user = $user;
        $this->like = $like;
    }

    // The channel on which the event will be broadcast
    public function broadcastOn()
    {
        return new Channel('notifications');
    }

    // Customize the broadcasted data
    public function broadcastWith()
    {
        \Log::info('Broadcasting like added event', [
            'like_id' => $this->like->id,
            'user_id' => $this->user->id,
            'message' => $this->user->name . ' liked a post',
            'post_id' => $this->like->post_id, // Assuming Like model has post_id
            'time' => now()->toDateTimeString(),
        ]);

        return [
            'like_id' => $this->like->id,
            'user_id' => $this->user->id,
            'message' => $this->user->name . ' liked a post',
            'post_id' => $this->like->post_id, // Assuming Like model has post_id
            'time' => now()->toDateTimeString(),
        ];
    }
}
