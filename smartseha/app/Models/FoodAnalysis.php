<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FoodAnalysis extends Model
{
    protected $fillable = [
        'user_id',
        'food_name',
        'image_path',
        'status',
        'model_used',
        'calories',
        'protein',
        'carbs',
        'fat',
        'minerals',
        'allergens',
        'error_message',
        'analysis_payload',
        'analyzed_date',
    ];

    protected function casts(): array
    {
        return [
            'minerals' => 'array',
            'allergens' => 'array',
            'analysis_payload' => 'array',
            'analyzed_date' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
