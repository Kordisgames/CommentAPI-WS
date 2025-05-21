<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @OA\Schema(
 *     schema="Comment",
 *     title="Comment",
 *     description="Comment model",
 *     @OA\Property(property="id", type="integer", format="int64"),
 *     @OA\Property(property="content", type="string"),
 *     @OA\Property(property="user_id", type="integer", format="int64"),
 *     @OA\Property(property="news_id", type="integer", format="int64"),
 *     @OA\Property(property="parent_id", type="integer", format="int64", nullable=true),
 *     @OA\Property(property="rating", type="integer"),
 *     @OA\Property(property="is_approved", type="boolean"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(
 *         property="user",
 *         ref="#/components/schemas/User"
 *     ),
 *     @OA\Property(
 *         property="news",
 *         ref="#/components/schemas/News"
 *     ),
 *     @OA\Property(
 *         property="replies",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/Comment")
 *     )
 * )
 */
class Comment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'content',
        'user_id',
        'news_id',
        'parent_id',
        'rating',
        'is_approved',
    ];

    protected $casts = [
        'rating' => 'integer',
        'is_approved' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function news(): BelongsTo
    {
        return $this->belongsTo(News::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }
}
