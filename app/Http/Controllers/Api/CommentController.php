<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\News;
use App\Services\CommentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Http\Resources\CommentResource;

/**
 * @OA\Tag(
 *     name="Comments",
 *     description="API Endpoints for managing comments"
 * )
 */
class CommentController extends Controller
{
    public function __construct(
        private readonly CommentService $commentService
    ) {
    }

    /**
     * @OA\Get(
     *     path="/v1/news/{news_id}/comments",
     *     summary="Get list of comments for news",
     *     tags={"Comments"},
     *     @OA\Parameter(
     *         name="news_id",
     *         in="path",
     *         required=true,
     *         description="News ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of comments",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Comment")),
     *             @OA\Property(property="links", type="object"),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="News not found"
     *     )
     * )
     */
    public function index(Request $request, News $news): JsonResponse
    {
        $comments = $news->comments()
            ->with(['user', 'parent', 'replies'])
            ->whereNull('parent_id')
            ->approved()
            ->latest()
            ->paginate($request->input('per_page', 15));

        return response()->json(CommentResource::collection($comments));
    }

    /**
     * @OA\Post(
     *     path="/v1/news/{news_id}/comments",
     *     summary="Create a new comment",
     *     tags={"Comments"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="news_id",
     *         in="path",
     *         required=true,
     *         description="News ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"content"},
     *             @OA\Property(property="content", type="string", example="Great article!"),
     *             @OA\Property(property="parent_id", type="integer", nullable=true, example=null)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Comment created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Comment created successfully"),
     *             @OA\Property(property="comment", ref="#/components/schemas/Comment")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="News not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function store(Request $request, News $news): JsonResponse
    {
        $validated = $request->validate([
            'content' => 'required|string',
            'parent_id' => 'nullable|exists:comments,id',
        ]);

        $comment = $news->comments()->create([
            'content' => $validated['content'],
            'user_id' => $request->user()->id,
            'parent_id' => $validated['parent_id'] ?? null,
            'rating' => 0,
            'is_approved' => true,
        ]);

        return response()->json([
            'message' => 'Comment created successfully',
            'comment' => new CommentResource($comment->load(['user', 'parent', 'replies'])),
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/v1/comments/{id}",
     *     summary="Get comment by ID",
     *     tags={"Comments"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Comment ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Comment details",
     *         @OA\JsonContent(
     *             @OA\Property(property="comment", ref="#/components/schemas/Comment")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Comment not found"
     *     )
     * )
     */
    public function show(Comment $comment): JsonResponse
    {
        return response()->json([
            'comment' => new CommentResource($comment->load(['user', 'news', 'parent', 'replies'])),
        ]);
    }

    /**
     * @OA\Put(
     *     path="/v1/comments/{id}",
     *     summary="Update comment",
     *     tags={"Comments"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Comment ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"content"},
     *             @OA\Property(property="content", type="string", example="Updated comment content")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Comment updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Comment updated successfully"),
     *             @OA\Property(property="comment", ref="#/components/schemas/Comment")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Comment not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function update(Request $request, Comment $comment): JsonResponse
    {
        $this->authorize('update', $comment);

        $validated = $request->validate([
            'content' => 'required|string',
        ]);

        $comment->update($validated);

        return response()->json([
            'message' => 'Comment updated successfully',
            'comment' => new CommentResource($comment->load(['user', 'news', 'parent', 'replies'])),
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/v1/comments/{id}",
     *     summary="Delete comment",
     *     tags={"Comments"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Comment ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Comment deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Comment deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Comment not found"
     *     )
     * )
     */
    public function destroy(Comment $comment): JsonResponse
    {
        $this->authorize('delete', $comment);

        $comment->delete();

        return response()->json([
            'message' => 'Comment deleted successfully',
        ]);
    }

    /**
     * @OA\Post(
     *     path="/v1/comments/{id}/rate",
     *     summary="Rate a comment",
     *     tags={"Comments"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Comment ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"rating"},
     *             @OA\Property(property="rating", type="integer", minimum=-1, maximum=1, example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Comment rated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Comment rated successfully"),
     *             @OA\Property(property="comment", ref="#/components/schemas/Comment")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Comment not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function rate(Request $request, Comment $comment): JsonResponse
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:-1|max:1',
        ]);

        $comment->increment('rating', $validated['rating']);

        return response()->json([
            'message' => 'Comment rated successfully',
            'comment' => new CommentResource($comment->load(['user', 'news', 'parent', 'replies'])),
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/comments/search",
     *     summary="Search comments",
     *     tags={"Comments"},
     *     @OA\Parameter(
     *         name="query",
     *         in="query",
     *         required=true,
     *         description="Search query",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="user_id",
     *         in="query",
     *         description="Filter by user ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="news_id",
     *         in="query",
     *         description="Filter by news ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="is_approved",
     *         in="query",
     *         description="Filter by approval status",
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Search results",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Comment")
     *         )
     *     )
     * )
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->get('query', '');
        $filters = $request->only(['user_id', 'news_id', 'is_approved']);

        $comments = $this->commentService->search($query, $filters);
        return response()->json($comments);
    }
}
