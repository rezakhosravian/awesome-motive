<?php

namespace App\Services;

use App\Contracts\Repository\ApiTokenRepositoryInterface;
use App\Contracts\Service\ApiTokenServiceInterface;
use App\Models\ApiToken;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use InvalidArgumentException;

class ApiTokenService implements ApiTokenServiceInterface
{
    /**
     * Read maximum tokens per user from configuration
     */
    private function getMaxTokensPerUser(): int
    {
        return (int) config('flashcard.api.rate_limits.api_tokens_per_user', 10);
    }

    /**
     * Read valid abilities from configuration
     *
     * @return array<int, string>
     */
    private function getValidAbilities(): array
    {
        return (array) config('flashcard.api.token.valid_abilities', ['read', 'write', 'delete', 'admin', '*']);
    }

    public function __construct(
        private ApiTokenRepositoryInterface $tokenRepository
    ) {}

    /**
     * Get all API tokens for the authenticated user.
     *
     * @param User $user
     * @return Collection<ApiToken>
     */
    public function getUserTokens(User $user): Collection
    {
        return $this->tokenRepository->getByUser($user);
    }

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
    public function createToken(User $user, string $name, array $abilities = ['*'], ?string $expiresAt = null): array
    {
        if (!$this->canCreateToken($user)) {
            $limit = $this->getMaxTokensPerUser();
            throw new InvalidArgumentException("Maximum number of tokens ({$limit}) reached for user.");
        }

        $this->validateTokenName($name);
        $this->validateAbilities($abilities);

        $plainToken = ApiToken::generateToken();
        $hashedToken = hash('sha256', $plainToken);

        $tokenData = [
            'name' => $name,
            'token' => $hashedToken,
            'abilities' => $abilities,
            'expires_at' => $expiresAt ? Carbon::parse($expiresAt) : null,
        ];

        $token = $this->tokenRepository->createForUser($user, $tokenData);

        return [
            'token' => $token,
            'plainToken' => $plainToken,
        ];
    }

    /**
     * Delete an API token for the user.
     *
     * @param User $user
     * @param ApiToken $token
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function deleteToken(User $user, ApiToken $token): bool
    {
        if ($token->user_id !== $user->id) {
            throw new InvalidArgumentException("Token does not belong to the user.");
        }

        return $this->tokenRepository->delete($token);
    }

    /**
     * Validate token creation data.
     *
     * @param array $data
     * @return array Validated data
     * @throws \InvalidArgumentException
     */
    public function validateTokenData(array $data): array
    {
        $validated = [];

        // Validate name
        if (empty($data['name']) || !is_string($data['name'])) {
            throw new InvalidArgumentException("Token name is required and must be a string.");
        }
        if (strlen($data['name']) > 255) {
            throw new InvalidArgumentException("Token name must not exceed 255 characters.");
        }
        $validated['name'] = trim($data['name']);

        // Validate abilities
        $abilities = $data['abilities'] ?? ['*'];
        if (!is_array($abilities)) {
            throw new InvalidArgumentException("Abilities must be an array.");
        }
        $this->validateAbilities($abilities);
        $validated['abilities'] = $abilities;

        // Validate expiration date
        if (!empty($data['expires_at'])) {
            try {
                $expiresAt = Carbon::parse($data['expires_at']);
                if ($expiresAt->isPast()) {
                    throw new InvalidArgumentException("Expiration date must be in the future.");
                }
                $validated['expires_at'] = $expiresAt->toDateTimeString();
            } catch (\Exception $e) {
                throw new InvalidArgumentException("Invalid expiration date format.");
            }
        }

        return $validated;
    }

    /**
     * Check if user can create more tokens (rate limiting).
     *
     * @param User $user
     * @return bool
     */
    public function canCreateToken(User $user): bool
    {
        $activeTokenCount = $this->tokenRepository->countActiveForUser($user);
        return $activeTokenCount < $this->getMaxTokensPerUser();
    }

    /**
     * Authenticate a token and update last used timestamp.
     *
     * @param string|null $token
     * @return ApiToken|null
     */
    public function authenticateToken(?string $token): ?ApiToken
    {
        if (!$token) {
            return null;
        }

        $hashedToken = hash('sha256', $token);
        $apiToken = $this->tokenRepository->findByHashedToken($hashedToken);

        if ($apiToken && !$apiToken->isExpired()) {
            $this->tokenRepository->updateLastUsed($apiToken);
            return $apiToken;
        }

        return null;
    }

    /**
     * Clean up expired tokens for all users.
     *
     * @return int Number of tokens deleted
     */
    public function cleanupExpiredTokens(): int
    {
        return $this->tokenRepository->deleteExpired();
    }

    /**
     * Get token statistics for a user.
     *
     * @param User $user
     * @return array
     */
    public function getTokenStats(User $user): array
    {
        $allTokens = $this->tokenRepository->getByUser($user);
        $activeTokens = $this->tokenRepository->getActiveByUser($user);

        return [
            'total_tokens' => $allTokens->count(),
            'active_tokens' => $activeTokens->count(),
            'expired_tokens' => $allTokens->count() - $activeTokens->count(),
            'max_allowed' => $this->getMaxTokensPerUser(),
            'can_create_more' => $this->canCreateToken($user),
            'recently_used' => $allTokens->where('last_used_at', '>', now()->subDays(7))->count(),
        ];
    }

    /**
     * Validate token name.
     *
     * @param string $name
     * @throws \InvalidArgumentException
     */
    private function validateTokenName(string $name): void
    {
        if (empty(trim($name))) {
            throw new InvalidArgumentException("Token name cannot be empty.");
        }

        if (strlen($name) > 255) {
            throw new InvalidArgumentException("Token name must not exceed 255 characters.");
        }

        if (preg_match('/[<>"\']/', $name)) {
            throw new InvalidArgumentException("Token name contains invalid characters.");
        }
    }

    /**
     * Validate abilities array.
     *
     * @param array $abilities
     * @throws \InvalidArgumentException
     */
    private function validateAbilities(array $abilities): void
    {
        if (empty($abilities)) {
            throw new InvalidArgumentException("At least one ability must be specified.");
        }

        $validAbilities = $this->getValidAbilities();

        foreach ($abilities as $ability) {
            if (!is_string($ability) || !in_array($ability, $validAbilities, true)) {
                throw new InvalidArgumentException(
                    "Invalid ability '{$ability}'. Valid abilities: " . implode(', ', $validAbilities)
                );
            }
        }
    }

    public function createTokenFromRequest(User $user, array $requestData): array
    {
        $validatedData = $this->validateTokenData($requestData);

        if (!$this->canCreateToken($user)) {
            throw new \App\Exceptions\RateLimitException(__('api.tokens.rate_limit_exceeded'));
        }

        // Fall back to configured default abilities if not provided
        $defaultAbilities = (array) config('flashcard.api.token.default_abilities', ['*']);

        return $this->createToken(
            $user,
            $validatedData['name'],
            $validatedData['abilities'] ?? $defaultAbilities,
            $validatedData['expires_at'] ?? null
        );
    }
}
