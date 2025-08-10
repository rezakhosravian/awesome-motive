<?php

namespace App\Http\Controllers\Deck;

use App\Http\Controllers\Controller;
use App\Contracts\Service\DeckServiceInterface;
use App\Models\Deck;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ShowController extends Controller
{
    use AuthorizesRequests;

    protected DeckServiceInterface $deckService;

    public function __construct(DeckServiceInterface $deckService)
    {
        $this->deckService = $deckService;
    }

    /**
     * Display the specified deck.
     */
    public function __invoke(Request $request, Deck $deck): View
    {
        $this->authorize('view', $deck);
        
        $deck->load(['flashcards' => function ($query) {
            $query->latest();
        }]);

        $stats = $this->deckService->getDeckStats($deck);

        return view('decks.show', compact('deck', 'stats'));
    }
} 