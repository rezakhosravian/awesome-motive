<?php

namespace App\Http\Controllers\Api\V1\ApiToken;

use App\Contracts\Api\ApiResponseServiceInterface;
use App\Contracts\Service\ApiTokenServiceInterface;
use App\Contracts\Service\AuthenticationServiceInterface;
use App\Http\Controllers\Api\BaseApiController;
use App\Http\Resources\Api\ApiTokenResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Get(
 *     path="/api/v1/auth/tokens",
 *     summary="Get user's API tokens",
 *     description="Retrieve a list of all API tokens for the authenticated user",
 *     operationId="getUserApiTokens",
 *     tags={"API Tokens"},
 *     security={{"BearerAuth":{}}, {"ApiKeyAuth":{}}},
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="success"),
 *             @OA\Property(property="message", type="string", example="API tokens retrieved successfully"),
 *             @OA\Property(property="timestamp", type="string", format="date-time"),
 *             @OA\Property(
 *                 property="data",
 *                 type="array",
 *                 @OA\Items(ref="#/components/schemas/ApiTokenResource")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="unauthorized"),
 *             @OA\Property(property="message", type="string", example="Authentication credentials are required or invalid."),
 *             @OA\Property(property="timestamp", type="string", format="date-time")
 *         )
 *     )
 * )
 */
class IndexController extends BaseApiController
{
    public function __construct(
        ApiResponseServiceInterface $responseService,
        AuthenticationServiceInterface $authService,
        private readonly ApiTokenServiceInterface $apiTokenService
    ) {
        parent::__construct($responseService, $authService);
    }

    public function __invoke(Request $request): JsonResponse
    {
        return $this->executeAuthenticatedOperation($request, function ($user, $request) {
            $tokens = $this->apiTokenService->getUserTokens($user);

            return $this->responseService->success(
                ApiTokenResource::collection($tokens),
                __('api.tokens.index_success')
            );
        });
    }
}
