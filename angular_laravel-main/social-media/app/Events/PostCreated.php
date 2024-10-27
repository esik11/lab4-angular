<?php

// app/Events/PostCreated.php

namespace App\Events;

use App\Models\Post;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class PostCreated implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public $post;
    public $user;

    public function __construct(User $user, Post $post)
    {
        $this->user = $user;
        $this->post = $post;
    }

    // The channel on which the event will be broadcast
    public function broadcastOn()
    {
        return new Channel('notifications');
    }

    // Customize the broadcasted data
    public function broadcastWith()
    {
        \Log::info('Broadcasting post created event', [
            'post_id' => $this->post->id,
            'user_id' => $this->user->id,
            'message' => 'New post created by ' . $this->user->name,
            'time' => now()->toDateTimeString(),
        ]);
    
        return [
            'post_id' => $this->post->id,
            'user_id' => $this->user->id,
            'name' => $this->user->name,  // Add name field here
            'message' => 'New post created by ' . $this->user->name,
            'time' => now()->toDateTimeString(),
        ];
    }
}