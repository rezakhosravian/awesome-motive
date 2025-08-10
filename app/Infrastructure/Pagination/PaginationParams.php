<?php

namespace App\Infrastructure\Pagination;

use Illuminate\Http\Request;

class PaginationParams
{
    public function __construct(
        public int $perPage,
        public int $page
    ) {}

    public static function fromRequest(Request $request, int $defaultPerPage = 15, int $maxPerPage = 50): self
    {
        $perPage = min((int) $request->input('per_page', $defaultPerPage), $maxPerPage);
        $perPage = max($perPage, 1);
        $page = max((int) $request->input('page', 1), 1);

        return new self($perPage, $page);
    }
}


