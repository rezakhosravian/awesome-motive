<?php

namespace App\DTOs;

use Illuminate\Http\Request;

readonly class ShowFlashcardDTO
{
    public function __construct(
        public int $deckId,
        public int $flashcardId,
        public ?int $userId = null
    ) {}

    public static function fromRequest(Request $request, int $deckId, int $flashcardId): self
    {
        return new self(
            deckId: $deckId,
            flashcardId: $flashcardId,
            userId: $request->user()?->id
        );
    }

    public static function fromRoute(int $deckId, int $flashcardId, ?int $userId = null): self
    {
        return new self(
            deckId: $deckId,
            flashcardId: $flashcardId,
            userId: $userId
        );
    }

    public function toArray(): array
    {
        return [
            'deck_id' => $this->deckId,
            'flashcard_id' => $this->flashcardId,
            'user_id' => $this->userId,
        ];
    }

    public function validate(): void
    {
        // Validation logic if needed
        if ($this->deckId <= 0) {
            throw new \InvalidArgumentException('Deck ID must be positive');
        }
        
        if ($this->flashcardId <= 0) {
            throw new \InvalidArgumentException('Flashcard ID must be positive');
        }
    }
}
