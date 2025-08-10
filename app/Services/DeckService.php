<?php

namespace App\Services;

use App\Contracts\Service\DeckServiceInterface;
use App\Contracts\Repository\DeckRepositoryInterface;
use App\DTOs\CreateDeckDTO;
use App\DTOs\UpdateDeckDTO;
use App\Events\DeckCreated;
use App\Models\Deck;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class DeckService implements DeckServiceInterface
{
    use AuthorizesRequests;

    protected DeckRepositoryInterface $deckRepository;

    public function __construct(DeckRepositoryInterface $deckRepository)
    {
        $this->deckRepository = $deckRepository;
    }

    public function createDeck(User $user, CreateDeckDTO $dto): Deck
    {
        $dto->validate();
        $deck = $this->deckRepository->create($dto->toArray());
        DeckCreated::dispatch($deck);
        return $deck;
    }

    public function createDeckFromArray(User $user, array $data): Deck
    {
        $dto = CreateDeckDTO::fromArray($data, $user->id);
        return $this->createDeck($user, $dto);
    }

    public function updateDeck(User $user, Deck $deck, UpdateDeckDTO $dto): Deck
    {
        $this->authorize('update', $deck);

        if ($deck->user_id !== $user->id) {
            throw new ModelNotFoundException(__('messages.deck.update_permission_denied'));
        }

        $dto->validate();

        return $this->deckRepository->update($deck, $dto->toArray());
    }

    public function updateDeckFromArray(User $user, Deck $deck, array $data): Deck
    {
        $dto = UpdateDeckDTO::fromArray($data);
        return $this->updateDeck($user, $deck, $dto);
    }

    public function deleteDeck(User $user, Deck $deck): bool
    {
        $this->authorize('delete', $deck);

        if ($deck->user_id !== $user->id) {
            throw new ModelNotFoundException(__('messages.deck.delete_permission_denied'));
        }

        return $this->deckRepository->delete($deck);
    }

    public function getUserDecks(User $user, int $perPage = null): LengthAwarePaginator
    {
        $perPage = $perPage ?? config('flashcard.pagination.default_per_page');
        return $this->deckRepository->getUserDecksPaginated($user, $perPage);
    }

    public function getPublicDecks(int $perPage = null): LengthAwarePaginator
    {
        $perPage = $perPage ?? config('flashcard.pagination.default_per_page');
        return $this->deckRepository->getPublicDecksPaginated($perPage);
    }

    public function searchDecks(string $query, bool $publicOnly = false, int $perPage = null): LengthAwarePaginator
    {
        $perPage = $perPage ?? config('flashcard.pagination.default_per_page');
        return $this->deckRepository->searchPaginated($query, $publicOnly, $perPage);
    }

    public function getDeckForStudy(int $deckId, ?User $user = null): Deck
    {
        $deck = $this->deckRepository->getDeckWithFlashcards($deckId);

        if (!$deck) {
            throw new ModelNotFoundException(__('messages.deck.not_found'));
        }

        if (!$this->canUserAccessDeck($deck, $user)) {
            throw new ModelNotFoundException(__('messages.deck.access_denied'));
        }

        if ($deck->flashcards->isEmpty()) {
            throw new \InvalidArgumentException(__('messages.deck.no_flashcards'));
        }

        return $deck;
    }

    public function canUserAccessDeck(Deck $deck, ?User $user = null): bool
    {
        if ($deck->is_public) {
            return true;
        }

        return $user && $deck->user_id === $user->id;
    }

    public function getDeckStats(Deck $deck): array
    {
        $flashcardCount = $deck->flashcards_count ?? $deck->flashcards()->count();
        
        return [
            'flashcard_count' => $flashcardCount,
            'is_public' => $deck->is_public,
            'created_at' => $deck->created_at,
            'updated_at' => $deck->updated_at,
            'owner' => $deck->user->name ?? 'Unknown',
        ];
    }

    public function getDeckForUser(Deck $deck, User $user): Deck
    {
        // Business logic: Check if user can access the deck
        if (!$this->canUserAccessDeck($deck, $user)) {
            // Return 404 instead of 403 to avoid leaking information about private decks
            throw new \App\Exceptions\Deck\DeckNotFoundException(__('api.decks.not_found'));
        }

        // Load necessary relationships for the response
        return $deck->load(['user', 'flashcards']);
    }
}
