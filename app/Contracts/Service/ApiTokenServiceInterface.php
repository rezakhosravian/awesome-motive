<?php

namespace App\Contracts\Service;

use App\Models\ApiToken;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface ApiTokenServiceInterface
{
    /**
     * Get all API tokens for the authenticated user.
     *
     * @param User $user
     * @return Collection<ApiToken>
     */
    public function getUserTokens(User $user): Collection;

    /**
     * Create a new API token for the user.
     *
     * @param User $user
     * @param string $name
     * @param array $abilities
     * @param string|null $expiresAt
     * @return array{token: ApiToken, plainToken: string}
     * @throws \InvalidArgumentException
     */
    public function createToken(User $user, string $name, array $abilities = ['*'], ?string $expiresAt = null): array;

    /**
     * Delete an API token for the user.
     *
     * @param User $user
     * @param ApiToken $token
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function deleteToken(User $user, ApiToken $token): bool;

    /**
     * Validate token creation data.
     *
     * @param array $data
     * @return array Validated data
     * @throws \InvalidArgumentException
     */
    public function validateTokenData(array $data): array;

    /**
     * Check if user can create more tokens (rate limiting).
     *
     * @param User $user
     * @return bool
     */
    public function canCreateToken(User $user): bool;

    /**
     * Authenticate a token and update last used timestamp.
     *
     * @param string $token
     * @return ApiToken|null
     */
    public function authenticateToken(string $token): ?ApiToken;

    /**
     * Clean up expired tokens for all users.
     *
     * @return int Number of tokens deleted
     */
    public function cleanupExpiredTokens(): int;

    /**
     * Get token statistics for a user.
     *
     * @param User $user
     * @return array
     */
    public function getTokenStats(User $user): array;

    /**
     * Create token from validated request data with all business logic.
     * Handles validation, rate limiting, and creation.
     *
     * @param User $user
     * @param array $requestData
     * @return array{token: ApiToken, plainToken: string}
     * @throws \App\Exceptions\RateLimitException
     * @throws \Illuminate\Validation\ValidationException
     */
    public function createTokenFromRequest(User $user, array $requestData): array;
}