<?php

namespace App\Http\Controllers\Api\V1\Deck\Flashcard;

use App\Contracts\Api\ApiResponseServiceInterface;
use App\Contracts\Service\AuthenticationServiceInterface;
use App\Contracts\Service\FlashcardServiceInterface;
use App\Http\Controllers\Api\BaseApiController;
use App\Http\Resources\Api\FlashcardResource;
use App\Models\Deck;
use App\Models\Flashcard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Get(
 *     path="/api/v1/decks/{deck_slug}/flashcards/{flashcard_id}",
 *     summary="Get a specific flashcard",
 *     description="Retrieve detailed information about a specific flashcard",
 *     operationId="getFlashcard",
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
 *         description="Successful response with flashcard details",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="success"),
 *             @OA\Property(property="message", type="string", example="Flashcard retrieved successfully"),
 *             @OA\Property(property="timestamp", type="string", format="date-time"),
 *             @OA\Property(
 *                 property="data",
 *                 ref="#/components/schemas/FlashcardResource"
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Flashcard not found or not accessible",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="not_found"),
 *             @OA\Property(property="message", type="string", example="Flashcard not found or not accessible"),
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
        private readonly FlashcardServiceInterface $flashcardService
    ) {
        parent::__construct($responseService, $authService);
    }

    public function __invoke(Request $request, Deck $deck, Flashcard $flashcard): JsonResponse
    {
        return $this->executeAuthenticatedOperation($request, function ($user, $request) use ($deck, $flashcard) {
            $flashcard = $this->flashcardService->getFlashcardForUser($deck, $flashcard, $user);

            return $this->responseService->success(
                new FlashcardResource($flashcard),
                __('api.flashcards.show_success')
            );
        });
    }
}
