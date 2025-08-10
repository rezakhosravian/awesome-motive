<?php

use App\Http\Controllers\Api\V1\Auth\TestController as ApiAuthTestController;
use App\Http\Controllers\Api\V1\Deck\IndexController as ApiDeckIndexController;
use App\Http\Controllers\Api\V1\Deck\StoreController as ApiDeckStoreController;
use App\Http\Controllers\Api\V1\Deck\ShowController as ApiDeckShowController;
use App\Http\Controllers\Api\V1\Deck\UpdateController as ApiDeckUpdateController;
use App\Http\Controllers\Api\V1\Deck\DestroyController as ApiDeckDestroyController;
use App\Http\Controllers\Api\V1\Deck\SearchController as ApiDeckSearchController;
use App\Http\Controllers\Api\V1\Deck\Flashcard\IndexController as ApiFlashcardIndexController;
use App\Http\Controllers\Api\V1\Deck\Flashcard\StoreController as ApiFlashcardStoreController;
use App\Http\Controllers\Api\V1\Deck\Flashcard\ShowController as ApiFlashcardShowController;
use App\Http\Controllers\Api\V1\Deck\Flashcard\UpdateController as ApiFlashcardUpdateController;
use App\Http\Controllers\Api\V1\Deck\Flashcard\DestroyController as ApiFlashcardDestroyController;
use App\Http\Controllers\Api\V1\ApiToken\IndexController as ApiTokenIndexController;
use App\Http\Controllers\Api\V1\ApiToken\StoreController as ApiTokenStoreController;
use App\Http\Controllers\Api\V1\ApiToken\DestroyController as ApiTokenDestroyController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('v1')->middleware(['api.key'])->group(function () {
    // Authentication routes
    Route::get('auth/test', ApiAuthTestController::class)->name('api.auth.test');

    // API Token management routes
    Route::get('auth/tokens', ApiTokenIndexController::class)->name('api.auth.tokens.index');
    Route::post('auth/tokens', ApiTokenStoreController::class)->name('api.auth.tokens.store');
    Route::delete('auth/tokens/{token}', ApiTokenDestroyController::class)->name('api.auth.tokens.destroy');

    // Deck routes 
    Route::get('decks', ApiDeckIndexController::class)->name('api.decks.index');
    Route::post('decks', ApiDeckStoreController::class)->name('api.decks.store');
    Route::get('decks/{deck:slug}', ApiDeckShowController::class)->name('api.decks.show');
    Route::put('decks/{deck:slug}', ApiDeckUpdateController::class)->name('api.decks.update');
    Route::delete('decks/{deck:slug}', ApiDeckDestroyController::class)->name('api.decks.destroy');
    Route::get('search/decks', ApiDeckSearchController::class)->name('api.decks.search');

    // Nested Flashcard routes within decks
    Route::get('decks/{deck:slug}/flashcards', ApiFlashcardIndexController::class)->name('api.decks.flashcards.index');
    Route::post('decks/{deck:slug}/flashcards', ApiFlashcardStoreController::class)->name('api.decks.flashcards.store');
    Route::get('decks/{deck:slug}/flashcards/{flashcard}', ApiFlashcardShowController::class)->name('api.decks.flashcards.show');
    Route::put('decks/{deck:slug}/flashcards/{flashcard}', ApiFlashcardUpdateController::class)->name('api.decks.flashcards.update');
    Route::delete('decks/{deck:slug}/flashcards/{flashcard}', ApiFlashcardDestroyController::class)->name('api.decks.flashcards.destroy');
});
