<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Contracts\Api\ApiResponseServiceInterface;
use App\Contracts\Service\AuthenticationServiceInterface;
use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\AuthTestRequest;
use App\Http\Resources\Api\ApiTokenResource;
use App\Http\Resources\Api\UserResource;
use Illuminate\Http\JsonResponse;

/**
 * Auth Test Controller
 * 
 * Provides authentication testing endpoint with professional
 * response structure and proper error handling.
 */
class TestController extends BaseApiController
{
    public function __construct(
        ApiResponseServiceInterface $responseService,
        AuthenticationServiceInterface $authService
    ) {
        parent::__construct($responseService, $authService);
    }

    public function __invoke(AuthTestRequest $request): JsonResponse
    {
        return $this->executeAuthenticatedOperation($request, function ($user, $request) {
            $authContext = $this->getAuthContext($request);
            
            return $this->responseService->success(
                [
                    'user' => new UserResource($authContext['user']),
                    'token' => new ApiTokenResource($authContext['token']),
                    'context' => [
                        'user_id' => $authContext['user_id'],
                        'token_name' => $authContext['token_name'],
                        'is_authenticated' => $authContext['is_authenticated']
                    ]
                ],
                __('api.auth.test_success')
            );
        });
    }
}
