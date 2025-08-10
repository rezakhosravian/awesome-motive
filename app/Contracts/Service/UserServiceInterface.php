<?php

namespace App\Contracts\Service;

use App\Models\User;
use Illuminate\Http\Request;

interface UserServiceInterface
{
    /**
     * Register a new user
     */
    public function register(array $data): User;

    /**
     * Authenticate user
     */
    public function authenticate(array $credentials): bool;

    /**
     * Update user profile
     */
    public function updateProfile(User $user, array $data): User;

    /**
     * Change user password
     */
    public function changePassword(User $user, string $currentPassword, string $newPassword): bool;

    /**
     * Delete user account
     */
    public function deleteAccount(User $user, string $password): bool;

    /**
     * Get user statistics
     */
    public function getUserStats(User $user): array;
} 