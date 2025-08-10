<?php

namespace App\Http\Controllers\Deck;

use App\DTOs\CreateDeckDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDeckRequest;
use App\Contracts\Service\DeckServiceInterface;
use Illuminate\Http\RedirectResponse;

class StoreController extends Controller
{
    public function __construct(
        private readonly DeckServiceInterface $deckService
    ) {}

    /**
     * Store a newly created deck in storage.
     */
    public function __invoke(StoreDeckRequest $request): RedirectResponse
    {
        $deck = $this->deckService->createDeck($request->user(), CreateDeckDTO::fromRequest($request));

        return redirect()->route('decks.show', $deck)
            ->with('success', __('messages.deck.created'));
    }
} 