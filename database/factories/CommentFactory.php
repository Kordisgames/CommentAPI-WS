<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\News;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comment>
 */
class CommentFactory extends Factory
{
    protected $model = Comment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'content' => $this->faker->paragraph,
            'user_id' => User::factory(),
            'news_id' => News::factory(),
            'parent_id' => null,
            'is_approved' => true,
        ];
    }

    public function unapproved(): self
    {
        return $this->state(fn (array $attributes) => [
            'is_approved' => false,
        ]);
    }

    public function reply(Comment $parent): self
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => $parent->id,
            'news_id' => $parent->news_id,
        ]);
    }
}
