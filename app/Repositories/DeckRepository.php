<?php

namespace App\Repositories;

use App\Contracts\Repository\DeckRepositoryInterface;
use App\Models\Deck;
use App\Models\User;
use App\Queries\DeckQuery;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class DeckRepository extends BaseRepository implements DeckRepositoryInterface
{
    public function __construct(Deck $model)
    {
        parent::__construct($model);
    }

    public function query(): DeckQuery
    {
        return DeckQuery::make();
    }

    public function getUserDecks(User $user): Collection
    {
        return $this->query()
            ->forUser($user)
            ->withFlashcardCount()
            ->orderByLatest()
            ->get();
    }

    public function getUserDecksPaginated(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->forUser($user)
            ->withFlashcardCount()
            ->orderByLatest()
            ->paginate($perPage);
    }

    public function getPublicDecks(): Collection
    {
        return $this->query()
            ->publicOnly()
            ->withUser()
            ->withFlashcardCount()
            ->orderByLatest()
            ->get();
    }

    public function getPublicDecksPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->publicOnly()
            ->withUser()
            ->withFlashcardCount()
            ->orderByLatest()
            ->paginate($perPage);
    }

    public function search(string $term, bool $publicOnly = false): Collection
    {
        $query = $this->query()
            ->search($term)
            ->withUser()
            ->withFlashcardCount()
            ->orderByLatest();

        if ($publicOnly) {
            $query->publicOnly();
        }

        return $query->get();
    }

    public function searchPaginated(string $term, bool $publicOnly = false, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->query()
            ->search($term)
            ->withUser()
            ->withFlashcardCount()
            ->orderByLatest();

        if ($publicOnly) {
            $query->publicOnly();
        }

        return $query->paginate($perPage);
    }

    public function getDeckWithFlashcards(int $deckId): ?Deck
    {
        return $this->query()
            ->withFlashcards()
            ->find($deckId);
    }

    public function getUserDeck(User $user, int $deckId): ?Deck
    {
        return $this->query()
            ->forUser($user)
            ->find($deckId);
    }
} 