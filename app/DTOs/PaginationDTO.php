<?php

namespace App\DTOs;

use Illuminate\Http\Request;

readonly class PaginationDTO
{
    public function __construct(
        public int $page,
        public int $perPage,
        public int $maxPerPage,
        public int $minPerPage = 1
    ) {}

    public static function fromRequest(Request $request): self
    {
        $defaultPerPage = config('flashcard.pagination.default_per_page');
        $maxPerPage = config('flashcard.pagination.max_per_page');
        $minPerPage = config('flashcard.pagination.min_per_page');

        $perPage = (int) $request->input('per_page', $defaultPerPage);
        $perPage = min($perPage, $maxPerPage);
        $perPage = max($perPage, $minPerPage);

        $page = max((int) $request->input('page', 1), 1);

        return new self(
            page: $page,
            perPage: $perPage,
            maxPerPage: $maxPerPage,
            minPerPage: $minPerPage
        );
    }

    public static function fromConfig(int $page = 1, ?int $perPage = null): self
    {
        $defaultPerPage = config('flashcard.pagination.default_per_page');
        $maxPerPage = config('flashcard.pagination.max_per_page');
        $minPerPage = config('flashcard.pagination.min_per_page');

        $perPage = $perPage ?? $defaultPerPage;
        $perPage = min($perPage, $maxPerPage);
        $perPage = max($perPage, $minPerPage);

        return new self(
            page: max($page, 1),
            perPage: $perPage,
            maxPerPage: $maxPerPage,
            minPerPage: $minPerPage
        );
    }

    public function toArray(): array
    {
        return [
            'page' => $this->page,
            'per_page' => $this->perPage,
            'max_per_page' => $this->maxPerPage,
            'min_per_page' => $this->minPerPage,
        ];
    }

    public function validate(): void
    {
        if ($this->page < 1) {
            throw new \InvalidArgumentException('Page must be at least 1');
        }

        if ($this->perPage < $this->minPerPage || $this->perPage > $this->maxPerPage) {
            throw new \InvalidArgumentException(
                "Per page must be between {$this->minPerPage} and {$this->maxPerPage}"
            );
        }
    }
}
