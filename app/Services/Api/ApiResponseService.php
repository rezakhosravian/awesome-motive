<?php

namespace App\Services\Api;

use App\Contracts\Api\ApiResponseServiceInterface;
use App\Enums\ApiStatusCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;


class ApiResponseService implements ApiResponseServiceInterface
{
    private ApiStatusCode $status;
    private ?string $message = null;
    private mixed $data = null;
    private array $meta = [];
    private array $errors = [];
    private ?array $pagination = null;

    public function __construct()
    {
        $this->status = ApiStatusCode::SUCCESS;
    }

    public static function make(): self
    {
        return new self();
    }

    public function status(ApiStatusCode $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function message(?string $message): self
    {
        $this->message = $message;
        return $this;
    }

    public function data(mixed $data): self
    {
        $this->data = $data;
        return $this;
    }

    public function meta(array $meta): self
    {
        $this->meta = array_merge($this->meta, $meta);
        return $this;
    }

    public function errors(array $errors): self
    {
        $this->errors = $errors;
        return $this;
    }

    public function pagination(LengthAwarePaginator $paginator): self
    {
        $this->pagination = [
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
            'has_more_pages' => $paginator->hasMorePages(),
        ];

        if ($this->data === null) {
            $this->data = $paginator->items();
        }

        return $this;
    }

    public function build(): JsonResponse
    {
        $response = [
            'status' => $this->status->value,
            'message' => $this->resolveMessage(),
            'timestamp' => now()->toISOString(),
        ];

        if ($this->data !== null) {
            $response['data'] = $this->transformData($this->data);
        }

        if (!empty($this->meta)) {
            $response['meta'] = $this->meta;
        }

        if ($this->pagination !== null) {
            $response['pagination'] = $this->pagination;
        }

        if (!empty($this->errors)) {
            $response['errors'] = $this->errors;
        }

        return response()->json($response, $this->status->httpCode());
    }

    // Convenience methods for common responses
    public static function success(mixed $data = null, ?string $message = null): JsonResponse
    {
        return self::make()
            ->status(ApiStatusCode::SUCCESS)
            ->data($data)
            ->message($message ?? __('api.responses.success'))
            ->build();
    }

    public static function created(mixed $data = null, ?string $message = null): JsonResponse
    {
        return self::make()
            ->status(ApiStatusCode::CREATED)
            ->data($data)
            ->message($message ?? __('api.responses.created'))
            ->build();
    }

    public static function updated(mixed $data = null, ?string $message = null): JsonResponse
    {
        return self::make()
            ->status(ApiStatusCode::UPDATED)
            ->data($data)
            ->message($message ?? __('api.responses.updated'))
            ->build();
    }

    public static function deleted(?string $message = null): JsonResponse
    {
        return self::make()
            ->status(ApiStatusCode::DELETED)
            ->message($message ?? __('api.responses.deleted'))
            ->build();
    }

    public static function error(?string $message = null, ?int $httpStatus = null): JsonResponse
    {
        return self::make()
            ->status(ApiStatusCode::ERROR)
            ->message($message ?? __('api.responses.error'))
            ->build()
            ->setStatusCode($httpStatus ?? ApiStatusCode::ERROR->httpCode());
    }

    public static function unauthorized(?string $message = null): JsonResponse
    {
        return self::make()
            ->status(ApiStatusCode::UNAUTHORIZED)
            ->message($message ?? __('api.responses.unauthorized'))
            ->build();
    }

    public static function badRequest(?string $message = null): JsonResponse
    {
        return self::make()
            ->status(ApiStatusCode::BAD_REQUEST)
            ->message($message ?? __('api.responses.bad_request'))
            ->build();
    }

    public static function forbidden(?string $message = null): JsonResponse
    {
        return self::make()
            ->status(ApiStatusCode::FORBIDDEN)
            ->message($message ?? __('api.responses.forbidden'))
            ->build();
    }

    public static function notFound(?string $message = null): JsonResponse
    {
        return self::make()
            ->status(ApiStatusCode::NOT_FOUND)
            ->message($message ?? __('api.responses.not_found'))
            ->build();
    }

    public static function validationError(array $errors = [], ?string $message = null): JsonResponse
    {
        return self::make()
            ->status(ApiStatusCode::VALIDATION_ERROR)
            ->message($message ?? __('api.responses.validation_error'))
            ->errors($errors)
            ->build();
    }

    public static function paginated(LengthAwarePaginator $paginator, ?string $message = null, array $meta = []): JsonResponse
    {
        return self::make()
            ->status(ApiStatusCode::SUCCESS)
            ->pagination($paginator)
            ->meta($meta)
            ->message($message ?? __('api.responses.success'))
            ->build();
    }

    public static function tooManyRequests(?string $message = null): JsonResponse
    {
        return self::make()
            ->status(ApiStatusCode::TOO_MANY_REQUESTS)
            ->message($message ?? __('api.responses.too_many_requests'))
            ->build();
    }

    private function resolveMessage(): string
    {
        if ($this->message !== null) {
            return $this->message;
        }

        return __($this->status->message());
    }

    private function transformData(mixed $data): mixed
    {
        if ($data instanceof JsonResource || $data instanceof ResourceCollection) {
            return $data->resolve();
        }

        return $data;
    }
}
