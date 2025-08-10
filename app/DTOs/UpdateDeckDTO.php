<?php

namespace App\DTOs;

use Illuminate\Http\Request;

readonly class UpdateDeckDTO
{
    public function __construct(
        public string $name,
        public ?string $description,
        public bool $isPublic
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            name: $request->validated('name'),
            description: $request->validated('description'),
            isPublic: $request->validated('is_public', false)
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            description: $data['description'] ?? null,
            isPublic: $data['is_public'] ?? false
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'is_public' => $this->isPublic,
        ];
    }

    public function validate(): void
    {
        if (empty(trim($this->name))) {
            throw new \InvalidArgumentException(__('validation.deck.name_required'));
        }

        if (strlen($this->name) > 255) {
            throw new \InvalidArgumentException(__('validation.deck.name_max'));
        }

        if ($this->description && strlen($this->description) > 1000) {
            throw new \InvalidArgumentException(__('validation.deck.description_max'));
        }
    }
}
