<?php

namespace App\WebSocket;

use Workerman\Worker;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Event;
use App\Events\CommentCreated;

class WebSocketServer
{
    protected $worker;
    protected static $instance = null;

    public function __construct()
    {
        if (self::$instance !== null) {
            throw new \Exception('WebSocketServer уже запущен');
        }
        self::$instance = $this;

        $this->worker = new Worker('websocket://0.0.0.0:6001');
        $this->worker->count = 1;

        $this->worker->onConnect = function($connection) {
            Log::info('Новое подключение', [
                'connection_id' => $connection->id,
                'remote_address' => $connection->getRemoteAddress()
            ]);
        };

        $this->worker->onClose = function($connection) {
            Log::info('Соединение закрыто', [
                'connection_id' => $connection->id
            ]);
        };

        // Подписываемся на события Laravel
        $this->subscribeToLaravelEvents();
    }

    protected function subscribeToLaravelEvents()
    {
        Log::info('Подписываемся на событие CommentCreated');

        // Слушаем только событие CommentCreated
        Event::listen(CommentCreated::class, function (CommentCreated $event) {
            Log::info('Обработка события CommentCreated', [
                'comment_id' => $event->comment->id,
                'news_id' => $event->comment->news_id
            ]);

            $message = json_encode([
                'event' => 'CommentCreated',
                'data' => [
                    'comment' => [
                        'id' => $event->comment->id,
                        'content' => $event->comment->content,
                        'news_id' => $event->comment->news_id,
                        'user' => [
                            'id' => $event->comment->user->id,
                            'name' => $event->comment->user->name,
                        ],
                        'created_at' => $event->comment->created_at,
                    ]
                ]
            ]);

            $connectionsCount = count($this->worker->connections);
            Log::info('Отправка сообщения', [
                'message' => $message,
                'connections_count' => $connectionsCount
            ]);

            // Отправляем сообщение всем подключенным клиентам
            foreach ($this->worker->connections as $connection) {
                $connection->send($message);
                Log::info('Сообщение отправлено клиенту', [
                    'connection_id' => $connection->id
                ]);
            }
        });
    }

    public function getWorker(): Worker
    {
        return $this->worker;
    }

    public function getConnections(): array
    {
        return $this->worker->connections;
    }

    public function start()
    {
        Log::info('Запуск WebSocket сервера');
        Worker::runAll();
    }

    public static function getInstance()
    {
        return self::$instance;
    }
}
