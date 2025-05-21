<?php

namespace App\Events;

use App\Models\Comment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CommentCreated
{
    use Dispatchable, SerializesModels;

    public $comment;

    /**
     * Create a new event instance.
     */
    public function __construct(Comment $comment)
    {
        $this->comment = $comment;
        Log::info('CommentCreated event constructed', [
            'comment_id' => $comment->id,
            'news_id' => $comment->news_id,
            'user_id' => $comment->user_id
        ]);
    }
}
