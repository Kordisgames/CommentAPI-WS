<?php

namespace App\Listeners;

use App\Events\CommentCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use App\Jobs\ProcessCommentNotification;
use App\Jobs\SendWebSocketMessage;

class HandleNewComment implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(CommentCreated $event): void
    {
        // Логируем создание нового комментария
        Log::info('New comment created', [
            'comment_id' => $event->comment->id,
            'news_id' => $event->comment->news_id,
            'user_id' => $event->comment->user_id
        ]);

        // Отправляем задачу в очередь для обработки уведомлений
        ProcessCommentNotification::dispatch($event->comment)
            ->onQueue('notifications');

        // Отправляем сообщение через WebSocket
        SendWebSocketMessage::dispatch($event->comment)
            ->onQueue('websocket');
    }
}
