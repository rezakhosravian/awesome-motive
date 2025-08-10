<?php

namespace App\Http\Controllers\ApiToken;

use App\Contracts\Api\ApiResponseServiceInterface;
use App\Contracts\Service\ApiTokenServiceInterface;
use App\Enums\ApiStatusCode;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiTokenResource;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly ApiTokenServiceInterface $tokenService,
        private readonly ApiResponseServiceInterface $responseService
    ) {}

    /**
     * Display a listing of the user's API tokens.
     */
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $tokens = $this->tokenService->getUserTokens($user);
            $stats = $this->tokenService->getTokenStats($user);

            $tokenData = ApiTokenResource::collection($tokens);

            return $this->responseService::make()
                ->data($tokenData)
                ->meta($stats)
                ->message('API tokens retrieved successfully')
                ->build();

        } catch (\Exception $e) {
            return $this->responseService->error(
                'Failed to retrieve API tokens: ' . (config('app.debug') ? $e->getMessage() : 'An error occurred'),
                ApiStatusCode::SERVER_ERROR->httpCode()
            );
        }
    }
}