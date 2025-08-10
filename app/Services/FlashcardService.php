<?php

namespace App\Services;

use App\Contracts\Service\FlashcardServiceInterface;
use App\Contracts\Repository\FlashcardRepositoryInterface;
use App\Contracts\Service\DeckServiceInterface;
use App\DTOs\CreateFlashcardDTO;
use App\DTOs\UpdateFlashcardDTO;
use App\Exceptions\Flashcard\FlashcardNotFoundException;
use App\Exceptions\Flashcard\FlashcardAccessDeniedException;
use App\Models\Deck;
use App\Models\Flashcard;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Pagination\LengthAwarePaginator;

class FlashcardService implements FlashcardServiceInterface
{
    use AuthorizesRequests;

    public function __construct(
        private readonly FlashcardRepositoryInterface $flashcardRepository,
        private readonly DeckServiceInterface $deckService
    ) {}

    public function createFlashcard(User $user, Deck $deck, CreateFlashcardDTO $dto): Flashcard
    {
        $this->authorize('update', $deck);
        
        if (!$this->canUserManageFlashcardsInDeck($user, $deck)) {
            throw new ModelNotFoundException(__('messages.flashcard.create_permission_denied'));
        }

        $dto->validate();

        return $this->flashcardRepository->createForDeck($deck, $dto->toArray());
    }

    public function createFlashcardFromArray(User $user, Deck $deck, array $data): Flashcard
    {
        $dto = CreateFlashcardDTO::fromArray($data, $deck->id);
        return $this->createFlashcard($user, $deck, $dto);
    }

    public function updateFlashcard(User $user, Deck $deck, Flashcard $flashcard, UpdateFlashcardDTO $dto): Flashcard
    {
        $this->authorize('update', $deck);
        
        if (!$this->canUserManageFlashcardsInDeck($user, $deck)) {
            throw new ModelNotFoundException(__('messages.flashcard.update_permission_denied'));
        }

        if ($flashcard->deck_id !== $deck->id) {
            throw new ModelNotFoundException(__('messages.flashcard.not_found_in_deck'));
        }

        $dto->validate();

        return $this->flashcardRepository->update($flashcard, $dto->toArray());
    }

    public function updateFlashcardFromArray(User $user, Deck $deck, Flashcard $flashcard, array $data): Flashcard
    {
        $dto = UpdateFlashcardDTO::fromArray($data);
        return $this->updateFlashcard($user, $deck, $flashcard, $dto);
    }

    public function deleteFlashcard(User $user, Deck $deck, Flashcard $flashcard): bool
    {
        $this->authorize('update', $deck);
        
        if (!$this->canUserManageFlashcardsInDeck($user, $deck)) {
            throw new ModelNotFoundException('You do not have permission to delete flashcards in this deck.');
        }

        if ($flashcard->deck_id !== $deck->id) {
            throw new ModelNotFoundException('Flashcard not found in this deck.');
        }

        return $this->flashcardRepository->delete($flashcard);
    }

    public function getFlashcardsForDeck(Deck $deck, ?User $user = null, int $perPage = null): LengthAwarePaginator
    {
        if (!$this->canUserViewFlashcardsInDeck($deck, $user)) {
            throw new ModelNotFoundException('You do not have permission to view flashcards in this deck.');
        }

        $perPage = $perPage ?? config('flashcard.pagination.default_per_page');
        return $this->flashcardRepository->getByDeckPaginated($deck, $perPage);
    }

    public function getFlashcard(Deck $deck, Flashcard $flashcard, ?User $user = null): Flashcard
    {
        if (!$this->canUserViewFlashcardsInDeck($deck, $user)) {
            throw new ModelNotFoundException('You do not have permission to view flashcards in this deck.');
        }

        // Ensure flashcard belongs to the deck
        if ($flashcard->deck_id !== $deck->id) {
            throw new ModelNotFoundException('Flashcard not found in this deck.');
        }

        return $flashcard;
    }

    /**
     * Get flashcard with proper business logic validation and custom exceptions
     */
    public function getFlashcardForUser(Deck $deck, Flashcard $flashcard, ?User $user = null): Flashcard
    {
        // Check if user can access deck (handles public/private logic)
        if (!$this->canUserViewFlashcardsInDeck($deck, $user)) {
            throw new FlashcardAccessDeniedException(__('api.flashcards.not_found'));
        }

        // Ensure flashcard belongs to the deck (IDOR protection)
        if ($flashcard->deck_id !== $deck->id) {
            throw new FlashcardNotFoundException(__('api.flashcards.not_found'));
        }

        return $flashcard;
    }

    public function canUserManageFlashcardsInDeck(User $user, Deck $deck): bool
    {
        // Only deck owners can manage flashcards
        return $deck->user_id === $user->id;
    }

    public function canUserViewFlashcardsInDeck(Deck $deck, ?User $user = null): bool
    {
        // Use the deck service to check access permissions
        return $this->deckService->canUserAccessDeck($deck, $user);
    }

    public function getFlashcardsForUserWithAccess(Deck $deck, User $user, int $perPage): LengthAwarePaginator
    {
        // Business logic: Check access control
        if (!$this->canUserViewFlashcardsInDeck($deck, $user)) {
            throw new \App\Exceptions\Deck\DeckAccessDeniedException(__('api.decks.not_found'));
        }

        // Delegate to repository for data retrieval
        return $this->flashcardRepository->getByDeckPaginated($deck, $perPage);
    }
}
