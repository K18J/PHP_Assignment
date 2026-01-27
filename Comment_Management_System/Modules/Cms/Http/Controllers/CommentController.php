<?php

namespace Modules\Cms\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Cms\Entities\Comment;
use Modules\Cms\Http\Requests\CommentRequest;
use Modules\Cms\Repositories\CommentRepository;
use Modules\Cms\Resources\CommentResource;

class CommentController
{
    public function __construct(private readonly CommentRepository $comments)
    {
    }

    public function index(Request $request, int $page): JsonResponse
    {
        // By default return all statuses; allow optional filter
        $approvedOnly = $request->boolean('approved_only', false);

        $items = $this->comments->listForPage($page, approvedOnly: $approvedOnly);

        return response()->json(CommentResource::collection($items));
    }

    public function store(CommentRequest $request, int $page): JsonResponse
    {
        $data = $request->validated();
        $data['page_id'] = $page;
        $data['user_id'] = $request->user()?->id ?? $request->input('user_id');
        $data['status'] = 'pending';

        $comment = $this->comments->create($data);

        return response()->json(new CommentResource($comment), 201);
    }

    public function update(CommentRequest $request, Comment $comment): JsonResponse
    {
        $data = $request->validated();

        if (isset($data['status']) && ! ($request->user()?->is_admin)) {
            unset($data['status']);
        }

        // Prevent moving comment between pages
        $data['page_id'] = $comment->page_id;

        $comment = $this->comments->update($comment, $data);

        return response()->json(new CommentResource($comment));
    }

    public function destroy(Comment $comment): JsonResponse
    {
        $this->comments->delete($comment);

        return response()->json(null, 204);
    }

    public function approve(Comment $comment): JsonResponse
    {
        $comment = $this->comments->approve($comment);

        return response()->json(new CommentResource($comment));
    }

    public function reject(Comment $comment): JsonResponse
    {
        $comment = $this->comments->reject($comment);

        return response()->json(new CommentResource($comment));
    }
}

