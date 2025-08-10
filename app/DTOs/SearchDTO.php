<?php

namespace App\DTOs;

use App\Exceptions\InvalidOperationException;
use Illuminate\Http\Request;

readonly class SearchDTO
{
    public function __construct(
        public string $query,
        public bool $publicOnly = false,
        public ?PaginationDTO $pagination = null
    ) {}

    public static function fromRequest(Request $request, bool $publicOnly = false): self
    {
        $query = trim((string) $request->input('q', ''));
        
        return new self(
            query: $query,
            publicOnly: $publicOnly,
            pagination: PaginationDTO::fromRequest($request)
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            query: trim((string) ($data['query'] ?? $data['q'] ?? '')),
            publicOnly: (bool) ($data['public_only'] ?? false),
            pagination: isset($data['pagination']) 
                ? PaginationDTO::fromConfig($data['pagination']['page'] ?? 1, $data['pagination']['per_page'] ?? null)
                : PaginationDTO::fromConfig()
        );
    }

    public function toArray(): array
    {
        return [
            'query' => $this->query,
            'public_only' => $this->publicOnly,
            'pagination' => $this->pagination?->toArray(),
        ];
    }

    public function validate(): void
    {
        $minLength = config('flashcard.search.min_query_length');
        $maxLength = config('flashcard.search.max_query_length');

        if (empty($this->query)) {
            throw InvalidOperationException::insufficientPermissions(
                'search',
                __('validation.search.query_required')
            );
        }

        if (strlen($this->query) < $minLength) {
            throw InvalidOperationException::insufficientPermissions(
                'search',
                __('validation.search.query_min', ['min' => $minLength])
            );
        }

        if (strlen($this->query) > $maxLength) {
            throw InvalidOperationException::insufficientPermissions(
                'search',
                __('validation.search.query_max', ['max' => $maxLength])
            );
        }

        $this->pagination?->validate();
    }

    public function isEmpty(): bool
    {
        return empty(trim($this->query));
    }

    public function getQuery(): string
    {
        return $this->query;
    }

    public function getPagination(): PaginationDTO
    {
        return $this->pagination ?? PaginationDTO::fromConfig();
    }
}
