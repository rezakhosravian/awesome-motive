<?php

namespace App\Http\Controllers\Flashcard;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateFlashcardRequest;
use App\Contracts\Service\FlashcardServiceInterface;
use App\Models\Deck;
use App\Models\Flashcard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UpdateController extends Controller
{
    public function __construct(
        private readonly FlashcardServiceInterface $flashcardService
    ) {}

    /**
     * Update the specified flashcard in storage.
     */
    public function __invoke(UpdateFlashcardRequest $request, Deck $deck, Flashcard $flashcard): RedirectResponse
    {
        try {
            $this->flashcardService->updateFlashcard(
                $request->user(),
                $deck,
                $flashcard,
                $request->validated()
            );

            return redirect()->route('decks.show', $deck)
                ->with('success', __('messages.flashcard.updated'));
                
        } catch (ModelNotFoundException $e) {
            return redirect()->route('decks.show', $deck)
                ->with('error', $e->getMessage());
        }
    }
} 