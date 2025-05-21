<?php

namespace App\Jobs;

use App\Models\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessCommentNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected Comment $comment
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Здесь можно добавить логику отправки уведомлений
            // Например, отправка email модератору или другим пользователям

            Log::info('Processing comment notification', [
                'comment_id' => $this->comment->id,
                'news_id' => $this->comment->news_id,
                'user_id' => $this->comment->user_id
            ]);

            // TODO: Добавить реальную логику отправки уведомлений
            // Например:
            // - Отправка email модератору
            // - Отправка push-уведомлений
            // - Обновление статистики

        } catch (\Exception $e) {
            Log::error('Failed to process comment notification', [
                'comment_id' => $this->comment->id,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }
}