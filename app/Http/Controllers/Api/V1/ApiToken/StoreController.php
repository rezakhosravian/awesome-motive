<?php

namespace App\Http\Controllers\Api\V1\ApiToken;

use App\Contracts\Api\ApiResponseServiceInterface;
use App\Contracts\Service\ApiTokenServiceInterface;
use App\Contracts\Service\AuthenticationServiceInterface;
use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\StoreApiTokenRequest;
use App\Http\Resources\ApiTokenWithPlaintextResource;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Post(
 *     path="/api/v1/auth/tokens",
 *     summary="Create a new API token",
 *     description="Create a new API token for the authenticated user",
 *     operationId="createApiToken",
 *     tags={"API Tokens"},
 *     security={{"BearerAuth":{}}, {"ApiKeyAuth":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"name"},
 *             @OA\Property(property="name", type="string", example="Mobile App Token"),
 *             @OA\Property(
 *                 property="abilities",
 *                 type="array",
 *                 @OA\Items(type="string"),
 *                 example={"read", "write"},
 *                 description="Token abilities (permissions)"
 *             ),
 *             @OA\Property(
 *                 property="expires_at",
 *                 type="string",
 *                 format="date",
 *                 example="2024-12-31",
 *                 description="Token expiration date (optional)"
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="API token created successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="created"),
 *             @OA\Property(property="message", type="string", example="API token created successfully"),
 *             @OA\Property(property="timestamp", type="string", format="date-time"),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="name", type="string", example="Mobile App Token"),
 *                 @OA\Property(property="abilities", type="array", @OA\Items(type="string", example="read")),
 *                 @OA\Property(property="plaintext_token", type="string", example="1|xyz...abc"),
 *                 @OA\Property(property="created_at", type="string", format="date-time"),
 *                 @OA\Property(property="expires_at", type="string", format="date-time", nullable=true)
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="validation_error"),
 *             @OA\Property(property="message", type="string", example="Validation failed"),
 *             @OA\Property(property="timestamp", type="string", format="date-time"),
 *             @OA\Property(property="errors", type="object")
 *         )
 *     ),
 *     @OA\Response(
 *         response=429,
 *         description="Rate limit exceeded",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="too_many_requests"),
 *             @OA\Property(property="message", type="string", example="You have reached the maximum number of API tokens allowed"),
 *             @OA\Property(property="timestamp", type="string", format="date-time")
 *         )
 *     )
 * )
 */
class StoreController extends BaseApiController
{
    public function __construct(
        ApiResponseServiceInterface $responseService,
        AuthenticationServiceInterface $authService,
        private readonly ApiTokenServiceInterface $apiTokenService
    ) {
        parent::__construct($responseService, $authService);
    }

    public function __invoke(StoreApiTokenRequest $request): JsonResponse
    {
        return $this->executeAuthenticatedOperation($request, function ($user, $request) {
            $result = $this->apiTokenService->createTokenFromRequest($user, $request->validated());

            return $this->responseService->created(
                new ApiTokenWithPlaintextResource($result['token'], $result['plainToken']),
                __('api.tokens.created_success')
            );
        });
    }
}
