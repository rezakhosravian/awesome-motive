<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\AuthTestRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTestRequestTest extends TestCase
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
        $request = new AuthTestRequest();
        $expectedRules = [];

        $this->assertEquals($expectedRules, $request->rules());
    }

    public function test_authorization_returns_true_for_authenticated_user()
    {
        $this->actingAs($this->user);
        
        $request = new AuthTestRequest();
        $request->setUserResolver(fn() => $this->user);

        $this->assertTrue($request->authorize());
    }

    public function test_authorization_returns_false_for_guest_user()
    {
        $request = new AuthTestRequest();
        $request->setUserResolver(fn() => null);

        $this->assertFalse($request->authorize());
    }

    public function test_validation_passes_with_no_data()
    {
        $this->actingAs($this->user);

        $data = [];

        $request = AuthTestRequest::create('/test', 'GET', $data);
        $request->setUserResolver(fn() => $this->user);

        $validator = validator($data, $request->rules(), $request->messages());

        $this->assertFalse($validator->fails());
    }

    public function test_validation_passes_with_any_data()
    {
        $this->actingAs($this->user);

        $data = [
            'random_field' => 'random_value',
            'another_field' => 123,
            'third_field' => true
        ];

        $request = AuthTestRequest::create('/test', 'GET', $data);
        $request->setUserResolver(fn() => $this->user);

        $validator = validator($data, $request->rules(), $request->messages());

        $this->assertFalse($validator->fails());
    }

    public function test_custom_attributes_are_empty()
    {
        $request = new AuthTestRequest();
        $expectedAttributes = [];

        $this->assertEquals($expectedAttributes, $request->attributes());
    }

    public function test_custom_messages_are_empty()
    {
        $request = new AuthTestRequest();
        $expectedMessages = [];

        $this->assertEquals($expectedMessages, $request->messages());
    }

    public function test_request_can_be_instantiated()
    {
        $request = new AuthTestRequest();
        
        $this->assertInstanceOf(AuthTestRequest::class, $request);
    }

    public function test_request_extends_form_request()
    {
        $request = new AuthTestRequest();
        
        $this->assertInstanceOf(\Illuminate\Foundation\Http\FormRequest::class, $request);
    }

    public function test_authorization_logic_is_simple()
    {
        // Test with authenticated user
        $request1 = new AuthTestRequest();
        $request1->setUserResolver(fn() => $this->user);
        $this->assertTrue($request1->authorize());

        // Test with null user
        $request2 = new AuthTestRequest();
        $request2->setUserResolver(fn() => null);
        $this->assertFalse($request2->authorize());

        // Test with different user
        $otherUser = User::factory()->create();
        $request3 = new AuthTestRequest();
        $request3->setUserResolver(fn() => $otherUser);
        $this->assertTrue($request3->authorize());
    }

    public function test_no_validation_errors_ever_occur()
    {
        $this->actingAs($this->user);

        // Try various types of data
        $testCases = [
            [],
            ['key' => 'value'],
            ['number' => 123],
            ['boolean' => true],
            ['array' => ['nested' => 'value']],
            ['null_value' => null],
            ['empty_string' => ''],
            ['special_chars' => '!@#$%^&*()'],
            ['unicode' => 'español français 中文'],
        ];

        foreach ($testCases as $data) {
            $request = AuthTestRequest::create('/test', 'GET', $data);
            $request->setUserResolver(fn() => $this->user);

            $validator = validator($data, $request->rules(), $request->messages());

            $this->assertFalse($validator->fails(), 
                'Validation should never fail for AuthTestRequest with data: ' . json_encode($data));
        }
    }
}
