<?php

namespace App\Contracts\Repository;

use App\Models\Deck;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface DeckRepositoryInterface extends RepositoryInterface
{
    /**
     * Get user's decks
     */
    public function getUserDecks(User $user): Collection;

    /**
     * Get user's decks with pagination
     */
    public function getUserDecksPaginated(User $user, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get public decks
     */
    public function getPublicDecks(): Collection;

    /**
     * Get public decks with pagination
     */
    public function getPublicDecksPaginated(int $perPage = 15): LengthAwarePaginator;

    /**
     * Search decks by name or description
     */
    public function search(string $query, bool $publicOnly = false): Collection;

    /**
     * Search decks with pagination
     */
    public function searchPaginated(string $query, bool $publicOnly = false, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get deck with flashcards
     */
    public function getDeckWithFlashcards(int $deckId): ?Deck;

    /**
     * Get user's deck by ID
     */
    public function getUserDeck(User $user, int $deckId): ?Deck;
} 