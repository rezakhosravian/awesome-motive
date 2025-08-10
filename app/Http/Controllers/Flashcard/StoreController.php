<?php

namespace App\Http\Controllers\Flashcard;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFlashcardRequest;
use App\Contracts\Service\FlashcardServiceInterface;
use App\Models\Deck;
use Illuminate\Http\RedirectResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class StoreController extends Controller
{
    public function __construct(
        private readonly FlashcardServiceInterface $flashcardService
    ) {}

    /**
     * Store a newly created flashcard in storage.
     */
    public function __invoke(StoreFlashcardRequest $request, Deck $deck): RedirectResponse
    {
        try {
            $this->flashcardService->createFlashcardFromArray(
                $request->user(),
                $deck,
                $request->validated()
            );

            return redirect()->route('decks.show', $deck)
                ->with('success', __('messages.flashcard.created'));
                
        } catch (ModelNotFoundException $e) {
            return redirect()->route('decks.show', $deck)
                ->with('error', $e->getMessage());
        }
    }
} 