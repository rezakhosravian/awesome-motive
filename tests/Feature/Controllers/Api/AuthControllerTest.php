<?php

namespace Tests\Feature\Controllers\Api;

use App\Models\ApiToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Use SQLite in-memory for tests
        config(['database.default' => 'sqlite']);
        config(['database.connections.sqlite.database' => ':memory:']);
    }

    public function test_auth_test_endpoint_requires_authentication()
    {
        $response = $this->get('/api/v1/auth/test');
        
        $response->assertStatus(401);
        $response->assertJsonStructure([
            'status',
            'message',
            'timestamp'
        ]);
        $response->assertJson(['status' => 'unauthorized']);
    }

    public function test_auth_test_endpoint_with_valid_bearer_token()
    {
        $user = User::factory()->create();
        $apiToken = $user->apiTokens()->create([
            'name' => 'Test Token',
            'token' => hash('sha256', 'valid-test-token'),
            'abilities' => ['*'],
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer valid-test-token'
        ])->get('/api/v1/auth/test');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'message',
            'timestamp',
            'data' => [
                'user' => [
                    'id',
                    'name',
                    'email',
                    'created_at'
                ],
                'token' => [
                    'id',
                    'name',
                    'abilities',
                    'last_used_at',
                    'expires_at',
                    'created_at',
                    'is_expired'
                ]
            ]
        ]);
        
        $response->assertJson([
            'status' => 'success',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email
                ],
                'token' => [
                    'id' => $apiToken->id,
                    'name' => 'Test Token',
                    'abilities' => ['*']
                ]
            ]
        ]);
    }

    public function test_auth_test_endpoint_with_valid_api_key_header()
    {
        $user = User::factory()->create();
        $apiToken = $user->apiTokens()->create([
            'name' => 'Test Token',
            'token' => hash('sha256', 'valid-api-key'),
            'abilities' => ['read'],
        ]);

        $response = $this->withHeaders([
            'X-API-Key' => 'valid-api-key'
        ])->get('/api/v1/auth/test');

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name
                ],
                'token' => [
                    'id' => $apiToken->id,
                    'name' => 'Test Token',
                    'abilities' => ['read']
                ]
            ]
        ]);
    }

    public function test_auth_test_endpoint_with_expired_token()
    {
        $user = User::factory()->create();
        $apiToken = $user->apiTokens()->create([
            'name' => 'Expired Token',
            'token' => hash('sha256', 'expired-token'),
            'abilities' => ['*'],
            'expires_at' => now()->subDay()
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer expired-token'
        ])->get('/api/v1/auth/test');

        $response->assertStatus(401);
        $response->assertJson(['status' => 'unauthorized']);
    }

    public function test_auth_test_endpoint_with_invalid_token()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer invalid-token'
        ])->get('/api/v1/auth/test');

        $response->assertStatus(401);
        $response->assertJson(['status' => 'unauthorized']);
    }

    public function test_response_includes_correct_timestamp_format()
    {
        $user = User::factory()->create();
        $user->apiTokens()->create([
            'name' => 'Test Token',
            'token' => hash('sha256', 'valid-token'),
            'abilities' => ['*'],
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer valid-token'
        ])->get('/api/v1/auth/test');

        $data = $response->json();
        $this->assertArrayHasKey('timestamp', $data);
        $this->assertMatchesRegularExpression('/\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/', $data['timestamp']);
    }
}