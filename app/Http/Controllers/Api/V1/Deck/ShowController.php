<?php

namespace App\Http\Controllers\Api\V1\Deck;

use App\Contracts\Api\ApiResponseServiceInterface;
use App\Contracts\Service\AuthenticationServiceInterface;
use App\Contracts\Service\DeckServiceInterface;
use App\Http\Controllers\Api\BaseApiController;
use App\Http\Resources\Api\DeckResource;
use App\Models\Deck;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Get(
 *     path="/api/v1/decks/{slug}",
 *     summary="Get a specific deck",
 *     description="Retrieve detailed information about a specific deck",
 *     operationId="getDeck",
 *     tags={"Decks"},
 *     security={{"BearerAuth":{}}, {"ApiKeyAuth":{}}},
 *     @OA\Parameter(
 *         name="slug",
 *         in="path",
 *         description="Deck slug (URL-friendly identifier)",
 *         required=true,
 *         @OA\Schema(type="string", example="spanish-vocabulary")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful response with deck details",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="success"),
 *             @OA\Property(property="message", type="string", example="Deck retrieved successfully"),
 *             @OA\Property(property="timestamp", type="string", format="date-time"),
 *             @OA\Property(
 *                 property="data",
 *                 ref="#/components/schemas/DeckResource"
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Deck not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="not_found"),
 *             @OA\Property(property="message", type="string", example="The requested resource was not found"),
 *             @OA\Property(property="timestamp", type="string", format="date-time")
 *         )
 *     )
 * )
 */
class ShowController extends BaseApiController
{
    public function __construct(
        ApiResponseServiceInterface $responseService,
        AuthenticationServiceInterface $authService,
        private readonly DeckServiceInterface $deckService
    ) {
        parent::__construct($responseService, $authService);
    }

    public function __invoke(Request $request, Deck $deck): JsonResponse
    {
        return $this->executeAuthenticatedOperation($request, function ($user, $request) use ($deck) {
            $deckWithRelations = $this->deckService->getDeckForUser($deck, $user);

            return $this->responseService->success(
                new DeckResource($deckWithRelations),
                __('api.decks.show_success')
            );
        });
    }
}
