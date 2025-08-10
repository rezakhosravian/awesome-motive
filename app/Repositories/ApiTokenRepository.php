<?php

namespace App\Repositories;

use App\Contracts\Repository\ApiTokenRepositoryInterface;
use App\Models\ApiToken;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class ApiTokenRepository extends BaseRepository implements ApiTokenRepositoryInterface
{
    public function __construct(ApiToken $model)
    {
        parent::__construct($model);
    }

    /**
     * Get all API tokens for a user, ordered by creation date (newest first).
     *
     * @param User $user
     * @return Collection<ApiToken>
     */
    public function getByUser(User $user): Collection
    {
        return $this->model->where('user_id', $user->id)
            ->latest()
            ->get();
    }

    /**
     * Create a new API token for a user.
     *
     * @param User $user
     * @param array $data
     * @return ApiToken
     */
    public function createForUser(User $user, array $data): ApiToken
    {
        $data['user_id'] = $user->id;
        return $this->create($data);
    }

    /**
     * Find a token by its hashed value.
     *
     * @param string $hashedToken
     * @return ApiToken|null
     */
    public function findByHashedToken(string $hashedToken): ?ApiToken
    {
        return $this->model->where('token', $hashedToken)->first();
    }

    /**
     * Update the last_used_at timestamp for a token.
     *
     * @param ApiToken $token
     * @return bool
     */
    public function updateLastUsed(ApiToken $token): bool
    {
        return $token->update(['last_used_at' => now()]);
    }

    /**
     * Delete tokens that have expired.
     *
     * @return int Number of deleted tokens
     */
    public function deleteExpired(): int
    {
        return $this->model->where('expires_at', '<', now())->delete();
    }

    /**
     * Count active (non-expired) tokens for a user.
     *
     * @param User $user
     * @return int
     */
    public function countActiveForUser(User $user): int
    {
        return $this->model->where('user_id', $user->id)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->count();
    }

    /**
     * Get active tokens for a user (non-expired).
     *
     * @param User $user
     * @return Collection<ApiToken>
     */
    public function getActiveByUser(User $user): Collection
    {
        return $this->model->where('user_id', $user->id)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->latest()
            ->get();
    }
}