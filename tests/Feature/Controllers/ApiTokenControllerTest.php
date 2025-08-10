<?php

namespace Tests\Feature\Controllers;

use App\Models\ApiToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiTokenControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $otherUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->otherUser = User::factory()->create();
    }

    public function test_authenticated_user_can_view_api_tokens_index()
    {
        // Create some API tokens for the user
        $token1 = $this->user->apiTokens()->create([
            'name' => 'Test Token 1',
            'token' => hash('sha256', 'token1'),
            'abilities' => ['read'],
            'created_at' => now()->subMinute()
        ]);
        
        $token2 = $this->user->apiTokens()->create([
            'name' => 'Test Token 2',
            'token' => hash('sha256', 'token2'),
            'abilities' => ['*'],
            'created_at' => now()
        ]);

        $response = $this->actingAs($this->user)
            ->getJson(route('api-tokens.index'));

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         '*' => [
                             'id',
                             'name',
                             'abilities',
                             'last_used_at',
                             'expires_at',
                             'created_at'
                         ]
                     ]
                 ]);

        $data = $response->json('data');
        $this->assertCount(2, $data);
        
        // Verify both tokens are present (order may vary)
        $tokenNames = array_column($data, 'name');
        $this->assertContains('Test Token 1', $tokenNames);
        $this->assertContains('Test Token 2', $tokenNames);
    }

    public function test_guest_cannot_view_api_tokens_index()
    {
        $response = $this->getJson(route('api-tokens.index'));

        $response->assertStatus(401);
    }

    public function test_user_only_sees_their_own_tokens()
    {
        // Create token for authenticated user
        $this->user->apiTokens()->create([
            'name' => 'My Token',
            'token' => hash('sha256', 'mytoken'),
            'abilities' => ['read'],
        ]);

        // Create token for other user
        $this->otherUser->apiTokens()->create([
            'name' => 'Other Token',
            'token' => hash('sha256', 'othertoken'),
            'abilities' => ['read'],
        ]);

        $response = $this->actingAs($this->user)
            ->getJson(route('api-tokens.index'));

        $response->assertStatus(200);
        
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('My Token', $data[0]['name']);
    }

    public function test_index_excludes_token_field()
    {
        $this->user->apiTokens()->create([
            'name' => 'Secret Token',
            'token' => hash('sha256', 'secrettoken'),
            'abilities' => ['read'],
        ]);

        $response = $this->actingAs($this->user)
            ->getJson(route('api-tokens.index'));

        $response->assertStatus(200);
        
        $data = $response->json('data');
        $this->assertArrayNotHasKey('token', $data[0]);
    }

    public function test_authenticated_user_can_create_api_token()
    {
        $tokenData = [
            'name' => 'New API Token',
            'abilities' => ['read', 'write'],
        ];

        $response = $this->actingAs($this->user)
            ->postJson(route('api-tokens.store'), $tokenData);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'message',
                     'data' => [
                         'id',
                         'name',
                         'token',
                         'abilities',
                         'expires_at',
                         'created_at'
                     ]
                 ]);

        $this->assertDatabaseHas('api_tokens', [
            'user_id' => $this->user->id,
            'name' => 'New API Token',
        ]);

        // Verify token is returned only once
        $responseData = $response->json('data');
        $this->assertNotEmpty($responseData['token']);
        $this->assertEquals(['read', 'write'], $responseData['abilities']);
    }

    public function test_guest_cannot_create_api_token()
    {
        $tokenData = [
            'name' => 'Unauthorized Token',
            'abilities' => ['read'],
        ];

        $response = $this->postJson(route('api-tokens.store'), $tokenData);

        $response->assertStatus(401);
        
        $this->assertDatabaseMissing('api_tokens', [
            'name' => 'Unauthorized Token'
        ]);
    }

    public function test_create_api_token_with_expiration()
    {
        $futureDate = now()->addDays(30)->format('Y-m-d H:i:s');
        
        $tokenData = [
            'name' => 'Expiring Token',
            'abilities' => ['read'],
            'expires_at' => $futureDate
        ];

        $response = $this->actingAs($this->user)
            ->postJson(route('api-tokens.store'), $tokenData);

        $response->assertStatus(201);

        $this->assertDatabaseHas('api_tokens', [
            'user_id' => $this->user->id,
            'name' => 'Expiring Token',
        ]);

        $token = ApiToken::where('name', 'Expiring Token')->first();
        $this->assertNotNull($token->expires_at);
    }

    public function test_create_api_token_defaults_to_all_abilities()
    {
        $tokenData = [
            'name' => 'Default Abilities Token',
        ];

        $response = $this->actingAs($this->user)
            ->postJson(route('api-tokens.store'), $tokenData);

        $response->assertStatus(201);

        $responseData = $response->json('data');
        $this->assertEquals(['*'], $responseData['abilities']);
    }

    public function test_create_api_token_validation_requires_name()
    {
        $tokenData = [
            'abilities' => ['read'],
        ];

        $response = $this->actingAs($this->user)
            ->postJson(route('api-tokens.store'), $tokenData);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name']);
    }

    public function test_create_api_token_validation_name_max_length()
    {
        $tokenData = [
            'name' => str_repeat('a', 256), // Too long
            'abilities' => ['read'],
        ];

        $response = $this->actingAs($this->user)
            ->postJson(route('api-tokens.store'), $tokenData);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name']);
    }

    public function test_create_api_token_validation_invalid_abilities()
    {
        $tokenData = [
            'name' => 'Invalid Abilities Token',
            'abilities' => ['invalid_ability'],
        ];

        $response = $this->actingAs($this->user)
            ->postJson(route('api-tokens.store'), $tokenData);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['abilities.0']);
    }

    public function test_create_api_token_validation_past_expiration()
    {
        $pastDate = now()->subDay()->format('Y-m-d H:i:s');
        
        $tokenData = [
            'name' => 'Past Expiration Token',
            'expires_at' => $pastDate
        ];

        $response = $this->actingAs($this->user)
            ->postJson(route('api-tokens.store'), $tokenData);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['expires_at']);
    }

    public function test_authenticated_user_can_delete_own_token()
    {
        $token = $this->user->apiTokens()->create([
            'name' => 'Token to Delete',
            'token' => hash('sha256', 'deletetoken'),
            'abilities' => ['read'],
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson(route('api-tokens.destroy', $token));

        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'API token deleted successfully'
                 ]);

        $this->assertDatabaseMissing('api_tokens', [
            'id' => $token->id
        ]);
    }

    public function test_user_cannot_delete_other_users_token()
    {
        $otherToken = $this->otherUser->apiTokens()->create([
            'name' => 'Other User Token',
            'token' => hash('sha256', 'othertoken'),
            'abilities' => ['read'],
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson(route('api-tokens.destroy', $otherToken));

        $response->assertStatus(403)
                 ->assertJson([
                     'status' => 'forbidden',
                     'message' => 'Token does not belong to the user.'
                 ]);

        $this->assertDatabaseHas('api_tokens', [
            'id' => $otherToken->id
        ]);
    }

    public function test_guest_cannot_delete_token()
    {
        $token = $this->user->apiTokens()->create([
            'name' => 'Protected Token',
            'token' => hash('sha256', 'protectedtoken'),
            'abilities' => ['read'],
        ]);

        $response = $this->deleteJson(route('api-tokens.destroy', $token));

        $response->assertStatus(401);

        $this->assertDatabaseHas('api_tokens', [
            'id' => $token->id
        ]);
    }

    public function test_delete_nonexistent_token_returns_404()
    {
        $response = $this->actingAs($this->user)
            ->deleteJson(route('api-tokens.destroy', 99999));

        $response->assertStatus(404);
    }

    public function test_created_token_has_correct_structure()
    {
        $tokenData = [
            'name' => 'Structure Test Token',
            'abilities' => ['read', 'write'],
        ];

        $response = $this->actingAs($this->user)
            ->postJson(route('api-tokens.store'), $tokenData);

        $response->assertStatus(201);

        $responseData = $response->json('data');
        
        $this->assertIsInt($responseData['id']);
        $this->assertEquals('Structure Test Token', $responseData['name']);
        $this->assertIsString($responseData['token']);
        $this->assertNotEmpty($responseData['token']);
        $this->assertEquals(['read', 'write'], $responseData['abilities']);
        $this->assertNull($responseData['expires_at']);
        $this->assertNotNull($responseData['created_at']);
    }

    public function test_token_is_properly_hashed_in_database()
    {
        $tokenData = [
            'name' => 'Hash Test Token',
        ];

        $response = $this->actingAs($this->user)
            ->postJson(route('api-tokens.store'), $tokenData);

        $response->assertStatus(201);

        $plainToken = $response->json('data.token');
        $hashedToken = hash('sha256', $plainToken);

        $this->assertDatabaseHas('api_tokens', [
            'name' => 'Hash Test Token',
            'token' => $hashedToken
        ]);

        // Verify plain token is not stored
        $this->assertDatabaseMissing('api_tokens', [
            'token' => $plainToken
        ]);
    }

    public function test_multiple_operations_workflow()
    {
        // Create a token
        $response = $this->actingAs($this->user)
            ->postJson(route('api-tokens.store'), [
                'name' => 'Workflow Token',
                'abilities' => ['read']
            ]);

        $response->assertStatus(201);
        $tokenId = $response->json('data.id');

        // List tokens
        $listResponse = $this->actingAs($this->user)
            ->getJson(route('api-tokens.index'));

        $listResponse->assertStatus(200);
        $tokens = $listResponse->json('data');
        $this->assertCount(1, $tokens);
        $this->assertEquals('Workflow Token', $tokens[0]['name']);

        // Delete token
        $deleteResponse = $this->actingAs($this->user)
            ->deleteJson(route('api-tokens.destroy', $tokenId));

        $deleteResponse->assertStatus(200);

        // Verify token is gone
        $finalListResponse = $this->actingAs($this->user)
            ->getJson(route('api-tokens.index'));

        $finalListResponse->assertStatus(200);
        $finalTokens = $finalListResponse->json('data');
        $this->assertCount(0, $finalTokens);
    }
}