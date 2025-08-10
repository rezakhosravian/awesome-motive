<?php

namespace App\Http\Controllers\Flashcard;

use App\Http\Controllers\Controller;
use App\Models\Deck;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class CreateController extends Controller
{
    use AuthorizesRequests;

    public function __invoke(Request $request, Deck $deck): View
    {
        $this->authorize('update', $deck);

        return view('flashcards.create', compact('deck'));
    }
} 