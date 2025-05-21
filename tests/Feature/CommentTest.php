<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\News;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;
    private News $news;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        // Создаем пользователя и новость для тестов
        $this->user = User::factory()->create();
        $this->news = News::factory()->create(['user_id' => $this->user->id]);

        // Получаем токен для аутентификации
        $this->token = $this->user->createToken('test-token')->plainTextToken;
    }

    public function test_can_create_comment(): void
    {
        $commentData = [
            'content' => $this->faker->paragraph,
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson("/api/v1/news/{$this->news->id}/comments", $commentData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'content',
                'user_id',
                'news_id',
                'created_at',
                'updated_at',
                'user' => [
                    'id',
                    'name',
                    'email',
                ],
            ]);

        $this->assertDatabaseHas('comments', [
            'content' => $commentData['content'],
            'user_id' => $this->user->id,
            'news_id' => $this->news->id,
        ]);
    }

    public function test_can_update_own_comment(): void
    {
        $comment = Comment::factory()->create([
            'user_id' => $this->user->id,
            'news_id' => $this->news->id,
        ]);

        $updateData = [
            'content' => 'Updated comment content',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson("/api/v1/comments/{$comment->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'content' => $updateData['content'],
            ]);

        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'content' => $updateData['content'],
        ]);
    }

    public function test_cannot_update_other_user_comment(): void
    {
        $otherUser = User::factory()->create();
        $comment = Comment::factory()->create([
            'user_id' => $otherUser->id,
            'news_id' => $this->news->id,
        ]);

        $updateData = [
            'content' => 'Updated comment content',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson("/api/v1/comments/{$comment->id}", $updateData);

        $response->assertStatus(403);
    }

    public function test_can_delete_own_comment(): void
    {
        $comment = Comment::factory()->create([
            'user_id' => $this->user->id,
            'news_id' => $this->news->id,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson("/api/v1/comments/{$comment->id}");

        $response->assertStatus(204);

        $this->assertSoftDeleted('comments', [
            'id' => $comment->id,
        ]);
    }

    public function test_can_search_comments(): void
    {
        // Создаем несколько комментариев
        Comment::factory()->count(3)->create([
            'news_id' => $this->news->id,
            'content' => 'Test comment',
        ]);

        Comment::factory()->create([
            'news_id' => $this->news->id,
            'content' => 'Different content',
        ]);

        $response = $this->getJson('/api/v1/comments/search?query=Test');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }
}
