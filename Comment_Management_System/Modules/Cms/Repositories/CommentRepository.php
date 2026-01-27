<?php

namespace Modules\Cms\Repositories;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Modules\Cms\Entities\Comment;

class CommentRepository
{
    /**
     * Cache lifetime in seconds.
     */
    private int $ttl = 300;

    public function listForPage(int $pageId, bool $approvedOnly = true)
    {
        $cacheKey = $this->cacheKey($pageId, $approvedOnly ? 'approved' : 'all');

        return Cache::remember($cacheKey, $this->ttl, function () use ($pageId, $approvedOnly) {
            $query = Comment::query()
                ->where('page_id', $pageId)
                ->whereNull('parent_id')
                ->with([
                    'author',
                    'replies' => function ($q) use ($approvedOnly) {
                        if ($approvedOnly) {
                            $q->approved();
                        }
                        $q->orderBy('created_at');
                    },
                    'replies.author',
                ])
                ->orderBy('created_at');

            if ($approvedOnly) {
                $query->approved();
            }

            return $query->get();
        });
    }

    public function find(int $id): ?Comment
    {
        $cacheKey = "comments:{$id}";

        return Cache::remember($cacheKey, $this->ttl, function () use ($id) {
            return Comment::with(['author', 'replies'])->find($id);
        });
    }

    public function create(array $data): Comment
    {
        $data['status'] = $data['status'] ?? 'pending';

        $comment = Comment::create($data);

        $this->flushPageCache($comment->page_id);

        return $comment->loadMissing(['author', 'replies']);
    }

    public function update(Comment $comment, array $data): Comment
    {
        $comment->fill($data);
        $comment->save();

        $this->flushPageCache($comment->page_id);
        Cache::forget("comments:{$comment->id}");

        return $comment->loadMissing(['author', 'replies']);
    }

    public function delete(Comment $comment): void
    {
        $pageId = $comment->page_id;

        $comment->delete();

        $this->flushPageCache($pageId);
        Cache::forget("comments:{$comment->id}");
    }

    public function approve(Comment $comment): Comment
    {
        $comment->status = 'approved';
        $comment->save();

        $this->flushPageCache($comment->page_id);
        Cache::forget("comments:{$comment->id}");

        return $comment;
    }

    public function reject(Comment $comment): Comment
    {
        $comment->status = 'rejected';
        $comment->save();

        $this->flushPageCache($comment->page_id);
        Cache::forget("comments:{$comment->id}");

        return $comment;
    }

    private function flushPageCache(int $pageId): void
    {
        Cache::forget($this->cacheKey($pageId, 'approved'));
        Cache::forget($this->cacheKey($pageId, 'all'));
    }

    private function cacheKey(int $pageId, string $suffix): string
    {
        return Str::lower("pages:{$pageId}:comments:{$suffix}");
    }
}

