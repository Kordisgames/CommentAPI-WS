<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="CommentResource",
 *     type="object",
 *     title="Comment Resource",
 *     description="Comment resource schema",
 *     required={"id", "content", "user_id", "news_id"},
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         description="Comment ID",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="content",
 *         type="string",
 *         description="Comment content",
 *         example="This is a great article!"
 *     ),
 *     @OA\Property(
 *         property="user_id",
 *         type="integer",
 *         description="Author ID",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="news_id",
 *         type="integer",
 *         description="News ID",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="parent_id",
 *         type="integer",
 *         nullable=true,
 *         description="Parent comment ID",
 *         example=null
 *     ),
 *     @OA\Property(
 *         property="rating",
 *         type="integer",
 *         description="Comment rating",
 *         example=5
 *     ),
 *     @OA\Property(
 *         property="is_approved",
 *         type="boolean",
 *         description="Approval status",
 *         example=true
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         description="Comment creation timestamp",
 *         example="2024-03-20T12:00:00Z"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         description="Comment last update timestamp",
 *         example="2024-03-20T12:00:00Z"
 *     ),
 *     @OA\Property(
 *         property="user",
 *         ref="#/components/schemas/UserResource",
 *         description="Comment author"
 *     ),
 *     @OA\Property(
 *         property="news",
 *         ref="#/components/schemas/NewsResource",
 *         description="Related news"
 *     ),
 *     @OA\Property(
 *         property="parent",
 *         ref="#/components/schemas/CommentResource",
 *         nullable=true,
 *         description="Parent comment"
 *     ),
 *     @OA\Property(
 *         property="replies",
 *         type="array",
 *         description="Comment replies",
 *         @OA\Items(ref="#/components/schemas/CommentResource")
 *     )
 * )
 */
class CommentResource extends JsonResource
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
            'content' => $this->content,
            'user_id' => $this->user_id,
            'news_id' => $this->news_id,
            'parent_id' => $this->parent_id,
            'rating' => $this->rating,
            'is_approved' => $this->is_approved,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'user' => new UserResource($this->whenLoaded('user')),
            'news' => new NewsResource($this->whenLoaded('news')),
            'parent' => new self($this->whenLoaded('parent')),
            'replies' => self::collection($this->whenLoaded('replies')),
        ];
    }
}
