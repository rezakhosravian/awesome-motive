<?php

namespace App\DTOs;

use App\Http\Requests\StoreDeckRequest;
use Illuminate\Http\Request;

readonly class CreateDeckDTO
{
    public function __construct(
        public string $name,
        public ?string $description,
        public bool $isPublic,
        public int $userId
    ) {}

    public static function fromRequest(StoreDeckRequest $request): self
    {
        return new self(
            name: $request->validated('name'),
            description: $request->validated('description'),
            isPublic: $request->validated('is_public', false),
            userId: $request->user()->id
        );
    }

    public static function fromValidatedRequest(Request $request): self
    {
        return new self(
            name: $request->input('name'),
            description: $request->input('description'),
            isPublic: $request->boolean('is_public', false),
            userId: $request->user()->id
        );
    }

    public static function fromArray(array $data, int $userId): self
    {
        return new self(
            name: $data['name'],
            description: $data['description'] ?? null,
            isPublic: $data['is_public'] ?? false,
            userId: $userId
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'is_public' => $this->isPublic,
            'user_id' => $this->userId,
        ];
    }

    public function toEloquentAttributes(): array
    {
        return $this->toArray();
    }

    public function validate(): void
    {
        // No-op when constructed from validated sources; form requests enforce validation
    }
}
