<?php

namespace App\Http\Controllers\Api\V1\Deck\Flashcard;

use App\Contracts\Api\ApiResponseServiceInterface;
use App\Contracts\Service\AuthenticationServiceInterface;
use App\Contracts\Service\FlashcardServiceInterface;
use App\DTOs\UpdateFlashcardDTO;
use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\UpdateFlashcardRequest;
use App\Http\Resources\Api\FlashcardResource;
use App\Models\Deck;
use App\Models\Flashcard;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Put(
 *     path="/api/v1/decks/{deck_slug}/flashcards/{flashcard_id}",
 *     summary="Update a flashcard",
 *     description="Update an existing flashcard in a deck",
 *     operationId="updateFlashcard",
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
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="question", type="string", example="What is 'goodbye' in Spanish?"),
 *             @OA\Property(property="answer", type="string", example="Adiós"),
 *             @OA\Property(property="hint", type="string", example="A farewell greeting")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Flashcard updated successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="updated"),
 *             @OA\Property(property="message", type="string", example="Flashcard updated successfully"),
 *             @OA\Property(property="timestamp", type="string", format="date-time"),
 *             @OA\Property(
 *                 property="data",
 *                 ref="#/components/schemas/FlashcardResource"
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
        private readonly FlashcardServiceInterface $flashcardService
    ) {
        parent::__construct($responseService, $authService);
    }

    public function __invoke(UpdateFlashcardRequest $request, Deck $deck, Flashcard $flashcard): JsonResponse
    {
        return $this->executeAuthenticatedOperation($request, function ($user, $request) use ($deck, $flashcard) {
            $dto = UpdateFlashcardDTO::fromArray($request->validated());
            $updatedFlashcard = $this->flashcardService->updateFlashcard($user, $deck, $flashcard, $dto);

            return $this->responseService->updated(
                new FlashcardResource($updatedFlashcard),
                __('api.flashcards.updated_success')
            );
        });
    }
}
