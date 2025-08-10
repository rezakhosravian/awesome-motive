<?php

namespace App\Http\Controllers\Api\V1\ApiToken;

use App\Contracts\Api\ApiResponseServiceInterface;
use App\Contracts\Service\ApiTokenServiceInterface;
use App\Contracts\Service\AuthenticationServiceInterface;
use App\Http\Controllers\Api\BaseApiController;
use App\Models\ApiToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Delete(
 *     path="/api/v1/auth/tokens/{token_id}",
 *     summary="Delete an API token",
 *     description="Delete an existing API token for the authenticated user",
 *     operationId="deleteApiToken",
 *     tags={"API Tokens"},
 *     security={{"BearerAuth":{}}, {"ApiKeyAuth":{}}},
 *     @OA\Parameter(
 *         name="token_id",
 *         in="path",
 *         description="API Token ID",
 *         required=true,
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="API token deleted successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="deleted"),
 *             @OA\Property(property="message", type="string", example="API token deleted successfully"),
 *             @OA\Property(property="timestamp", type="string", format="date-time")
 *         )
 *     ),
 *     @OA\Response(
 *         response=403,
 *         description="Forbidden - Token doesn't belong to user",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="forbidden"),
 *             @OA\Property(property="message", type="string", example="You do not have permission to perform this action"),
 *             @OA\Property(property="timestamp", type="string", format="date-time")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="API token not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="not_found"),
 *             @OA\Property(property="message", type="string", example="API token not found"),
 *             @OA\Property(property="timestamp", type="string", format="date-time")
 *         )
 *     )
 * )
 */
class DestroyController extends BaseApiController
{
    public function __construct(
        ApiResponseServiceInterface $responseService,
        AuthenticationServiceInterface $authService,
        private readonly ApiTokenServiceInterface $apiTokenService
    ) {
        parent::__construct($responseService, $authService);
    }

    public function __invoke(Request $request, ApiToken $token): JsonResponse
    {
        return $this->executeAuthenticatedOperation($request, function ($user, $request) use ($token) {
            $this->apiTokenService->deleteToken($user, $token);

            return $this->responseService->deleted(__('api.tokens.deleted_success'));
        });
    }
}
