<?php

namespace Tests\Unit\Http\Controllers\Api;

use App\Contracts\Api\ApiResponseServiceInterface;
use App\Contracts\Service\AuthenticationServiceInterface;
use App\Http\Controllers\Api\BaseApiController;
use App\Models\User;
use App\Services\Api\ApiResponseService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class BaseApiControllerTest extends TestCase
{
    private function makeController(bool $authorized = true, ?User $currentUser = null): BaseApiController
    {
        $responseService = new ApiResponseService();

        $authService = new class($currentUser) implements AuthenticationServiceInterface {
            public function __construct(private ?User $user) {}
            public function getCurrentUser(Request $request): ?User { return $this->user; }
            public function getCurrentToken(Request $request): ?\App\Models\ApiToken { return null; }
            public function isAuthenticated(Request $request): bool { return (bool) $this->user; }
            public function getAuthContext(Request $request): array { return ['user' => $this->user, 'token' => null, 'user_id' => $this->user?->id, 'token_name' => null, 'is_authenticated' => (bool) $this->user]; }
            public function setAuthContext(Request $request, User $user, \App\Models\ApiToken $token): void {}
        };

        return new class($responseService, $authService, $authorized) extends BaseApiController {
            public function __construct(ApiResponseServiceInterface $responseService, AuthenticationServiceInterface $authService, private bool $authorized)
            {
                parent::__construct($responseService, $authService);
            }

            protected function isAuthorized(Request $request, User $user): bool
            {
                return $this->authorized;
            }

            public function callAuth(Request $request, callable $op): JsonResponse
            {
                return $this->executeAuthenticatedOperation($request, $op);
            }

            public function callPublic(Request $request, callable $op): JsonResponse
            {
                return $this->executePublicOperation($request, $op);
            }

            public function getParams(Request $request): array
            {
                return $this->getPaginationParams($request);
            }
        };
    }

    public function test_unauthorized_when_no_user(): void
    {
        $controller = $this->makeController(true, null);
        $request = Request::create('/test', 'GET');

        $response = $controller->callAuth($request, fn() => ApiResponseService::success());

        $this->assertEquals(401, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('unauthorized', $data['status']);
    }

    public function test_forbidden_when_not_authorized(): void
    {
        $user = User::factory()->make(['id' => 123]);
        $controller = $this->makeController(false, $user);
        $request = Request::create('/test', 'GET');

        $response = $controller->callAuth($request, fn() => ApiResponseService::success());

        $this->assertEquals(403, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('forbidden', $data['status']);
    }

    public function test_not_found_is_mapped_for_model_not_found(): void
    {
        $user = User::factory()->make(['id' => 123]);
        $controller = $this->makeController(true, $user);
        $request = Request::create('/test', 'GET');

        $response = $controller->callAuth($request, function () {
            throw new ModelNotFoundException('missing');
        });

        $this->assertEquals(404, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('not_found', $data['status']);
    }

    public function test_not_found_is_mapped_for_http_not_found(): void
    {
        $user = User::factory()->make(['id' => 123]);
        $controller = $this->makeController(true, $user);
        $request = Request::create('/test', 'GET');

        $response = $controller->callAuth($request, function () {
            throw new NotFoundHttpException('missing');
        });

        $this->assertEquals(404, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('not_found', $data['status']);
    }

    public function test_validation_error_is_mapped(): void
    {
        $user = User::factory()->make(['id' => 123]);
        $controller = $this->makeController(true, $user);
        $request = Request::create('/test', 'POST');

        $response = $controller->callAuth($request, function () {
            throw ValidationException::withMessages(['field' => ['Required']]);
        });

        $this->assertEquals(422, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('validation_error', $data['status']);
        $this->assertArrayHasKey('errors', $data);
    }

    public function test_generic_error_is_mapped(): void
    {
        $user = User::factory()->make(['id' => 123]);
        $controller = $this->makeController(true, $user);
        $request = Request::create('/test', 'GET');

        $response = $controller->callAuth($request, function () {
            throw new \RuntimeException('boom');
        });

        // Generic exceptions map to ApiStatusCode::ERROR (400) in this project
        $this->assertEquals(400, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('error', $data['status']);
    }

    public function test_public_operation_success_passthrough_json_response(): void
    {
        $controller = $this->makeController();
        $request = Request::create('/test', 'GET');

        $custom = ApiResponseService::success(['ok' => true], 'done');
        $response = $controller->callPublic($request, fn() => $custom);

        $this->assertSame($custom->getStatusCode(), $response->getStatusCode());
        $this->assertSame($custom->getContent(), $response->getContent());
    }

    public function test_pagination_params_are_bounded(): void
    {
        $controller = $this->makeController();
        $request = Request::create('/test', 'GET', ['per_page' => 999, 'page' => -5]);

        $params = $controller->getParams($request);
        $this->assertEquals(50, $params['per_page']);
        $this->assertEquals(1, $params['page']);
    }
}


