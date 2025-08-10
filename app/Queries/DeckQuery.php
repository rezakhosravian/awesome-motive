<?php

namespace App\Queries;

use App\Models\Deck;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class DeckQuery
{
    private Builder $query;

    public function __construct(Builder $query = null)
    {
        $this->query = $query ?? Deck::query();
    }

    public static function make(): self
    {
        return new self();
    }

    public function forUser(User $user): self
    {
        $this->query->where('user_id', $user->id);
        return $this;
    }

    public function publicOnly(): self
    {
        $this->query->where('is_public', true);
        return $this;
    }

    public function privateOnly(): self
    {
        $this->query->where('is_public', false);
        return $this;
    }

    public function search(string $term): self
    {
        $this->query->where(function ($q) use ($term) {
            // Use full-text search if available 
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%");
        });
        return $this;
    }

    public function withFlashcardCount(): self
    {
        $this->query->withCount('flashcards');
        return $this;
    }

    public function withUser(): self
    {
        $this->query->with(['user:id,name']);
        return $this;
    }

    public function withFlashcards(): self
    {
        $this->query->with(['flashcards' => function ($query) {
            $query->latest();
        }]);
        return $this;
    }

    public function hasFlashcards(): self
    {
        $this->query->has('flashcards');
        return $this;
    }

    public function withoutFlashcards(): self
    {
        $this->query->doesntHave('flashcards');
        return $this;
    }

    public function orderByLatest(): self
    {
        $this->query->latest();
        return $this;
    }

    public function orderByOldest(): self
    {
        $this->query->oldest();
        return $this;
    }

    public function orderByName(): self
    {
        $this->query->orderBy('name');
        return $this;
    }

    public function orderByFlashcardCount(): self
    {
        $this->query->orderBy('flashcards_count', 'desc');
        return $this;
    }

    public function limit(int $limit): self
    {
        $this->query->limit($limit);
        return $this;
    }

    public function get(): Collection
    {
        return $this->query->get();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->query->paginate($perPage);
    }

    public function first(): ?Deck
    {
        return $this->query->first();
    }

    public function find(int $id): ?Deck
    {
        return $this->query->find($id);
    }

    public function findOrFail(int $id): Deck
    {
        return $this->query->findOrFail($id);
    }

    public function count(): int
    {
        return $this->query->count();
    }

    public function exists(): bool
    {
        return $this->query->exists();
    }

    public function getQuery(): Builder
    {
        return $this->query;
    }
}
