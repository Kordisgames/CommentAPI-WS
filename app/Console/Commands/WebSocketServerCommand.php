<?php

namespace App\Console\Commands;

use App\WebSocket\WebSocketServer;
use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'websocket:serve', description: 'Запуск WebSocket сервера')]
class WebSocketServerCommand extends Command
{
    public function handle(): int
    {
        $this->info('Запуск WebSocket сервера...');
        $server = new WebSocketServer();
        $server->start();

        return self::SUCCESS;
    }
}