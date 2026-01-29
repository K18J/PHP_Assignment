<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Asset extends Model
{
    protected $fillable = [
        'name',
        'serial_number',
        'category_id',
        'value',
        'status',
        'description',
        'purchase_date',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'purchase_date' => 'date',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}