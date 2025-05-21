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

        $email = $this->faker->email;

        // Создаем тестового пользователя
        $this->user = User::factory()->create([
            'email' => $email,
            'password' => bcrypt('11211121')
        ]);

        // Создаем тестовую новость
        $this->news = News::factory()->create([
            'user_id' => $this->user->id,
            'is_published' => true,
            'published_at' => now()
        ]);

        // Получаем токен для авторизации
        $response = $this->postJson('/v1/auth/login', [
            'email' => $email,
            'password' => '11211121'
        ]);

        $this->token = $response->json('token');
    }

    /** @test */
    public function user_can_create_comment()
    {
        $commentData = [
            'content' => 'Test comment content'
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson("/v1/news/{$this->news->id}/comments", $commentData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'comment' => [
                    'id',
                    'content',
                    'user_id',
                    'news_id',
                    'created_at',
                    'user' => [
                        'id',
                        'name',
                        'email'
                    ]
                ]
            ]);

        $this->assertDatabaseHas('comments', [
            'content' => $commentData['content'],
            'user_id' => $this->user->id,
            'news_id' => $this->news->id
        ]);
    }

    /** @test */
    public function user_cannot_create_comment_without_content()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson("/v1/news/{$this->news->id}/comments", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['content']);
    }

    /** @test */
    public function user_can_update_own_comment()
    {
        $comment = Comment::factory()->create([
            'user_id' => $this->user->id,
            'news_id' => $this->news->id,
            'content' => 'Original content'
        ]);

        $updatedData = [
            'content' => 'Updated content'
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson("/v1/comments/{$comment->id}", $updatedData);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Comment updated successfully',
                'comment' => [
                    'content' => 'Updated content'
                ]
            ]);

        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'content' => 'Updated content'
        ]);
    }

    /** @test */
    public function user_cannot_update_other_users_comment()
    {
        $otherUser = User::factory()->create();
        $comment = Comment::factory()->create([
            'user_id' => $otherUser->id,
            'news_id' => $this->news->id,
            'content' => 'Original content'
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson("/v1/comments/{$comment->id}", [
                'content' => 'Updated content'
            ]);

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'You are not authorized to update this comment. You can only edit your own comments.',
                'error' => 'unauthorized'
            ]);
    }

    /** @test */
    public function user_can_delete_own_comment()
    {
        $comment = Comment::factory()->create([
            'user_id' => $this->user->id,
            'news_id' => $this->news->id
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson("/v1/comments/{$comment->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Comment deleted successfully'
            ]);

        $this->assertSoftDeleted('comments', [
            'id' => $comment->id
        ]);
    }

    /** @test */
    public function user_cannot_delete_other_users_comment()
    {
        $otherUser = User::factory()->create();
        $comment = Comment::factory()->create([
            'user_id' => $otherUser->id,
            'news_id' => $this->news->id
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson("/v1/comments/{$comment->id}");

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'You are not authorized to delete this comment. You can only delete your own comments.',
                'error' => 'unauthorized'
            ]);
    }

    /** @test */
    public function user_can_search_comments()
    {
        // Создаем несколько комментариев
        Comment::factory()->create([
            'user_id' => $this->user->id,
            'news_id' => $this->news->id,
            'content' => 'Great article about Laravel'
        ]);

        Comment::factory()->create([
            'user_id' => $this->user->id,
            'news_id' => $this->news->id,
            'content' => 'Another comment about PHP'
        ]);

        // Поиск по слову "Laravel"
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/v1/comments/search?query=Laravel');

        $response->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment(['content' => 'Great article about Laravel']);

        // Поиск по ID пользователя
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson("/v1/comments/search?user_id={$this->user->id}");

        $response->assertStatus(200)
            ->assertJsonCount(2);
    }
}
