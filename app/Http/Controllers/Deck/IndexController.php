<?php

namespace App\Http\Controllers\Deck;

use App\Http\Controllers\Controller;
use App\Contracts\Service\DeckServiceInterface;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IndexController extends Controller
{
    protected DeckServiceInterface $deckService;

    public function __construct(DeckServiceInterface $deckService)
    {
        $this->deckService = $deckService;
    }

    /**
     * Display a listing of the user's decks.
     */
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $decks = $this->deckService->getUserDecks($user);

        return view('decks.index', compact('decks'));
    }
} 