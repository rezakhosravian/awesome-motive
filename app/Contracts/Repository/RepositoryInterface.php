<?php

namespace App\Contracts\Repository;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface RepositoryInterface
{
    /**
     * Get all records
     */
    public function all(): Collection;

    /**
     * Find a record by ID
     */
    public function find(int $id): ?Model;

    /**
     * Find a record by ID or fail
     */
    public function findOrFail(int $id): Model;

    /**
     * Create a new record
     */
    public function create(array $data): Model;

    /**
     * Update a record
     */
    public function update(Model $model, array $data): Model;

    /**
     * Delete a record
     */
    public function delete(Model $model): bool;

    /**
     * Get paginated records
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    /**
     * Find records by criteria
     */
    public function findBy(array $criteria): Collection;

    /**
     * Find a single record by criteria
     */
    public function findOneBy(array $criteria): ?Model;
} 