<?php

namespace Tests\Unit\Resources\Api;

use App\Http\Resources\Api\ApiTokenResource;
use App\Models\ApiToken;
use Illuminate\Http\Request;
use Tests\TestCase;

class ApiTokenResourceTest extends TestCase
{
    public function test_api_token_resource_structure()
    {
        $token = new ApiToken([
            'id' => 1,
            'name' => 'Test Token',
            'abilities' => ['read', 'write'],
            'last_used_at' => now(),
            'expires_at' => now()->addDays(30),
            'created_at' => now()
        ]);
        
        // Mock the isExpired method
        $token = $this->createPartialMock(ApiToken::class, ['isExpired']);
        $token->method('isExpired')->willReturn(false);
        $token->id = 1;
        $token->name = 'Test Token';
        $token->abilities = ['read', 'write'];
        $token->last_used_at = now();
        $token->expires_at = now()->addDays(30);
        $token->created_at = now();
        
        $request = Request::create('/');
        $resource = new ApiTokenResource($token);
        $data = $resource->toArray($request);
        
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('name', $data);
        $this->assertArrayHasKey('abilities', $data);
        $this->assertArrayHasKey('last_used_at', $data);
        $this->assertArrayHasKey('expires_at', $data);
        $this->assertArrayHasKey('created_at', $data);
        $this->assertArrayHasKey('is_expired', $data);
        
        $this->assertEquals(1, $data['id']);
        $this->assertEquals('Test Token', $data['name']);
        $this->assertEquals(['read', 'write'], $data['abilities']);
        $this->assertFalse($data['is_expired']);
    }

    public function test_api_token_resource_with_expired_token()
    {
        $token = $this->createPartialMock(ApiToken::class, ['isExpired']);
        $token->method('isExpired')->willReturn(true);
        $token->id = 1;
        $token->name = 'Expired Token';
        $token->abilities = ['read'];
        $token->expires_at = now()->subDays(1);
        $token->created_at = now()->subDays(10);
        
        $request = Request::create('/');
        $resource = new ApiTokenResource($token);
        $data = $resource->toArray($request);
        
        $this->assertTrue($data['is_expired']);
    }

    public function test_api_token_resource_timestamps_are_iso_format()
    {
        $now = now();
        $token = $this->createPartialMock(ApiToken::class, ['isExpired']);
        $token->method('isExpired')->willReturn(false);
        $token->id = 1;
        $token->name = 'Test Token';
        $token->abilities = ['read'];
        $token->last_used_at = $now;
        $token->expires_at = $now->copy()->addDays(30);
        $token->created_at = $now;
        
        $request = Request::create('/');
        $resource = new ApiTokenResource($token);
        $data = $resource->toArray($request);
        
        // Check timestamp format rather than exact value due to microsecond precision
        $this->assertMatchesRegularExpression('/\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/', $data['last_used_at']);
        $this->assertMatchesRegularExpression('/\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/', $data['expires_at']);
        $this->assertMatchesRegularExpression('/\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/', $data['created_at']);
    }

    public function test_api_token_resource_handles_null_timestamps()
    {
        $token = $this->createPartialMock(ApiToken::class, ['isExpired']);
        $token->method('isExpired')->willReturn(false);
        $token->id = 1;
        $token->name = 'Test Token';
        $token->abilities = ['read'];
        $token->last_used_at = null;
        $token->expires_at = null;
        $token->created_at = now();
        
        $request = Request::create('/');
        $resource = new ApiTokenResource($token);
        $data = $resource->toArray($request);
        
        $this->assertNull($data['last_used_at']);
        $this->assertNull($data['expires_at']);
        $this->assertNotNull($data['created_at']);
    }
}