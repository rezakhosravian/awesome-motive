<?php

namespace Tests\Unit\Unit;

use App\Models\ApiToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiTokenModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_generate_token_creates_random_string()
    {
        $token1 = ApiToken::generateToken();
        $token2 = ApiToken::generateToken();

        $this->assertIsString($token1);
        $this->assertIsString($token2);
        $this->assertEquals(40, strlen($token1));
        $this->assertEquals(40, strlen($token2));
        $this->assertNotEquals($token1, $token2);
    }

    public function test_token_is_not_expired_when_no_expiration_set()
    {
        $user = User::factory()->create();
        $token = $user->apiTokens()->create([
            'name' => 'Test Token',
            'token' => hash('sha256', 'test-token'),
            'abilities' => ['*'],
        ]);

        $this->assertFalse($token->isExpired());
    }

    public function test_token_is_not_expired_when_expiration_is_in_future()
    {
        $user = User::factory()->create();
        $token = $user->apiTokens()->create([
            'name' => 'Test Token',
            'token' => hash('sha256', 'test-token'),
            'abilities' => ['*'],
            'expires_at' => now()->addDay(),
        ]);

        $this->assertFalse($token->isExpired());
    }

    public function test_token_is_expired_when_expiration_is_in_past()
    {
        $user = User::factory()->create();
        $token = $user->apiTokens()->create([
            'name' => 'Test Token',
            'token' => hash('sha256', 'test-token'),
            'abilities' => ['*'],
            'expires_at' => now()->subDay(),
        ]);

        $this->assertTrue($token->isExpired());
    }

    public function test_token_can_check_abilities()
    {
        $user = User::factory()->create();
        $token = $user->apiTokens()->create([
            'name' => 'Test Token',
            'token' => hash('sha256', 'test-token'),
            'abilities' => ['read', 'write'],
        ]);

        $this->assertTrue($token->can('read'));
        $this->assertTrue($token->can('write'));
        $this->assertFalse($token->can('delete'));
    }

    public function test_token_with_wildcard_ability_can_do_anything()
    {
        $user = User::factory()->create();
        $token = $user->apiTokens()->create([
            'name' => 'Test Token',
            'token' => hash('sha256', 'test-token'),
            'abilities' => ['*'],
        ]);

        $this->assertTrue($token->can('read'));
        $this->assertTrue($token->can('write'));
        $this->assertTrue($token->can('delete'));
        $this->assertTrue($token->can('any-ability'));
    }

    public function test_expired_token_cannot_do_anything()
    {
        $user = User::factory()->create();
        $token = $user->apiTokens()->create([
            'name' => 'Test Token',
            'token' => hash('sha256', 'test-token'),
            'abilities' => ['*'],
            'expires_at' => now()->subDay(),
        ]);

        $this->assertFalse($token->can('read'));
        $this->assertFalse($token->can('write'));
        $this->assertFalse($token->can('delete'));
    }

    public function test_update_last_used_updates_timestamp()
    {
        $user = User::factory()->create();
        $token = $user->apiTokens()->create([
            'name' => 'Test Token',
            'token' => hash('sha256', 'test-token'),
            'abilities' => ['*'],
        ]);

        $this->assertNull($token->last_used_at);

        $token->updateLastUsed();

        $this->assertNotNull($token->fresh()->last_used_at);
    }

    public function test_user_relationship()
    {
        $user = User::factory()->create();
        $token = $user->apiTokens()->create([
            'name' => 'Test Token',
            'token' => hash('sha256', 'test-token'),
            'abilities' => ['*'],
        ]);

        $this->assertInstanceOf(User::class, $token->user);
        $this->assertEquals($user->id, $token->user->id);
    }

    public function test_token_is_hidden_from_serialization()
    {
        $user = User::factory()->create();
        $token = $user->apiTokens()->create([
            'name' => 'Test Token',
            'token' => hash('sha256', 'test-token'),
            'abilities' => ['*'],
        ]);

        $array = $token->toArray();

        $this->assertArrayNotHasKey('token', $token->toArray());
        $this->assertArrayHasKey('name', $token->toArray());
        $this->assertArrayHasKey('abilities', $token->toArray());
    }

    public function test_fillable_attributes()
    {
        $user = User::factory()->create();
        $token = $user->apiTokens()->create([
            'name' => 'Test Token',
            'token' => hash('sha256', 'test-token'),
            'abilities' => ['read', 'write'],
            'expires_at' => now()->addDay(),
        ]);

        $this->assertEquals('Test Token', $token->name);
        $this->assertEquals(['read', 'write'], $token->abilities);
        $this->assertNotNull($token->expires_at);
    }

    public function test_casts_are_applied()
    {
        $user = User::factory()->create();
        $token = $user->apiTokens()->create([
            'name' => 'Test Token',
            'token' => hash('sha256', 'test-token'),
            'abilities' => ['read', 'write'],
            'expires_at' => now()->addDay(),
        ]);

        $this->assertIsArray($token->abilities);
        $this->assertInstanceOf(\Carbon\Carbon::class, $token->expires_at);
    }
}
