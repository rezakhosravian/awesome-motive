<?php

namespace App\Services;

use App\Contracts\Service\AuthenticationServiceInterface;
use App\Models\ApiToken;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Authentication Service
 * 
 * Centralizes authentication logic and removes direct coupling
 * to middleware static methods. This service follows the Dependency
 * Inversion Principle by providing an abstraction layer.
 */
class AuthenticationService implements AuthenticationServiceInterface
{
    public function getCurrentUser(Request $request): ?User
    {
        return $request->attributes->get('api_user');
    }

    public function getCurrentToken(Request $request): ?ApiToken
    {
        return $request->attributes->get('api_token');
    }

    public function isAuthenticated(Request $request): bool
    {
        return $this->getCurrentUser($request) !== null;
    }

    public function getAuthContext(Request $request): array
    {
        $user = $this->getCurrentUser($request);
        $token = $this->getCurrentToken($request);

        return [
            'user' => $user,
            'token' => $token,
            'is_authenticated' => $user !== null,
            'user_id' => $user?->id,
            'token_id' => $token?->id,
            'token_name' => $token?->name
        ];
    }

    public function setAuthContext(Request $request, User $user, ApiToken $token): void
    {
        $request->attributes->set('api_user', $user);
        $request->attributes->set('api_token', $token);
        
        // Set user resolver for Laravel's auth system
        $request->setUserResolver(fn() => $user);
    }
}
