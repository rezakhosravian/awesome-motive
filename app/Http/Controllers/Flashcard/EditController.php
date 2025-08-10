<?php

namespace App\Http\Controllers\Flashcard;

use App\Http\Controllers\Controller;
use App\Models\Deck;
use App\Models\Flashcard;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class EditController extends Controller
{
    use AuthorizesRequests;

    /**
     * Show the form for editing the specified flashcard.
     */
    public function __invoke(Request $request, Deck $deck, Flashcard $flashcard): View
    {
        $this->authorize('update', $deck);

        return view('flashcards.edit', compact('deck', 'flashcard'));
    }
} 