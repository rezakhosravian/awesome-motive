<?php

namespace App\Contracts\Repository;

use App\Models\Deck;
use App\Models\Flashcard;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface FlashcardRepositoryInterface extends RepositoryInterface
{
    /**
     * Get flashcards for a deck
     */
    public function getByDeck(Deck $deck): Collection;

    /**
     * Get flashcards for a deck with pagination
     */
    public function getByDeckPaginated(Deck $deck, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get flashcard by deck and flashcard ID
     */
    public function getByDeckAndId(Deck $deck, int $flashcardId): ?Flashcard;

    /**
     * Create flashcard for deck
     */
    public function createForDeck(Deck $deck, array $data): Flashcard;

    /**
     * Get random flashcards from deck
     */
    public function getRandomFromDeck(Deck $deck, int $limit = null): Collection;

    /**
     * Count flashcards in deck
     */
    public function countByDeck(Deck $deck): int;
} 