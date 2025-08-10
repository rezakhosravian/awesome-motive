<?php

namespace App\Http\Controllers\ApiToken;

use App\Contracts\Api\ApiResponseServiceInterface;
use App\Contracts\Service\ApiTokenServiceInterface;
use App\Enums\ApiStatusCode;
use App\Http\Controllers\Controller;
use App\Models\ApiToken;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class DestroyController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly ApiTokenServiceInterface $tokenService,
        private readonly ApiResponseServiceInterface $responseService
    ) {}

    /**
     * Remove the specified API token.
     */
    public function __invoke(Request $request, ApiToken $token): JsonResponse
    {
        try {
            $user = $request->user();
            
            $this->tokenService->deleteToken($user, $token);

            return $this->responseService->deleted('API token deleted successfully');

        } catch (InvalidArgumentException $e) {
            return $this->responseService->forbidden($e->getMessage());
        } catch (\Exception $e) {
            return $this->responseService->error(
                'Failed to delete API token: ' . (config('app.debug') ? $e->getMessage() : 'An error occurred'),
                ApiStatusCode::SERVER_ERROR->httpCode()
            );
        }
    }
}