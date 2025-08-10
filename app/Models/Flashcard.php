<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Flashcard extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'deck_id',
        'question',
        'answer',
    ];

    /**
     * Get the deck that owns the flashcard.
     */
    public function deck(): BelongsTo
    {
        return $this->belongsTo(Deck::class);
    }
}
