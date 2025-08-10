<?php

namespace App\Repositories;

use App\Contracts\Repository\FlashcardRepositoryInterface;
use App\Models\Deck;
use App\Models\Flashcard;
use App\Queries\FlashcardQuery;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class FlashcardRepository extends BaseRepository implements FlashcardRepositoryInterface
{
    public function __construct(Flashcard $model)
    {
        parent::__construct($model);
    }

    public function query(): FlashcardQuery
    {
        return FlashcardQuery::make();
    }

    public function getByDeck(Deck $deck): Collection
    {
        return $this->query()
            ->forDeck($deck)
            ->orderByLatest()
            ->get();
    }

    public function getByDeckPaginated(Deck $deck, int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->forDeck($deck)
            ->orderByLatest()
            ->paginate($perPage);
    }

    public function getByDeckAndId(Deck $deck, int $flashcardId): ?Flashcard
    {
        return $this->query()
            ->forDeck($deck)
            ->find($flashcardId);
    }

    public function createForDeck(Deck $deck, array $data): Flashcard
    {
        $data['deck_id'] = $deck->id;
        return $this->create($data);
    }

    public function getRandomFromDeck(Deck $deck, int $limit = null): Collection
    {
        $query = $this->query()
            ->forDeck($deck)
            ->random();

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }

    public function countByDeck(Deck $deck): int
    {
        return $this->query()
            ->forDeck($deck)
            ->count();
    }
} 