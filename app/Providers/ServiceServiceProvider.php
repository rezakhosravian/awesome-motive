<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Contracts\Api\ApiResponseServiceInterface;
use App\Contracts\Service\UserServiceInterface;
use App\Contracts\Service\DeckServiceInterface;
use App\Contracts\Service\ApiTokenServiceInterface;
use App\Contracts\Service\FlashcardServiceInterface;
use App\Contracts\Service\AuthenticationServiceInterface;
use App\Services\Api\ApiResponseService;
use App\Services\UserService;
use App\Services\DeckService;
use App\Services\ApiTokenService;
use App\Services\FlashcardService;
use App\Services\AuthenticationService;

class ServiceServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(UserServiceInterface::class, UserService::class);
        $this->app->bind(DeckServiceInterface::class, DeckService::class);
        $this->app->bind(ApiResponseServiceInterface::class, ApiResponseService::class);
        $this->app->bind(ApiTokenServiceInterface::class, ApiTokenService::class);
        $this->app->bind(FlashcardServiceInterface::class, FlashcardService::class);
        $this->app->bind(AuthenticationServiceInterface::class, AuthenticationService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        
    }
} 