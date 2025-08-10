<?php

namespace App\Http\Controllers\Deck;

use App\Http\Controllers\Controller;
use App\Contracts\Service\DeckServiceInterface;
use App\Models\Deck;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class DestroyController extends Controller
{
    public function __construct(
        private readonly DeckServiceInterface $deckService
    ) {}

    /**
     * Remove the specified deck from storage.
     */
    public function __invoke(Request $request, Deck $deck): RedirectResponse
    {
        try {
            $this->deckService->deleteDeck($request->user(), $deck);

            return redirect()->route('decks.index')
                ->with('success', __('messages.deck.deleted'));
                
        } catch (ModelNotFoundException $e) {
            return redirect()->route('decks.index')
                ->with('error', $e->getMessage());
        }
    }
} 