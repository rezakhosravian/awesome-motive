<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateFlashcardBelongsToDeck
{
    /**
     * Handle an incoming request.
     *
     * This middleware prevents IDOR (Insecure Direct Object Reference) attacks
     * by ensuring that the flashcard belongs to the specified deck.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $deck = $request->route('deck');
        $flashcard = $request->route('flashcard');

        if (!$deck || !$flashcard) {
            return $next($request);
        }

        if ($flashcard->deck_id !== $deck->id) {
            abort(404);
        }

        return $next($request);
    }
}