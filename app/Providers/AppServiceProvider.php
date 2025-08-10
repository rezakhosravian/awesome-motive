<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Infrastructure\Auth\ApiKeyHeaderResolver;
use App\Infrastructure\Auth\BearerTokenResolver;
use App\Infrastructure\Auth\ChainTokenResolver;
use App\Infrastructure\Auth\TokenResolverInterface;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(TokenResolverInterface::class, function () {
            return new ChainTokenResolver(
                new BearerTokenResolver(),
                new ApiKeyHeaderResolver()
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
    }
}
