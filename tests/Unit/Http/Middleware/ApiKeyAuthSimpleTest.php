<?php

namespace Tests\Unit\Http\Middleware;

use App\Contracts\Api\ApiResponseServiceInterface;
use App\Contracts\Service\ApiTokenServiceInterface;
use App\Http\Middleware\ApiKeyAuth;
use App\Models\ApiToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Mockery;
use Tests\TestCase;

class ApiKeyAuthSimpleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Use SQLite in-memory for tests
        config(['database.default' => 'sqlite']);
        config(['database.connections.sqlite.database' => ':memory:']);
    }

    public function test_middleware_rejects_request_without_token()
    {
        $mockResponse = Mockery::mock(JsonResponse::class);
        $mockResponse->shouldReceive('getStatusCode')->andReturn(401);
        $mockResponse->shouldReceive('getContent')->andReturn('{"status":"unauthorized","message":"Authentication required"}');
        
        $responseService = Mockery::mock(ApiResponseServiceInterface::class);
        $responseService->shouldReceive('unauthorized')->andReturn($mockResponse);
        
        $tokenService = Mockery::mock(ApiTokenServiceInterface::class);
        // No expectation needed since we don't call authenticateToken with null
        
        $middleware = new ApiKeyAuth($responseService, $tokenService);
        $request = Request::create('/api/test');
        
        $response = $middleware->handle($request, function ($req) {
            return new Response('Success');
        });
        
        $this->assertEquals(401, $response->getStatusCode());
        
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('unauthorized', $data['status']);
        $this->assertArrayHasKey('message', $data);
    }

    public function test_middleware_accepts_valid_bearer_token()
    {
        $user = User::factory()->create();
        $apiToken = $user->apiTokens()->create([
            'name' => 'Test Token',
            'token' => hash('sha256', 'valid-test-token'),
            'abilities' => ['*'],
        ]);

        $responseService = Mockery::mock(ApiResponseServiceInterface::class);
        // No unauthorized call expected for valid token
        
        $tokenService = Mockery::mock(ApiTokenServiceInterface::class);
        $tokenService->shouldReceive('authenticateToken')->with('valid-test-token')->andReturn($apiToken);
        
        $middleware = new ApiKeyAuth($responseService, $tokenService);
        $request = Request::create('/api/test');
        $request->headers->set('Authorization', 'Bearer valid-test-token');
        
        $response = $middleware->handle($request, function ($req) {
            return new Response('Success');
        });
        
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Success', $response->getContent());
    }

    public function test_middleware_accepts_valid_api_key_header()
    {
        $user = User::factory()->create();
        $apiToken = $user->apiTokens()->create([
            'name' => 'Test Token',
            'token' => hash('sha256', 'valid-api-key'),
            'abilities' => ['read'],
        ]);

        $responseService = Mockery::mock(ApiResponseServiceInterface::class);
        // No unauthorized call expected for valid token
        
        $tokenService = Mockery::mock(ApiTokenServiceInterface::class);
        $tokenService->shouldReceive('authenticateToken')->with('valid-api-key')->andReturn($apiToken);
        
        $middleware = new ApiKeyAuth($responseService, $tokenService);
        $request = Request::create('/api/test');
        $request->headers->set('X-API-Key', 'valid-api-key');
        
        $response = $middleware->handle($request, function ($req) {
            return new Response('Success');
        });
        
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Success', $response->getContent());
    }

    public function test_middleware_rejects_expired_token()
    {
        $user = User::factory()->create();
        $apiToken = $user->apiTokens()->create([
            'name' => 'Expired Token',
            'token' => hash('sha256', 'expired-token'),
            'abilities' => ['*'],
            'expires_at' => now()->subDay()
        ]);

        $mockResponse = Mockery::mock(JsonResponse::class);
        $mockResponse->shouldReceive('getStatusCode')->andReturn(401);
        
        $responseService = Mockery::mock(ApiResponseServiceInterface::class);
        $responseService->shouldReceive('unauthorized')->andReturn($mockResponse);
        
        $tokenService = Mockery::mock(ApiTokenServiceInterface::class);
        $tokenService->shouldReceive('authenticateToken')->with('expired-token')->andReturn(null);
        
        $middleware = new ApiKeyAuth($responseService, $tokenService);
        $request = Request::create('/api/test');
        $request->headers->set('Authorization', 'Bearer expired-token');
        
        $response = $middleware->handle($request, function ($req) {
            return new Response('Success');
        });
        
        $this->assertEquals(401, $response->getStatusCode());
    }

    public function test_get_current_token_returns_token_from_request()
    {
        $apiToken = new ApiToken();
        $apiToken->id = 1;
        $apiToken->name = 'Test Token';
        
        $request = Request::create('/api/test');
        $request->attributes->set('api_token', $apiToken);
        
        $result = ApiKeyAuth::getCurrentToken($request);
        
        $this->assertInstanceOf(ApiToken::class, $result);
        $this->assertEquals(1, $result->id);
        $this->assertEquals('Test Token', $result->name);
    }

    public function test_get_current_user_returns_user_from_token()
    {
        $user = new User();
        $user->id = 1;
        $user->name = 'Test User';
        
        $apiToken = new ApiToken();
        $apiToken->id = 1;
        $apiToken->name = 'Test Token';
        $apiToken->user = $user;
        
        $request = Request::create('/api/test');
        $request->attributes->set('api_token', $apiToken);
        
        $result = ApiKeyAuth::getCurrentUser($request);
        
        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals(1, $result->id);
        $this->assertEquals('Test User', $result->name);
    }
}