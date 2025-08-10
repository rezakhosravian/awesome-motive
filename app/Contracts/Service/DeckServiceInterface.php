<?php

namespace App\Contracts\Service;

use App\DTOs\CreateDeckDTO;
use App\DTOs\UpdateDeckDTO;
use App\Models\Deck;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface DeckServiceInterface
{
    /**
     * Create a new deck for user
     */
    public function createDeck(User $user, CreateDeckDTO $dto): Deck;

    /**
     * Create a new deck for user from array (backward compatibility)
     */
    public function createDeckFromArray(User $user, array $data): Deck;

    /**
     * Update user's deck
     */
    public function updateDeck(User $user, Deck $deck, UpdateDeckDTO $dto): Deck;

    /**
     * Update user's deck from array (backward compatibility)
     */
    public function updateDeckFromArray(User $user, Deck $deck, array $data): Deck;

    /**
     * Delete user's deck
     */
    public function deleteDeck(User $user, Deck $deck): bool;

    /**
     * Get user's decks
     */
    public function getUserDecks(User $user, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get public decks
     */
    public function getPublicDecks(int $perPage = 15): LengthAwarePaginator;

    /**
     * Search decks
     */
    public function searchDecks(string $query, bool $publicOnly = false, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get deck for study
     */
    public function getDeckForStudy(int $deckId, ?User $user = null): Deck;

    /**
     * Check if user can access deck
     */
    public function canUserAccessDeck(Deck $deck, ?User $user = null): bool;

    /**
     * Get deck statistics
     */
    public function getDeckStats(Deck $deck): array;

    /**
     * Get deck for user with complete access control and business logic.
     * 
     * @param Deck $deck
     * @param User $user
     * @return Deck
     * @throws \App\Exceptions\Deck\DeckNotFoundException
     * @throws \App\Exceptions\Deck\DeckAccessDeniedException
     */
    public function getDeckForUser(Deck $deck, User $user): Deck;
} 