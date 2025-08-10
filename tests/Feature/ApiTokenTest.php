<?php

namespace Tests\Feature;

use App\Models\ApiToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiTokenTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_api_tokens_page()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/manage-api-tokens');

        $response->assertStatus(200);
        $response->assertSee('API Tokens');
        $response->assertSee('Create New API Token');
    }

    public function test_user_can_create_api_token()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api-tokens', [
            'name' => 'Test Token',
            'abilities' => ['read', 'write'],
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'message',
            'data' => [
                'id',
                'name',
                'token',
                'abilities',
                'expires_at',
                'created_at',
            ]
        ]);

        $this->assertDatabaseHas('api_tokens', [
            'user_id' => $user->id,
            'name' => 'Test Token',
        ]);
    }

    public function test_user_can_create_token_with_expiration()
    {
        $user = User::factory()->create();
        $expiresAt = now()->addDays(30)->toISOString();

        $response = $this->actingAs($user)->postJson('/api-tokens', [
            'name' => 'Expiring Token',
            'abilities' => ['read'],
            'expires_at' => $expiresAt,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('api_tokens', [
            'user_id' => $user->id,
            'name' => 'Expiring Token',
        ]);
    }

    public function test_user_can_list_their_tokens()
    {
        $user = User::factory()->create();
        $token = $user->createApiToken('Test Token', ['read']);

        $response = $this->actingAs($user)->getJson('/api-tokens');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'abilities',
                    'last_used_at',
                    'expires_at',
                    'created_at',
                ]
            ]
        ]);
    }

    public function test_user_can_delete_their_token()
    {
        $user = User::factory()->create();
        $token = $user->createApiToken('Test Token', ['read']);

        $response = $this->actingAs($user)->deleteJson("/api-tokens/{$token->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('api_tokens', ['id' => $token->id]);
    }

    public function test_user_cannot_delete_other_users_token()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $token = $user2->createApiToken('Test Token', ['read']);

        $response = $this->actingAs($user1)->deleteJson("/api-tokens/{$token->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('api_tokens', ['id' => $token->id]);
    }

    public function test_token_validation_requires_name()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api-tokens', [
            'abilities' => ['read'],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name']);
    }

    public function test_token_validation_accepts_valid_abilities()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api-tokens', [
            'name' => 'Test Token',
            'abilities' => ['read', 'write', 'delete', '*'],
        ]);

        $response->assertStatus(201);
    }

    public function test_token_validation_rejects_invalid_abilities()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api-tokens', [
            'name' => 'Test Token',
            'abilities' => ['invalid_ability'],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['abilities.0']);
    }

    public function test_token_validation_rejects_past_expiration()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api-tokens', [
            'name' => 'Test Token',
            'expires_at' => now()->subDay()->toISOString(),
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['expires_at']);
    }

    public function test_api_authentication_with_valid_token()
    {
        $user = User::factory()->create();
        $plainToken = ApiToken::generateToken();
        $token = $user->apiTokens()->create([
            'name' => 'Test Token',
            'token' => hash('sha256', $plainToken),
            'abilities' => ['*'],
        ]);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$plainToken}"
        ])->getJson('/api/v1/decks');

        $response->assertStatus(200);
    }

    public function test_api_authentication_with_invalid_token()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer invalid-token'
        ])->getJson('/api/v1/decks');

        $response->assertStatus(401);
        $response->assertJson([
            'status' => 'unauthorized',
            'message' => 'Authentication credentials are required or invalid.'
        ]);
    }

    public function test_api_authentication_with_expired_token()
    {
        $user = User::factory()->create();
        $plainToken = ApiToken::generateToken();
        $token = $user->apiTokens()->create([
            'name' => 'Expired Token',
            'token' => hash('sha256', $plainToken),
            'abilities' => ['*'],
            'expires_at' => now()->subDay(),
        ]);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$plainToken}"
        ])->getJson('/api/v1/decks');

        $response->assertStatus(401);
    }

    public function test_api_authentication_without_token()
    {
        $response = $this->getJson('/api/v1/decks');

        $response->assertStatus(401);
    }

    public function test_token_last_used_is_updated()
    {
        $user = User::factory()->create();
        $plainToken = ApiToken::generateToken();
        $token = $user->apiTokens()->create([
            'name' => 'Test Token',
            'token' => hash('sha256', $plainToken),
            'abilities' => ['*'],
        ]);

        $this->assertNull($token->last_used_at);

        $this->withHeaders([
            'Authorization' => "Bearer {$plainToken}"
        ])->getJson('/api/v1/decks');

        $token->refresh();
        $this->assertNotNull($token->last_used_at);
    }

    public function test_legacy_api_key_still_works()
    {
        $user = User::factory()->create();
        $legacyToken = $user->apiTokens()->create([
            'name' => 'Legacy Token',
            'token' => hash('sha256', 'flashcard-pro-demo-key-123'),
            'abilities' => ['*'],
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer flashcard-pro-demo-key-123'
        ])->getJson('/api/v1/decks');

        $response->assertStatus(200);
    }

    public function test_x_api_key_header_still_works()
    {
        $user = User::factory()->create();
        $legacyToken = $user->apiTokens()->create([
            'name' => 'Legacy Token',
            'token' => hash('sha256', 'flashcard-pro-demo-key-123'),
            'abilities' => ['*'],
        ]);

        $response = $this->withHeaders([
            'X-API-Key' => 'flashcard-pro-demo-key-123'
        ])->getJson('/api/v1/decks');

        $response->assertStatus(200);
    }
}
