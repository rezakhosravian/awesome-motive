<?php

namespace App\Http\Controllers\Api\V1\Deck\Flashcard;

use App\Contracts\Api\ApiResponseServiceInterface;
use App\Contracts\Service\AuthenticationServiceInterface;
use App\Contracts\Service\FlashcardServiceInterface;
use App\Http\Controllers\Api\BaseApiController;
use App\Models\Deck;
use App\Models\Flashcard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Delete(
 *     path="/api/v1/decks/{deck_slug}/flashcards/{flashcard_id}",
 *     summary="Delete a flashcard",
 *     description="Delete an existing flashcard from a deck",
 *     operationId="deleteFlashcard",
 *     tags={"Flashcards"},
 *     security={{"BearerAuth":{}}, {"ApiKeyAuth":{}}},
 *     @OA\Parameter(
 *         name="deck_slug",
 *         in="path",
 *         description="Deck slug (URL-friendly identifier)",
 *         required=true,
 *         @OA\Schema(type="string", example="spanish-vocabulary")
 *     ),
 *     @OA\Parameter(
 *         name="flashcard_id",
 *         in="path",
 *         description="Flashcard ID",
 *         required=true,
 *         @OA\Schema(type="integer", example=123)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Flashcard deleted successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="deleted"),
 *             @OA\Property(property="message", type="string", example="Flashcard deleted successfully"),
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
        private readonly FlashcardServiceInterface $flashcardService
    ) {
        parent::__construct($responseService, $authService);
    }

    public function __invoke(Request $request, Deck $deck, Flashcard $flashcard): JsonResponse
    {
        return $this->executeAuthenticatedOperation($request, function ($user, $request) use ($deck, $flashcard) {
            $this->flashcardService->deleteFlashcard($user, $deck, $flashcard);

            return $this->responseService->deleted(__('api.flashcards.deleted_success'));
        });
    }
}
