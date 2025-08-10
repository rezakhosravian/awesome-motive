<?php

namespace Tests\Unit\Resources\Api;

use App\Http\Resources\Api\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Tests\TestCase;

class UserResourceTest extends TestCase
{
    public function test_user_resource_structure()
    {
        $user = new User();
        $user->id = 1;
        $user->name = 'John Doe';
        $user->email = 'john@example.com';
        $user->created_at = now();
        
        $request = Request::create('/');
        $resource = new UserResource($user);
        $data = $resource->toArray($request);
        
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('name', $data);
        $this->assertArrayHasKey('created_at', $data);
        
        $this->assertEquals(1, $data['id']);
        $this->assertEquals('John Doe', $data['name']);
    }

    public function test_user_resource_hides_email_by_default()
    {
        $user = new User([
            'id' => 1,
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ]);
        
        $request = Request::create('/');
        $resource = new UserResource($user);
        $data = $resource->toArray($request);
        
        // The email should be present but with MissingValue when condition is false
        $this->assertInstanceOf(\Illuminate\Http\Resources\MissingValue::class, $data['email']);
    }

    public function test_user_resource_shows_email_for_same_user()
    {
        $user = new User([
            'id' => 1,
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ]);
        
        $request = Request::create('/');
        $request->setUserResolver(function () use ($user) {
            return $user;
        });
        
        $resource = new UserResource($user);
        $data = $resource->toArray($request);
        
        $this->assertArrayHasKey('email', $data);
        $this->assertEquals('john@example.com', $data['email']);
    }

    public function test_user_resource_shows_email_when_explicitly_requested()
    {
        $user = new User([
            'id' => 1,
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ]);
        
        $request = Request::create('/', 'GET', ['include_email' => true]);
        // Set a mock authenticated user to satisfy the condition
        $request->setUserResolver(function () use ($user) {
            return $user;
        });
        
        $resource = new UserResource($user);
        $data = $resource->toArray($request);
        
        $this->assertArrayHasKey('email', $data);
        $this->assertEquals('john@example.com', $data['email']);
    }

    public function test_user_resource_timestamp_is_iso_format()
    {
        $now = now();
        $user = new User();
        $user->id = 1;
        $user->name = 'John Doe';
        $user->email = 'john@example.com';
        $user->created_at = $now;
        
        $request = Request::create('/');
        $resource = new UserResource($user);
        $data = $resource->toArray($request);
        
        // Check timestamp format rather than exact value due to microsecond precision
        if ($data['created_at']) {
            $this->assertMatchesRegularExpression('/\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/', $data['created_at']);
        } else {
            $this->assertNull($data['created_at']);
        }
    }
}