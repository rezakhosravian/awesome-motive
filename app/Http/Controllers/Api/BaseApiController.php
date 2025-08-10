<?php

namespace App\Http\Controllers\Api;

use App\Contracts\Api\ApiResponseServiceInterface;
use App\Contracts\Service\AuthenticationServiceInterface;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Support\Facades\Log;
use App\DTOs\PaginationDTO;
use App\Exceptions\ApiExceptionInterface;
use App\Exceptions\Deck\DeckValidationException;

/**
 * Base API Controller implementing Template Method Pattern
 * 
 * This controller provides common functionality for all API controllers
 * following the Template Method design pattern to ensure consistency
 * and reduce code duplication. It implements SOLID principles and
 * clean architecture patterns.
 */
abstract class BaseApiController extends Controller
{

    public function __construct(
        protected readonly ApiResponseServiceInterface $responseService,
        protected readonly AuthenticationServiceInterface $authService
    ) {}

    /**
     * Template method for authenticated API operations.
     */
    protected function executeAuthenticatedOperation(Request $request, callable $operation): JsonResponse
    {
        try {
            $user = $this->authService->getCurrentUser($request);
            if (!$user) {
                return $this->responseService->unauthorized(__('api.auth.unauthorized'));
            }

            if (!$this->isAuthorized($request, $user)) {
                return $this->responseService->forbidden(__('api.auth.forbidden'));
            }

            $this->logOperation($request, $user);

            $result = $operation($user, $request);

            return $this->handleSuccessResponse($result);
        } catch (DeckValidationException $e) {
            return $this->responseService::make()
                ->status(\App\Enums\ApiStatusCode::VALIDATION_ERROR)
                ->message($e->getMessage())
                ->errors($e->getValidationErrors())
                ->meta(['error_code' => $e->getErrorCode()])
                ->build();
        } catch (ApiExceptionInterface $e) {
            return $this->handleApiException($e);
        } catch (ValidationException $e) {
            return $this->responseService->validationError(
                $e->errors(),
                __('api.responses.validation_error')
            );
        } catch (AuthorizationException $e) {
            return $this->responseService->forbidden(__('api.auth.forbidden'));
        } catch (ModelNotFoundException | NotFoundHttpException $e) {
            return $this->responseService->notFound(__('api.responses.not_found'));
        } catch (\Exception $e) {
            return $this->handleErrorResponse($e);
        }
    }

    /**
     * Template method for public API operations (no authentication required).
     */
    protected function executePublicOperation(Request $request, callable $operation): JsonResponse
    {
        try {
            $result = $operation($request);
            return $this->handleSuccessResponse($result);
        } catch (DeckValidationException $e) {
            return $this->responseService::make()
                ->status(\App\Enums\ApiStatusCode::VALIDATION_ERROR)
                ->message($e->getMessage())
                ->errors($e->getValidationErrors())
                ->meta(['error_code' => $e->getErrorCode()])
                ->build();
        } catch (ApiExceptionInterface $e) {
            return $this->handleApiException($e);
        } catch (ValidationException $e) {
            return $this->responseService->validationError(
                $e->errors(),
                __('api.responses.validation_error')
            );
        } catch (AuthorizationException $e) {
            return $this->responseService->forbidden(__('api.auth.forbidden'));
        } catch (ModelNotFoundException | NotFoundHttpException $e) {
            return $this->responseService->notFound(__('api.responses.not_found'));
        } catch (\Exception $e) {
            return $this->handleErrorResponse($e);
        }
    }

    /**
     * Get pagination parameters with validation.
     */
    protected function getPaginationParams(Request $request): array
    {
        $params = PaginationDTO::fromRequest($request);
        $params->validate();

        return [
            'per_page' => $params->perPage,
            'page' => $params->page,
        ];
    }

    /**
     * Authorization hook; override in child controllers when needed.
     */
    protected function isAuthorized(Request $request, User $user): bool
    {
        return true;
    }

    /**
     * Log API operation for monitoring.
     */
    protected function logOperation(Request $request, User $user): void
    {
        Log::info('API Operation', [
            'user_id' => $user->id,
            'endpoint' => $request->path(),
            'method' => $request->method(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);
    }

    /**
     * Handle successful operation response.
     * Controllers should return JsonResponse directly from ApiResponseService.
     */
    protected function handleSuccessResponse(mixed $result): JsonResponse
    {
        if ($result instanceof JsonResponse) {
            return $result;
        }

        return $this->responseService->success($result);
    }

    /**
     * Handle error response with proper logging.
     */
    protected function handleErrorResponse(\Exception $e): JsonResponse
    {
        Log::error('API Error', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);

        $message = app()->environment('production')
            ? __('api.responses.error')
            : $e->getMessage();

        return $this->responseService->error($message);
    }

    /**
     * Handle custom API exceptions with proper status codes and error information.
     */
    protected function handleApiException(ApiExceptionInterface $exception): JsonResponse
    {
        $statusCode = $exception->getApiStatusCode();
        $errorCode = $exception->getErrorCode();

        $message = $exception instanceof \Exception ? $exception->getMessage() : __('messages.general.error');

        return $this->responseService::make()
            ->status($statusCode)
            ->message($message)
            ->meta(['error_code' => $errorCode])
            ->build();
    }

    /**
     * Get auth context for operations that need it.
     */
    protected function getAuthContext(Request $request): array
    {
        return $this->authService->getAuthContext($request);
    }
}
