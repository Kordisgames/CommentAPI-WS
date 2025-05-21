<?php

namespace App\Jobs;

use App\Models\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\WebSocket\WebSocketServer;

class SendWebSocketMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected Comment $comment
    ) {}

    public function handle(): void
    {
        $server = WebSocketServer::getInstance();
        if (!$server) {
            Log::error('WebSocket сервер не запущен');
            return;
        }

        $message = json_encode([
            'event' => 'CommentCreated',
            'data' => [
                'comment' => [
                    'id' => $this->comment->id,
                    'content' => $this->comment->content,
                    'news_id' => $this->comment->news_id,
                    'user' => [
                        'id' => $this->comment->user->id,
                        'name' => $this->comment->user->name,
                    ],
                    'created_at' => $this->comment->created_at,
                ]
            ]
        ]);

        $connections = $server->getConnections();
        $connectionsCount = count($connections);
        Log::info('Отправка сообщения через очередь', [
            'message' => $message,
            'connections_count' => $connectionsCount
        ]);

        foreach ($connections as $connection) {
            $connection->send($message);
            Log::info('Сообщение отправлено клиенту', [
                'connection_id' => $connection->id
            ]);
        }
    }
}
