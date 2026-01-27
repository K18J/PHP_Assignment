<?php

namespace Modules\Cms\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \Modules\Cms\Entities\Comment */
class CommentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'page_id' => $this->page_id,
            'user_id' => $this->user_id,
            'content' => $this->content,
            'status' => $this->status,
            'parent_id' => $this->parent_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'author' => $this->whenLoaded('author', function () {
                return [
                    'id' => $this->author->id,
                    'name' => $this->author->name,
                    'email' => $this->author->email,
                ];
            }),
            'page' => $this->whenLoaded('page', function () {
                return [
                    'id' => $this->page->id,
                    'title' => $this->page->title,
                ];
            }),
            'replies' => CommentResource::collection($this->whenLoaded('replies')),
        ];
    }
}

