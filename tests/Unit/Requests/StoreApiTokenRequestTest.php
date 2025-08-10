<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\StoreApiTokenRequest;
use App\Models\ApiToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreApiTokenRequestTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_validation_rules_are_correct()
    {
        $request = new StoreApiTokenRequest();
        $expectedRules = [
            'name' => 'required|string|max:255',
            'abilities' => 'sometimes|array',
            'abilities.*' => 'string|in:read,write,delete,admin',
            'expires_at' => 'sometimes|date|after:today'
        ];

        $this->assertEquals($expectedRules, $request->rules());
    }

    public function test_authorization_returns_true_for_authenticated_user()
    {
        $this->actingAs($this->user);
        
        $request = new StoreApiTokenRequest();
        $request->setUserResolver(fn() => $this->user);

        $this->assertTrue($request->authorize());
    }

    public function test_authorization_returns_false_for_guest_user()
    {
        $request = new StoreApiTokenRequest();
        $request->setUserResolver(fn() => null);

        $this->assertFalse($request->authorize());
    }

    public function test_validation_passes_with_valid_data()
    {
        $this->actingAs($this->user);

        $data = [
            'name' => 'Test Token',
            'abilities' => ['read', 'write'],
            'expires_at' => now()->addDays(30)->format('Y-m-d')
        ];

        $request = StoreApiTokenRequest::create('/test', 'POST', $data);
        $request->setUserResolver(fn() => $this->user);

        $validator = validator($data, $request->rules(), $request->messages());

        $this->assertFalse($validator->fails());
    }

    public function test_validation_fails_without_name()
    {
        $this->actingAs($this->user);

        $data = [
            'abilities' => ['read']
        ];

        $request = StoreApiTokenRequest::create('/test', 'POST', $data);
        $request->setUserResolver(fn() => $this->user);

        $validator = validator($data, $request->rules(), $request->messages());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    public function test_validation_fails_with_empty_name()
    {
        $this->actingAs($this->user);

        $data = [
            'name' => '',
            'abilities' => ['read']
        ];

        $request = StoreApiTokenRequest::create('/test', 'POST', $data);
        $request->setUserResolver(fn() => $this->user);

        $validator = validator($data, $request->rules(), $request->messages());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    public function test_validation_fails_with_name_too_long()
    {
        $this->actingAs($this->user);

        $data = [
            'name' => str_repeat('a', 256), // 256 characters
            'abilities' => ['read']
        ];

        $request = StoreApiTokenRequest::create('/test', 'POST', $data);
        $request->setUserResolver(fn() => $this->user);

        $validator = validator($data, $request->rules(), $request->messages());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    public function test_validation_passes_with_max_length_name()
    {
        $this->actingAs($this->user);

        $data = [
            'name' => str_repeat('a', 255), // 255 characters
            'abilities' => ['read']
        ];

        $request = StoreApiTokenRequest::create('/test', 'POST', $data);
        $request->setUserResolver(fn() => $this->user);

        $validator = validator($data, $request->rules(), $request->messages());

        $this->assertFalse($validator->fails());
    }

    public function test_validation_fails_with_invalid_abilities()
    {
        $this->actingAs($this->user);

        $data = [
            'name' => 'Test Token',
            'abilities' => ['invalid_ability']
        ];

        $request = StoreApiTokenRequest::create('/test', 'POST', $data);
        $request->setUserResolver(fn() => $this->user);

        $validator = validator($data, $request->rules(), $request->messages());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('abilities.0', $validator->errors()->toArray());
    }

    public function test_validation_passes_with_valid_abilities()
    {
        $this->actingAs($this->user);

        $validAbilities = [
            ['read'],
            ['write'],
            ['delete'],
            ['admin'],
            ['read', 'write'],
            ['read', 'write', 'delete', 'admin']
        ];

        foreach ($validAbilities as $abilities) {
            $data = [
                'name' => 'Test Token',
                'abilities' => $abilities
            ];

            $request = StoreApiTokenRequest::create('/test', 'POST', $data);
            $request->setUserResolver(fn() => $this->user);

            $validator = validator($data, $request->rules(), $request->messages());

            $this->assertFalse($validator->fails(), 
                "Validation failed for abilities: " . implode(', ', $abilities));
        }
    }

    public function test_validation_fails_with_past_expiration_date()
    {
        $this->actingAs($this->user);

        $data = [
            'name' => 'Test Token',
            'abilities' => ['read'],
            'expires_at' => now()->subDay()->format('Y-m-d')
        ];

        $request = StoreApiTokenRequest::create('/test', 'POST', $data);
        $request->setUserResolver(fn() => $this->user);

        $validator = validator($data, $request->rules(), $request->messages());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('expires_at', $validator->errors()->toArray());
    }

    public function test_validation_passes_with_future_expiration_date()
    {
        $this->actingAs($this->user);

        $data = [
            'name' => 'Test Token',
            'abilities' => ['read'],
            'expires_at' => now()->addDays(30)->format('Y-m-d')
        ];

        $request = StoreApiTokenRequest::create('/test', 'POST', $data);
        $request->setUserResolver(fn() => $this->user);

        $validator = validator($data, $request->rules(), $request->messages());

        $this->assertFalse($validator->fails());
    }

    public function test_validation_passes_without_optional_fields()
    {
        $this->actingAs($this->user);

        $data = [
            'name' => 'Test Token'
        ];

        $request = StoreApiTokenRequest::create('/test', 'POST', $data);
        $request->setUserResolver(fn() => $this->user);

        $validator = validator($data, $request->rules(), $request->messages());

        $this->assertFalse($validator->fails());
    }

    public function test_custom_attributes_are_correct()
    {
        $request = new StoreApiTokenRequest();
        $expectedAttributes = [
            'name' => 'token name',
            'abilities' => 'token abilities',
            'abilities.*' => 'ability',
            'expires_at' => 'expiration date',
        ];

        $this->assertEquals($expectedAttributes, $request->attributes());
    }

    public function test_custom_messages_are_correct()
    {
        $request = new StoreApiTokenRequest();
        $expectedMessages = [
            'name.required' => __('validation.api_token.name_required'),
            'name.string' => __('validation.api_token.name_string'),
            'name.max' => __('validation.api_token.name_max'),
            'abilities.array' => __('validation.api_token.abilities_array'),
            'abilities.*.string' => __('validation.api_token.ability_string'),
            'abilities.*.in' => __('validation.api_token.ability_invalid'),
            'expires_at.date' => __('validation.api_token.expires_at_date'),
            'expires_at.after' => __('validation.api_token.expires_at_future'),
        ];

        $this->assertEquals($expectedMessages, $request->messages());
    }

    public function test_prepare_for_validation_sets_default_abilities()
    {
        $this->actingAs($this->user);

        $data = ['name' => 'Test Token'];
        
        $request = StoreApiTokenRequest::create('/test', 'POST', $data);
        $request->setUserResolver(fn() => $this->user);

        $reflection = new \ReflectionClass($request);
        $method = $reflection->getMethod('prepareForValidation');
        $method->setAccessible(true);
        $method->invoke($request);

        $this->assertEquals(['read', 'write'], $request->input('abilities'));
    }

    public function test_prepare_for_validation_preserves_existing_abilities()
    {
        $this->actingAs($this->user);

        $customAbilities = ['read', 'admin'];
        $data = [
            'name' => 'Test Token',
            'abilities' => $customAbilities
        ];
        
        $request = StoreApiTokenRequest::create('/test', 'POST', $data);
        $request->setUserResolver(fn() => $this->user);

        $reflection = new \ReflectionClass($request);
        $method = $reflection->getMethod('prepareForValidation');
        $method->setAccessible(true);
        $method->invoke($request);

        $this->assertEquals($customAbilities, $request->input('abilities'));
    }

    public function test_validation_fails_with_non_string_name()
    {
        $this->actingAs($this->user);

        $data = [
            'name' => 123,
            'abilities' => ['read']
        ];

        $request = StoreApiTokenRequest::create('/test', 'POST', $data);
        $request->setUserResolver(fn() => $this->user);

        $validator = validator($data, $request->rules(), $request->messages());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    public function test_validation_fails_with_non_array_abilities()
    {
        $this->actingAs($this->user);

        $data = [
            'name' => 'Test Token',
            'abilities' => 'read,write'
        ];

        $request = StoreApiTokenRequest::create('/test', 'POST', $data);
        $request->setUserResolver(fn() => $this->user);

        $validator = validator($data, $request->rules(), $request->messages());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('abilities', $validator->errors()->toArray());
    }

    public function test_validation_fails_with_invalid_date_format()
    {
        $this->actingAs($this->user);

        $data = [
            'name' => 'Test Token',
            'abilities' => ['read'],
            'expires_at' => 'invalid-date'
        ];

        $request = StoreApiTokenRequest::create('/test', 'POST', $data);
        $request->setUserResolver(fn() => $this->user);

        $validator = validator($data, $request->rules(), $request->messages());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('expires_at', $validator->errors()->toArray());
    }
}
