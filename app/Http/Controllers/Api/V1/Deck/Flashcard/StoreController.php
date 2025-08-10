<?php

namespace App\Http\Controllers\Api\V1\Deck\Flashcard;

use App\Contracts\Api\ApiResponseServiceInterface;
use App\Contracts\Service\AuthenticationServiceInterface;
use App\Contracts\Service\FlashcardServiceInterface;
use App\DTOs\CreateFlashcardDTO;
use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\StoreFlashcardRequest;
use App\Http\Resources\Api\FlashcardResource;
use App\Models\Deck;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Post(
 *     path="/api/v1/decks/{slug}/flashcards",
 *     summary="Create a new flashcard",
 *     description="Create a new flashcard for a specific deck",
 *     operationId="createFlashcard",
 *     tags={"Flashcards"},
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
 *             required={"question", "answer"},
 *             @OA\Property(property="question", type="string", example="What is 'hello' in Spanish?"),
 *             @OA\Property(property="answer", type="string", example="Hola"),
 *             @OA\Property(property="hint", type="string", example="A common greeting")
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Flashcard created successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="created"),
 *             @OA\Property(property="message", type="string", example="Flashcard created successfully"),
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
class StoreController extends BaseApiController
{
    public function __construct(
        ApiResponseServiceInterface $responseService,
        AuthenticationServiceInterface $authService,
        private readonly FlashcardServiceInterface $flashcardService
    ) {
        parent::__construct($responseService, $authService);
    }

    public function __invoke(StoreFlashcardRequest $request, Deck $deck): JsonResponse
    {
        return $this->executeAuthenticatedOperation($request, function ($user, $request) use ($deck) {
            // Delegate ALL business logic to service (authorization, DTO creation, validation)
            $dto = CreateFlashcardDTO::fromArray($request->validated(), $deck->id);
            $flashcard = $this->flashcardService->createFlashcard($user, $deck, $dto);

            return $this->responseService->created(
                new FlashcardResource($flashcard),
                __('api.flashcards.created_success')
            );
        });
    }
}
