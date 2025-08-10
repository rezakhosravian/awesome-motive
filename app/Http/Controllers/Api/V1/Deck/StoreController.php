<?php

namespace App\Http\Controllers\Api\V1\Deck;

use App\Contracts\Api\ApiResponseServiceInterface;
use App\Contracts\Service\AuthenticationServiceInterface;
use App\Contracts\Service\DeckServiceInterface;
use App\DTOs\CreateDeckDTO;
use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\StoreDeckRequest;
use App\Http\Resources\Api\DeckResource;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Post(
 *     path="/api/v1/decks",
 *     summary="Create a new deck",
 *     description="Create a new flashcard deck for the authenticated user",
 *     operationId="createDeck",
 *     tags={"Decks"},
 *     security={{"BearerAuth":{}}, {"ApiKeyAuth":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"name"},
 *             @OA\Property(property="name", type="string", example="Spanish Vocabulary"),
 *             @OA\Property(property="description", type="string", example="Basic Spanish words for beginners"),
 *             @OA\Property(property="is_public", type="boolean", example=true)
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Deck created successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="created"),
 *             @OA\Property(property="message", type="string", example="Deck created successfully"),
 *             @OA\Property(property="timestamp", type="string", format="date-time"),
 *             @OA\Property(
 *                 property="data",
 *                 ref="#/components/schemas/DeckResource"
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
 *     )
 * )
 */
class StoreController extends BaseApiController
{
    public function __construct(
        ApiResponseServiceInterface $responseService,
        AuthenticationServiceInterface $authService,
        private readonly DeckServiceInterface $deckService
    ) {
        parent::__construct($responseService, $authService);
    }

    public function __invoke(StoreDeckRequest $request): JsonResponse
    {
        return $this->executeAuthenticatedOperation($request, function ($user, $request) {
            $dto = CreateDeckDTO::fromRequest($request);
            $deck = $this->deckService->createDeck($user, $dto);

            return $this->responseService->created(
                new DeckResource($deck),
                __('api.decks.created_success')
            );
        });
    }
}
