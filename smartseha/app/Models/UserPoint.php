<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPoint extends Model
{
    protected $fillable = [
        'user_id',
        'action_key',
        'unique_key',
        'meta',
        'activity',
        'points',
        'recorded_date',
    ];

    protected function casts(): array
    {
        return [
            'recorded_date' => 'date',
            'meta' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
