<?php

namespace App\Http\Controllers\Flashcard;

use App\Http\Controllers\Controller;
use App\Models\Deck;
use App\Models\Flashcard;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ShowController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display the specified flashcard.
     */
    public function __invoke(Request $request, Deck $deck, Flashcard $flashcard): View
    {
        $this->authorize('view', $deck);

        return view('flashcards.show', compact('deck', 'flashcard'));
    }
} 