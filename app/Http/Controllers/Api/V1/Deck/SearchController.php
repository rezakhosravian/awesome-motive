<?php

namespace App\Http\Controllers\Api\V1\Deck;

use App\Contracts\Api\ApiResponseServiceInterface;
use App\Contracts\Service\AuthenticationServiceInterface;
use App\Contracts\Service\DeckServiceInterface;
use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\SearchDecksRequest;
use App\Http\Resources\Api\DeckResource;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Get(
 *     path="/api/v1/search/decks",
 *     summary="Search decks",
 *     description="Search for public decks by name or description",
 *     operationId="searchDecks",
 *     tags={"Decks"},
 *     security={{"BearerAuth":{}}, {"ApiKeyAuth":{}}},
 *     @OA\Parameter(
 *         name="q",
 *         in="query",
 *         description="Search query string",
 *         required=true,
 *         @OA\Schema(type="string", example="spanish")
 *     ),
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
 *         description="Search results",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="success"),
 *             @OA\Property(property="message", type="string", example="Search completed successfully"),
 *             @OA\Property(property="timestamp", type="string", format="date-time"),
 *             @OA\Property(
 *                 property="data",
 *                 type="array",
 *                 @OA\Items(ref="#/components/schemas/DeckResource")
 *             ),
 *             @OA\Property(
 *                 property="meta",
 *                 type="object",
 *                 @OA\Property(property="query", type="string", example="spanish")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error - search query required",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="validation_error"),
 *             @OA\Property(property="message", type="string", example="Validation failed"),
 *             @OA\Property(property="timestamp", type="string", format="date-time")
 *         )
 *     )
 * )
 */
class SearchController extends BaseApiController
{
    public function __construct(
        ApiResponseServiceInterface $responseService,
        AuthenticationServiceInterface $authService,
        private readonly DeckServiceInterface $deckService
    ) {
        parent::__construct($responseService, $authService);
    }

    public function __invoke(SearchDecksRequest $request): JsonResponse
    {
        return $this->executeAuthenticatedOperation($request, function ($user, $request) {
            $query = $request->getQuery();
            $perPage = $request->getPerPage();
            $decks = $this->deckService->searchDecks($query, true, $perPage);

            return $this->responseService->paginated(
                $decks->through(fn($deck) => new DeckResource($deck)),
                __('api.decks.search_success'),
                ['query' => $query]
            );
        });
    }
}
