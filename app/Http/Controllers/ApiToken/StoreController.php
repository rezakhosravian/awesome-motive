<?php

namespace App\Http\Controllers\ApiToken;

use App\Contracts\Api\ApiResponseServiceInterface;
use App\Contracts\Service\ApiTokenServiceInterface;
use App\Enums\ApiStatusCode;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiTokenWithPlaintextResource;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class StoreController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly ApiTokenServiceInterface $tokenService,
        private readonly ApiResponseServiceInterface $responseService
    ) {}

    /**
     * Store a newly created API token.
     */
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'abilities' => 'array',
                'abilities.*' => 'string|in:read,write,delete,admin,*',
                'expires_at' => 'nullable|date|after:now',
            ]);

            $result = $this->tokenService->createToken(
                $user,
                $validated['name'],
                $validated['abilities'] ?? ['*'],
                $validated['expires_at'] ?? null
            );

            $tokenResource = new ApiTokenWithPlaintextResource(
                $result['token'],
                $result['plainToken']
            );

            return $this->responseService->created($tokenResource, 'API token created successfully');

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (InvalidArgumentException $e) {
            return $this->responseService->badRequest($e->getMessage());
        } catch (\Exception $e) {
            return $this->responseService->error(
                'Failed to create API token: ' . (config('app.debug') ? $e->getMessage() : 'An error occurred'),
                ApiStatusCode::SERVER_ERROR->httpCode()
            );
        }
    }
}