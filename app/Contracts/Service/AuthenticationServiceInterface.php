<?php

namespace App\Contracts\Service;

use App\Models\ApiToken;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Authentication Service Interface
 * 
 * Provides abstraction for authentication operations,
 * following the Dependency Inversion Principle.
 */
interface AuthenticationServiceInterface
{
    /**
     * Get the current authenticated user from request
     */
    public function getCurrentUser(Request $request): ?User;

    /**
     * Get the current API token from request
     */
    public function getCurrentToken(Request $request): ?ApiToken;

    /**
     * Check if request is authenticated
     */
    public function isAuthenticated(Request $request): bool;

    /**
     * Get authentication context (user + token)
     */
    public function getAuthContext(Request $request): array;

    /**
     * Set authentication context in request
     */
    public function setAuthContext(Request $request, User $user, ApiToken $token): void;
}
