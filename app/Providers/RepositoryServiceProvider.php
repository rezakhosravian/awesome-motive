<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Contracts\Repository\DeckRepositoryInterface;
use App\Contracts\Repository\FlashcardRepositoryInterface;
use App\Contracts\Repository\ApiTokenRepositoryInterface;
use App\Repositories\DeckRepository;
use App\Repositories\FlashcardRepository;
use App\Repositories\ApiTokenRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register repository services.
     */
    public function register(): void
    {
        // Bind Repository Interfaces
        $this->app->bind(DeckRepositoryInterface::class, DeckRepository::class);
        $this->app->bind(FlashcardRepositoryInterface::class, FlashcardRepository::class);
        $this->app->bind(ApiTokenRepositoryInterface::class, ApiTokenRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
    }
} 