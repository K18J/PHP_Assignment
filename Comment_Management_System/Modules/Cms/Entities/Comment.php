<?php

namespace Modules\Cms\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'page_id',
        'user_id',
        'content',
        'status',
        'parent_id',
    ];

    protected $attributes = [
        'status' => 'pending',
    ];

    /**
        * Page the comment belongs to.
        */
    public function page(): BelongsTo
    {
        return $this->belongsTo('Modules\Cms\Entities\Page', 'page_id');
    }

    /**
        * Author of the comment.
        */
    public function author(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    /**
        * Direct replies to the comment.
        */
    public function replies(): HasMany
    {
        return $this->hasMany(static::class, 'parent_id')
            ->with(['author', 'replies']);
    }

    /**
        * Parent comment if this is a reply.
        */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(static::class, 'parent_id');
    }

    /**
        * Scope for only approved comments.
        */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }
}

