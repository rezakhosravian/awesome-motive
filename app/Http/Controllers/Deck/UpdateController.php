<?php

namespace App\Http\Controllers\Deck;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateDeckRequest;
use App\Contracts\Service\DeckServiceInterface;
use App\Models\Deck;
use Illuminate\Http\RedirectResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UpdateController extends Controller
{
    public function __construct(
        private readonly DeckServiceInterface $deckService
    ) {}

    /**
     * Update the specified deck in storage.
     */
    public function __invoke(UpdateDeckRequest $request, Deck $deck): RedirectResponse
    {
        try {
            $this->deckService->updateDeckFromArray($request->user(), $deck, $request->validated());

            return redirect()->route('decks.show', $deck)
                ->with('success', __('messages.deck.updated'));
                
        } catch (ModelNotFoundException $e) {
            return redirect()->route('decks.index')
                ->with('error', $e->getMessage());
        }
    }
} 