<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecommendationLog extends Model
{
    protected $fillable = [
        'user_id',
        'category',
        'icon',
        'title',
        'description',
        'created_date',
    ];

    protected function casts(): array
    {
        return [
            'created_date' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
