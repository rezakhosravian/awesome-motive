<?php

namespace App\Http\Controllers\Deck;

use App\Http\Controllers\Controller;
use App\Contracts\Service\DeckServiceInterface;
use App\Models\Deck;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class StudyController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly DeckServiceInterface $deckService
    ) {}

    /**
     * Study the specified deck.
     */
    public function __invoke(Request $request, Deck $deck): View|RedirectResponse
    {
        $this->authorize('view', $deck);

        try {
            $deck = $this->deckService->getDeckForStudy($deck->id, $request->user());
            
            return view('decks.study', compact('deck'));
        } catch (\InvalidArgumentException $e) {
            return redirect()->route('decks.show', $deck)
                ->with('error', $e->getMessage());
        } catch (ModelNotFoundException $e) {
            return redirect()->route('decks.index')
                ->with('error', $e->getMessage());
        }
    }
} 