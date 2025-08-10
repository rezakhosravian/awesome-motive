<?php

namespace App\Contracts\Api;

use App\Enums\ApiStatusCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Interface for API Response Service
 * 
 * Provides a contract for generating consistent API responses
 * following professional standards and best practices.
 */
interface ApiResponseServiceInterface
{
    /**
     * Create a new response builder instance
     */
    public static function make(): self;

    /**
     * Set the response status
     */
    public function status(ApiStatusCode $status): self;

    /**
     * Set custom message (overrides default locale message)
     */
    public function message(?string $message): self;

    /**
     * Set response data
     */
    public function data(mixed $data): self;

    /**
     * Set pagination information
     */
    public function pagination(LengthAwarePaginator $paginator): self;

    /**
     * Set additional metadata
     */
    public function meta(array $meta): self;

    /**
     * Set validation or other errors
     */
    public function errors(array $errors): self;

    /**
     * Build and return the JSON response
     */
    public function build(): JsonResponse;

    /**
     * Quick success response
     */
    public static function success(mixed $data = null, ?string $message = null): JsonResponse;

    /**
     * Quick created response
     */
    public static function created(mixed $data = null, ?string $message = null): JsonResponse;

    /**
     * Quick updated response
     */
    public static function updated(mixed $data = null, ?string $message = null): JsonResponse;

    /**
     * Quick deleted response
     */
    public static function deleted(?string $message = null): JsonResponse;

    /**
     * Quick paginated response
     */
    public static function paginated(LengthAwarePaginator $paginator, ?string $message = null, array $meta = []): JsonResponse;

    /**
     * Quick error response
     */
    public static function error(?string $message = null, int $httpStatus = 500): JsonResponse;

    /**
     * Quick unauthorized response
     */
    public static function unauthorized(?string $message = null): JsonResponse;

    /**
     * Quick not found response
     */
    public static function notFound(?string $message = null): JsonResponse;

    /**
     * Quick validation error response
     */
    public static function validationError(array $errors = [], ?string $message = null): JsonResponse;

    /**
     * Quick bad request response
     */
    public static function badRequest(?string $message = null): JsonResponse;

    /**
     * Quick forbidden response
     */
    public static function forbidden(?string $message = null): JsonResponse;
}