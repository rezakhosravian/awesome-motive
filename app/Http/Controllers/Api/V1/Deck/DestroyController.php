<?php

namespace App\Http\Controllers\Api\V1\Deck;

use App\Contracts\Api\ApiResponseServiceInterface;
use App\Contracts\Service\AuthenticationServiceInterface;
use App\Contracts\Service\DeckServiceInterface;
use App\Http\Controllers\Api\BaseApiController;
use App\Models\Deck;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Delete(
 *     path="/api/v1/decks/{slug}",
 *     summary="Delete a deck",
 *     description="Delete an existing deck for the authenticated user",
 *     operationId="deleteDeck",
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
 *         description="Deck deleted successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="deleted"),
 *             @OA\Property(property="message", type="string", example="Deck deleted successfully"),
 *             @OA\Property(property="timestamp", type="string", format="date-time")
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
class DestroyController extends BaseApiController
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
            $this->deckService->deleteDeck($user, $deck);

            return $this->responseService->deleted(__('api.decks.deleted_success'));
        });
    }
}
