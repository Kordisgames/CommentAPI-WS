<?php
namespace App\Services;

use App\Models\Comment;
use App\Models\News;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class CommentService
{
    public function create(array $data, User $user, News $news): Comment
    {
        Log::info('Создание комментария через сервис', [
            'news_id' => $news->id,
            'user_id' => $user->id
        ]);

        $comment = new Comment($data);
        $comment->user()->associate($user);
        $comment->news()->associate($news);
        $comment->save();

        Log::info('Комментарий создан через сервис', [
            'comment_id' => $comment->id,
            'news_id' => $news->id,
            'user_id' => $user->id
        ]);

        // Отправляем событие о создании комментария
        event(new \App\Events\CommentCreated($comment));

        Log::info('Событие CommentCreated отправлено через сервис', [
            'comment_id' => $comment->id
        ]);

        return $comment;
    }

    public function update(Comment $comment, array $data): Comment
    {
        $comment->update($data);
        return $comment;
    }

    public function delete(Comment $comment): bool
    {
        return $comment->delete();
    }

    public function getCommentsForNews(News $news, int $perPage = 15): LengthAwarePaginator
    {
        return $news->comments()
            ->with(['user', 'replies.user'])
            ->whereNull('parent_id')
            ->latest()
            ->paginate($perPage);
    }

    public function search(string $query, array $filters = []): Collection
    {
        $comments = Comment::query()
            ->with(['user', 'news'])
            ->where('content', 'like', "%{$query}%");

        if (isset($filters['user_id'])) {
            $comments->where('user_id', $filters['user_id']);
        }

        if (isset($filters['news_id'])) {
            $comments->where('news_id', $filters['news_id']);
        }

        if (isset($filters['is_approved'])) {
            $comments->where('is_approved', $filters['is_approved']);
        }

        return $comments->get();
    }

    public function getCommentWithReplies(Comment $comment): Comment
    {
        return $comment->load(['user', 'replies.user']);
    }
}
