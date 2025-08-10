<?php

namespace App\Http\Controllers\Api\V1\Deck;

use App\Contracts\Api\ApiResponseServiceInterface;
use App\Contracts\Service\AuthenticationServiceInterface;
use App\Contracts\Service\DeckServiceInterface;
use App\Http\Controllers\Api\BaseApiController;
use App\Http\Resources\Api\DeckResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Get(
 *     path="/api/v1/decks",
 *     summary="Get all public decks",
 *     description="Retrieve a paginated list of all public decks",
 *     operationId="getPublicDecks",
 *     tags={"Decks"},
 *     security={{"BearerAuth":{}}, {"ApiKeyAuth":{}}},
 *     @OA\Parameter(
 *         name="page",
 *         in="query",
 *         description="Page number for pagination",
 *         required=false,
 *         @OA\Schema(type="integer", default=1)
 *     ),
 *     @OA\Parameter(
 *         name="per_page",
 *         in="query",
 *         description="Number of items per page",
 *         required=false,
 *         @OA\Schema(type="integer", default=15)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="success"),
 *             @OA\Property(property="message", type="string", example="Public decks retrieved successfully"),
 *             @OA\Property(property="timestamp", type="string", format="date-time"),
 *             @OA\Property(
 *                 property="data",
 *                 type="array",
 *                 @OA\Items(ref="#/components/schemas/DeckResource")
 *             ),
 *             @OA\Property(
 *                 property="pagination",
 *                 type="object",
 *                 @OA\Property(property="current_page", type="integer"),
 *                 @OA\Property(property="last_page", type="integer"),
 *                 @OA\Property(property="per_page", type="integer"),
 *                 @OA\Property(property="total", type="integer"),
 *                 @OA\Property(property="from", type="integer"),
 *                 @OA\Property(property="to", type="integer"),
 *                 @OA\Property(property="has_more_pages", type="boolean")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="unauthorized"),
 *             @OA\Property(property="message", type="string", example="Unauthorized"),
 *             @OA\Property(property="timestamp", type="string", format="date-time", example="2025-01-10T12:00:00.000Z")
 *         )
 *     )
 * )
 */
class IndexController extends BaseApiController
{
    public function __construct(
        ApiResponseServiceInterface $responseService,
        AuthenticationServiceInterface $authService,
        private readonly DeckServiceInterface $deckService
    ) {
        parent::__construct($responseService, $authService);
    }

    public function __invoke(Request $request): JsonResponse
    {
        return $this->executeAuthenticatedOperation($request, function ($user, $request) {
            $params = $this->getPaginationParams($request);
            $decks = $this->deckService->getPublicDecks($params['per_page']);

            return $this->responseService->paginated(
                $decks->through(fn($deck) => new DeckResource($deck)),
                __('api.decks.index_success')
            );
        });
    }
}
