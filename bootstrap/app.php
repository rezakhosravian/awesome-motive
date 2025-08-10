<?php

use App\Http\Middleware\ApiKeyAuth;
use App\Http\Middleware\ValidateFlashcardBelongsToDeck;
use App\Services\Api\ApiResponseService;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'api.key' => ApiKeyAuth::class,
            'validate.flashcard.deck' => ValidateFlashcardBelongsToDeck::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Illuminate\Database\Eloquent\ModelNotFoundException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson() || str_contains($request->getPathInfo(), '/api/v1/')) {
                return ApiResponseService::notFound(__('api.responses.not_found'));
            }
        });
        
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson() || str_contains($request->getPathInfo(), '/api/v1/')) {
                return ApiResponseService::notFound(__('api.responses.not_found'));
            }
        });
    })->create();
