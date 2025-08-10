<?php

namespace App\Contracts\Service;

use App\DTOs\CreateFlashcardDTO;
use App\DTOs\UpdateFlashcardDTO;
use App\Models\Deck;
use App\Models\Flashcard;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface FlashcardServiceInterface
{
    /**
     * Create a new flashcard for a deck
     */
    public function createFlashcard(User $user, Deck $deck, CreateFlashcardDTO $dto): Flashcard;

    /**
     * Create a new flashcard for a deck from array (backward compatibility)
     */
    public function createFlashcardFromArray(User $user, Deck $deck, array $data): Flashcard;

    /**
     * Update an existing flashcard
     */
    public function updateFlashcard(User $user, Deck $deck, Flashcard $flashcard, UpdateFlashcardDTO $dto): Flashcard;

    /**
     * Update an existing flashcard from array (backward compatibility)
     */
    public function updateFlashcardFromArray(User $user, Deck $deck, Flashcard $flashcard, array $data): Flashcard;

    /**
     * Delete a flashcard from a deck
     */
    public function deleteFlashcard(User $user, Deck $deck, Flashcard $flashcard): bool;

    /**
     * Get flashcards for a deck with authorization check
     */
    public function getFlashcardsForDeck(Deck $deck, ?User $user = null, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get a specific flashcard with authorization check
     */
    public function getFlashcard(Deck $deck, Flashcard $flashcard, ?User $user = null): Flashcard;

    /**
     * Check if user can manage flashcards in a deck
     */
    public function canUserManageFlashcardsInDeck(User $user, Deck $deck): bool;

    /**
     * Check if user can view flashcards in a deck
     */
    public function canUserViewFlashcardsInDeck(Deck $deck, ?User $user = null): bool;

    /**
     * Get flashcard for user with complete business logic validation.
     * Handles deck validation, IDOR protection, and access control.
     * 
     * @param Deck $deck
     * @param Flashcard $flashcard
     * @param User $user
     * @return Flashcard
     * @throws \App\Exceptions\Flashcard\FlashcardNotFoundException
     * @throws \App\Exceptions\Flashcard\FlashcardAccessDeniedException
     */
    public function getFlashcardForUser(Deck $deck, Flashcard $flashcard, ?User $user = null): Flashcard;

    /**
     * Get flashcards for deck with complete business logic.
     * Handles access control and authorization.
     *
     * @param Deck $deck
     * @param User $user
     * @param int $perPage
     * @return LengthAwarePaginator
     * @throws \App\Exceptions\Deck\DeckAccessDeniedException
     */
    public function getFlashcardsForUserWithAccess(Deck $deck, User $user, int $perPage): LengthAwarePaginator;
}
