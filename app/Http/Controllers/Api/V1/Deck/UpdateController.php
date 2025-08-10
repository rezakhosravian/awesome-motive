<?php

namespace App\Http\Controllers\Api\V1\Deck;

use App\Contracts\Api\ApiResponseServiceInterface;
use App\Contracts\Service\AuthenticationServiceInterface;
use App\Contracts\Service\DeckServiceInterface;
use App\DTOs\UpdateDeckDTO;
use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\UpdateDeckRequest;
use App\Http\Resources\Api\DeckResource;
use App\Models\Deck;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Put(
 *     path="/api/v1/decks/{slug}",
 *     summary="Update a deck",
 *     description="Update an existing deck for the authenticated user",
 *     operationId="updateDeck",
 *     tags={"Decks"},
 *     security={{"BearerAuth":{}}, {"ApiKeyAuth":{}}},
 *     @OA\Parameter(
 *         name="slug",
 *         in="path",
 *         description="Deck slug (URL-friendly identifier)",
 *         required=true,
 *         @OA\Schema(type="string", example="spanish-vocabulary")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="name", type="string", example="Advanced Spanish Vocabulary"),
 *             @OA\Property(property="description", type="string", example="Advanced Spanish words for intermediate learners"),
 *             @OA\Property(property="is_public", type="boolean", example=false)
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Deck updated successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="updated"),
 *             @OA\Property(property="message", type="string", example="Deck updated successfully"),
 *             @OA\Property(property="timestamp", type="string", format="date-time"),
 *             @OA\Property(
 *                 property="data",
 *                 ref="#/components/schemas/DeckResource"
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=403,
 *         description="Forbidden - User doesn't own this deck",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="forbidden"),
 *             @OA\Property(property="message", type="string", example="You do not have permission to perform this action"),
 *             @OA\Property(property="timestamp", type="string", format="date-time")
 *         )
 *     )
 * )
 */
class UpdateController extends BaseApiController
{
    public function __construct(
        ApiResponseServiceInterface $responseService,
        AuthenticationServiceInterface $authService,
        private readonly DeckServiceInterface $deckService
    ) {
        parent::__construct($responseService, $authService);
    }

    public function __invoke(UpdateDeckRequest $request, Deck $deck): JsonResponse
    {
        return $this->executeAuthenticatedOperation($request, function ($user, $request) use ($deck) {
            $dto = UpdateDeckDTO::fromArray($request->validated());
            $updatedDeck = $this->deckService->updateDeck($user, $deck, $dto);

            return $this->responseService->updated(
                new DeckResource($updatedDeck),
                __('api.decks.updated_success')
            );
        });
    }
}
