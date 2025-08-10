<?php

namespace App\Queries;

use App\Models\Deck;
use App\Models\Flashcard;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class FlashcardQuery
{
    private Builder $query;

    public function __construct(Builder $query = null)
    {
        $this->query = $query ?? Flashcard::query();
    }

    public static function make(): self
    {
        return new self();
    }

    public function forDeck(Deck $deck): self
    {
        $this->query->where('deck_id', $deck->id);
        return $this;
    }

    public function forDeckId(int $deckId): self
    {
        $this->query->where('deck_id', $deckId);
        return $this;
    }

    public function search(string $term): self
    {
        $this->query->where(function ($q) use ($term) {
            $q->where('question', 'like', "%{$term}%")
              ->orWhere('answer', 'like', "%{$term}%");
        });
        return $this;
    }

    public function withDeck(): self
    {
        $this->query->with('deck');
        return $this;
    }

    public function random(): self
    {
        $this->query->inRandomOrder();
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

    public function first(): ?Flashcard
    {
        return $this->query->first();
    }

    public function find(int $id): ?Flashcard
    {
        return $this->query->find($id);
    }

    public function findOrFail(int $id): Flashcard
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
