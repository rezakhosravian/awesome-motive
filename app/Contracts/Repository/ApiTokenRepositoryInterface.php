<?php

namespace App\Contracts\Repository;

use App\Models\ApiToken;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface ApiTokenRepositoryInterface extends RepositoryInterface
{
    /**
     * Get all API tokens for a user, ordered by creation date (newest first).
     *
     * @param User $user
     * @return Collection<ApiToken>
     */
    public function getByUser(User $user): Collection;

    /**
     * Create a new API token for a user.
     *
     * @param User $user
     * @param array $data
     * @return ApiToken
     */
    public function createForUser(User $user, array $data): ApiToken;

    /**
     * Find a token by its hashed value.
     *
     * @param string $hashedToken
     * @return ApiToken|null
     */
    public function findByHashedToken(string $hashedToken): ?ApiToken;

    /**
     * Update the last_used_at timestamp for a token.
     *
     * @param ApiToken $token
     * @return bool
     */
    public function updateLastUsed(ApiToken $token): bool;

    /**
     * Delete tokens that have expired.
     *
     * @return int Number of deleted tokens
     */
    public function deleteExpired(): int;

    /**
     * Count active (non-expired) tokens for a user.
     *
     * @param User $user
     * @return int
     */
    public function countActiveForUser(User $user): int;

    /**
     * Get active tokens for a user (non-expired).
     *
     * @param User $user
     * @return Collection<ApiToken>
     */
    public function getActiveByUser(User $user): Collection;
}