<?php

namespace App\Http\Controllers\Flashcard;

use App\Http\Controllers\Controller;
use App\Contracts\Service\FlashcardServiceInterface;
use App\Models\Deck;
use App\Models\Flashcard;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class DestroyController extends Controller
{
    public function __construct(
        private readonly FlashcardServiceInterface $flashcardService
    ) {}

    /**
     * Remove the specified flashcard from storage.
     */
    public function __invoke(Request $request, Deck $deck, Flashcard $flashcard): RedirectResponse
    {
        try {
            $this->flashcardService->deleteFlashcard(
                $request->user(),
                $deck,
                $flashcard
            );

            return redirect()->route('decks.show', $deck)
                ->with('success', __('messages.flashcard.deleted'));
        } catch (ModelNotFoundException $e) {
            return redirect()->route('decks.show', $deck)
                ->with('error', $e->getMessage());
        }
    }
}
