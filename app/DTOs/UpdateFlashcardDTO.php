<?php

namespace App\DTOs;

use Illuminate\Http\Request;

readonly class UpdateFlashcardDTO
{
    public function __construct(
        public string $question,
        public string $answer
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            question: $request->validated('question'),
            answer: $request->validated('answer')
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            question: $data['question'],
            answer: $data['answer']
        );
    }

    public function toArray(): array
    {
        return [
            'question' => $this->question,
            'answer' => $this->answer,
        ];
    }

    public function validate(): void
    {
        if (empty(trim($this->question))) {
            throw new \InvalidArgumentException(__('messages.validation.flashcard_question_required'));
        }

        if (empty(trim($this->answer))) {
            throw new \InvalidArgumentException(__('messages.validation.flashcard_answer_required'));
        }

        if (strlen($this->question) > 1000) {
            throw new \InvalidArgumentException(__('messages.validation.flashcard_question_max_length'));
        }

        if (strlen($this->answer) > 1000) {
            throw new \InvalidArgumentException(__('messages.validation.flashcard_answer_max_length'));
        }
    }
}
