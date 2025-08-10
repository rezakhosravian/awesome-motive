<?php

namespace App\Http\Middleware;

use App\Contracts\Api\ApiResponseServiceInterface;
use App\Contracts\Service\ApiTokenServiceInterface;
use App\Contracts\Service\AuthenticationServiceInterface;
use Closure;
use App\Infrastructure\Auth\TokenResolverInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyAuth
{
    private AuthenticationServiceInterface $authService;
    private TokenResolverInterface $tokenResolver;

    public function __construct(
        private readonly ApiResponseServiceInterface $responseService,
        private readonly ApiTokenServiceInterface $tokenService,
        ?TokenResolverInterface $tokenResolver = null,
        ?AuthenticationServiceInterface $authService = null
    ) {
        $this->tokenResolver = $tokenResolver ?? app(TokenResolverInterface::class);
        $this->authService = $authService ?? app(AuthenticationServiceInterface::class);
    }
    public static function getCurrentToken(Request $request): ?\App\Models\ApiToken
    {
        return $request->attributes->get('api_token');
    }

    public static function getCurrentUser(Request $request): ?\App\Models\User
    {
        $token = self::getCurrentToken($request);
        return $token ? $token->user : null;
    }

    public function handle(Request $request, Closure $next): Response
    {
        $token = $this->tokenResolver->resolve($request);

        if (!$token) {
            return $this->responseService->unauthorized();
        }

        $apiToken = $this->tokenService->authenticateToken($token);
        if (!$apiToken) {
            return $this->responseService->unauthorized();
        }

        $this->authService->setAuthContext($request, $apiToken->user, $apiToken);

        return $next($request);
    }

    private function isValidToken(?string $token): bool
    {
        return $this->tokenService->authenticateToken($token) !== null;
    }
}
