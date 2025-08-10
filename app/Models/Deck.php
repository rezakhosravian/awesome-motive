<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Deck extends Model
{
    use HasFactory;

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'description',
        'is_public',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'is_public' => 'boolean',
    ];

    /**
     * Get the user that owns the deck.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the flashcards for the deck.
     */
    public function flashcards(): HasMany
    {
        return $this->hasMany(Flashcard::class);
    }

    /**
     * Scope a query to only include public decks.
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }


    /**
     * Generate a unique slug for the deck.
     */
    public static function generateSlug(?string $name): string
    {
        if (empty($name)) {
            return 'deck-' . uniqid();
        }
        
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;

        while (static::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($deck) {
            if (empty($deck->slug)) {
                $deck->slug = static::generateSlug($deck->name);
            }
        });

        static::updating(function ($deck) {
            if ($deck->isDirty('name') && !$deck->isDirty('slug')) {
                $deck->slug = static::generateSlug($deck->name);
            }
        });
    }
}
