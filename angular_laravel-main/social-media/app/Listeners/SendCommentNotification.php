<?php

namespace App\Listeners;

use App\Events\CommentAdded;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\Notification;
use App\Models\User;
class SendCommentNotification implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\CommentAdded  $event
     * @return void
     */
    public function handle(CommentAdded $event)
    {
        Notification::create([
            'user_id' => $event->post->user_id,  // Notify the owner of the post
            'post_id' => $event->post->id,
            'type' => 'comment',
            'is_read' => false,
        ]);
    }
}
