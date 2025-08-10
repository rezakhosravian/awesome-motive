<?php

namespace App\Policies;

use App\Models\Flashcard;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class FlashcardPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Users can view flashcards they have access to
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Flashcard $flashcard): bool
    {
        // User can view flashcard if they own the deck or deck is public
        $deck = $flashcard->deck;
        return $deck->user_id === $user->id || $deck->is_public;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true; // Authenticated users can create flashcards
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Flashcard $flashcard): bool
    {
        // User can update flashcard only if they own the deck
        return $flashcard->deck->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Flashcard $flashcard): bool
    {
        // User can delete flashcard only if they own the deck
        return $flashcard->deck->user_id === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Flashcard $flashcard): bool
    {
        // User can restore flashcard only if they own the deck
        return $flashcard->deck->user_id === $user->id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Flashcard $flashcard): bool
    {
        // User can force delete flashcard only if they own the deck
        return $flashcard->deck->user_id === $user->id;
    }
}
