<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="NewsResource",
 *     type="object",
 *     title="News Resource",
 *     description="News resource schema",
 *     required={"id", "title", "content", "slug", "user_id"},
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         description="News ID",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="title",
 *         type="string",
 *         description="News title",
 *         example="Breaking News"
 *     ),
 *     @OA\Property(
 *         property="content",
 *         type="string",
 *         description="News content",
 *         example="This is a breaking news article..."
 *     ),
 *     @OA\Property(
 *         property="slug",
 *         type="string",
 *         description="News slug",
 *         example="breaking-news"
 *     ),
 *     @OA\Property(
 *         property="user_id",
 *         type="integer",
 *         description="Author ID",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="is_published",
 *         type="boolean",
 *         description="Publication status",
 *         example=true
 *     ),
 *     @OA\Property(
 *         property="published_at",
 *         type="string",
 *         format="date-time",
 *         nullable=true,
 *         description="Publication timestamp",
 *         example="2024-03-20T12:00:00Z"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         description="News creation timestamp",
 *         example="2024-03-20T12:00:00Z"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         description="News last update timestamp",
 *         example="2024-03-20T12:00:00Z"
 *     ),
 *     @OA\Property(
 *         property="user",
 *         ref="#/components/schemas/UserResource",
 *         description="News author"
 *     ),
 *     @OA\Property(
 *         property="comments",
 *         type="array",
 *         description="News comments",
 *         @OA\Items(ref="#/components/schemas/CommentResource")
 *     )
 * )
 */
class NewsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'slug' => $this->slug,
            'user_id' => $this->user_id,
            'is_published' => $this->is_published,
            'published_at' => $this->published_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'user' => new UserResource($this->whenLoaded('user')),
            'comments' => CommentResource::collection($this->whenLoaded('comments')),
        ];
    }
}
