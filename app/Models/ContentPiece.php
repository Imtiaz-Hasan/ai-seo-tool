<?php

namespace App\Models;

use Database\Factories\ContentPieceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContentPiece extends Model
{
    /** @use HasFactory<ContentPieceFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id', 'title', 'target_keyword', 'target_word_count',
        'body', 'meta_title', 'meta_description', 'last_score',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function generations(): HasMany
    {
        return $this->hasMany(Generation::class)->latest();
    }
}
