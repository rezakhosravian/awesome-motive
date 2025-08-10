<?php

use App\Http\Controllers\Dashboard\ShowController as DashboardController;
use App\Http\Controllers\Profile\ShowController as ProfileController;
use App\Http\Controllers\Deck\IndexController as DeckIndexController;
use App\Http\Controllers\Deck\CreateController as DeckCreateController;
use App\Http\Controllers\Deck\StoreController as DeckStoreController;
use App\Http\Controllers\Deck\ShowController as DeckShowController;
use App\Http\Controllers\Deck\EditController as DeckEditController;
use App\Http\Controllers\Deck\UpdateController as DeckUpdateController;
use App\Http\Controllers\Deck\DestroyController as DeckDestroyController;
use App\Http\Controllers\Deck\StudyController as DeckStudyController;
use App\Http\Controllers\Flashcard\CreateController as FlashcardCreateController;
use App\Http\Controllers\Flashcard\StoreController as FlashcardStoreController;
use App\Http\Controllers\Flashcard\ShowController as FlashcardShowController;
use App\Http\Controllers\Flashcard\EditController as FlashcardEditController;
use App\Http\Controllers\Flashcard\UpdateController as FlashcardUpdateController;
use App\Http\Controllers\Flashcard\DestroyController as FlashcardDestroyController;
use App\Http\Controllers\ApiToken\IndexController as ApiTokenIndexController;
use App\Http\Controllers\ApiToken\StoreController as ApiTokenStoreController;
use App\Http\Controllers\ApiToken\DestroyController as ApiTokenDestroyController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::get('dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('profile', ProfileController::class)
    ->middleware(['auth'])
    ->name('profile');

// Authenticated routes for deck and flashcard management
Route::middleware(['auth'])->group(function () {
    // Deck routes
    Route::get('decks', DeckIndexController::class)->name('decks.index');
    Route::get('decks/create', DeckCreateController::class)->name('decks.create');
    Route::post('decks', DeckStoreController::class)->name('decks.store');
    Route::get('decks/{deck}', DeckShowController::class)->name('decks.show');
    Route::get('decks/{deck}/edit', DeckEditController::class)->name('decks.edit');
    Route::put('decks/{deck}', DeckUpdateController::class)->name('decks.update');
    Route::delete('decks/{deck}', DeckDestroyController::class)->name('decks.destroy');
    Route::get('decks/{deck}/study', DeckStudyController::class)->name('decks.study');

    // Nested flashcard routes within decks
    Route::get('decks/{deck}/flashcards/create', FlashcardCreateController::class)->name('decks.flashcards.create');
    Route::post('decks/{deck}/flashcards', FlashcardStoreController::class)->name('decks.flashcards.store');

    Route::middleware(['validate.flashcard.deck'])->group(function () {
        Route::get('decks/{deck}/flashcards/{flashcard}', FlashcardShowController::class)->name('decks.flashcards.show');
        Route::get('decks/{deck}/flashcards/{flashcard}/edit', FlashcardEditController::class)->name('decks.flashcards.edit');
        Route::put('decks/{deck}/flashcards/{flashcard}', FlashcardUpdateController::class)->name('decks.flashcards.update');
        Route::delete('decks/{deck}/flashcards/{flashcard}', FlashcardDestroyController::class)->name('decks.flashcards.destroy');
    });

    // API Token Management
    Route::get('api-tokens', ApiTokenIndexController::class)->name('api-tokens.index');
    Route::post('api-tokens', ApiTokenStoreController::class)->name('api-tokens.store');
    Route::delete('api-tokens/{token}', ApiTokenDestroyController::class)->name('api-tokens.destroy');

    // API Token Management Page
    Route::get('manage-api-tokens', function () {
        return view('api-tokens');
    })->name('api-tokens.manage');
});


require __DIR__.'/auth.php';
