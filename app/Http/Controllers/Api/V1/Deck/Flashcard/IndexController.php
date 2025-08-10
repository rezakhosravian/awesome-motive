<?php

namespace App\Http\Controllers\Api\V1\Deck\Flashcard;

use App\Contracts\Api\ApiResponseServiceInterface;
use App\Contracts\Service\AuthenticationServiceInterface;
use App\Contracts\Service\FlashcardServiceInterface;
use App\Http\Controllers\Api\BaseApiController;
use App\Http\Resources\Api\FlashcardResource;
use App\Models\Deck;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Get(
 *     path="/api/v1/decks/{slug}/flashcards",
 *     summary="Get flashcards for a deck",
 *     description="Retrieve a paginated list of flashcards for a specific deck",
 *     operationId="getDeckFlashcards",
 *     tags={"Flashcards"},
 *     security={{"BearerAuth":{}}, {"ApiKeyAuth":{}}},
 *     @OA\Parameter(
 *         name="slug",
 *         in="path",
 *         description="Deck slug (URL-friendly identifier)",
 *         required=true,
 *         @OA\Schema(type="string", example="spanish-vocabulary")
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
 *         description="Successful operation",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="success"),
 *             @OA\Property(property="message", type="string", example="Flashcards retrieved successfully"),
 *             @OA\Property(property="timestamp", type="string", format="date-time", example="2025-01-10T12:00:00.000Z"),
 *             @OA\Property(
 *                 property="data",
 *                 type="array",
 *                 @OA\Items(ref="#/components/schemas/FlashcardResource")
 *             ),
 *             @OA\Property(
 *                 property="pagination",
 *                 type="object",
 *                 @OA\Property(property="current_page", type="integer", example=1),
 *                 @OA\Property(property="last_page", type="integer", example=5),
 *                 @OA\Property(property="per_page", type="integer", example=15),
 *                 @OA\Property(property="total", type="integer", example=75),
 *                 @OA\Property(property="from", type="integer", example=1),
 *                 @OA\Property(property="to", type="integer", example=15),
 *                 @OA\Property(property="has_more_pages", type="boolean", example=true)
 *             ),
 *             @OA\Property(
 *                 property="meta",
 *                 type="object",
 *                 @OA\Property(property="deck", type="object")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Deck not found or not accessible",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="not_found"),
 *             @OA\Property(property="message", type="string", example="Deck not found or not accessible"),
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
        private readonly FlashcardServiceInterface $flashcardService
    ) {
        parent::__construct($responseService, $authService);
    }

    public function __invoke(Request $request, Deck $deck): JsonResponse
    {
        return $this->executeAuthenticatedOperation($request, function ($user, $request) use ($deck) {
            $params = $this->getPaginationParams($request);
            
            $flashcards = $this->flashcardService->getFlashcardsForUserWithAccess($deck, $user, $params['per_page']);

            return $this->responseService->paginated(
                $flashcards->through(fn($flashcard) => new FlashcardResource($flashcard)),
                __('api.flashcards.index_success'),
                [
                    'deck' => [
                        'id' => $deck->id,
                        'name' => $deck->name,
                        'slug' => $deck->slug
                    ]
                ]
            );
        });
    }
}
