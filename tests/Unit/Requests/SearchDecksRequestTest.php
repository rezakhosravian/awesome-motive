<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\SearchDecksRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchDecksRequestTest extends TestCase
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
        $request = new SearchDecksRequest();
        $expectedRules = [
            'q' => 'required|string|min:1|max:255',
            'per_page' => 'sometimes|integer|min:1|max:50',
            'page' => 'sometimes|integer|min:1'
        ];

        $this->assertEquals($expectedRules, $request->rules());
    }

    public function test_authorization_returns_true_for_authenticated_user()
    {
        $this->actingAs($this->user);

        $request = new SearchDecksRequest();
        $request->setUserResolver(fn() => $this->user);

        $this->assertTrue($request->authorize());
    }

    public function test_authorization_returns_false_for_guest_user()
    {
        $request = new SearchDecksRequest();
        $request->setUserResolver(fn() => null);

        $this->assertFalse($request->authorize());
    }

    public function test_validation_passes_with_valid_data()
    {
        $this->actingAs($this->user);

        $data = [
            'q' => 'spanish',
            'per_page' => 20,
            'page' => 1
        ];

        $request = SearchDecksRequest::create('/test', 'GET', $data);
        $request->setUserResolver(fn() => $this->user);

        $validator = validator($data, $request->rules(), $request->messages());

        $this->assertFalse($validator->fails());
    }

    public function test_validation_fails_without_query()
    {
        $this->actingAs($this->user);

        $data = [
            'per_page' => 20
        ];

        $request = SearchDecksRequest::create('/test', 'GET', $data);
        $request->setUserResolver(fn() => $this->user);

        $validator = validator($data, $request->rules(), $request->messages());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('q', $validator->errors()->toArray());
    }

    public function test_validation_fails_with_empty_query()
    {
        $this->actingAs($this->user);

        $data = [
            'q' => '',
            'per_page' => 20
        ];

        $request = SearchDecksRequest::create('/test', 'GET', $data);
        $request->setUserResolver(fn() => $this->user);

        $validator = validator($data, $request->rules(), $request->messages());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('q', $validator->errors()->toArray());
    }

    public function test_validation_fails_with_query_too_long()
    {
        $this->actingAs($this->user);

        $data = [
            'q' => str_repeat('a', 256), // 256 characters
            'per_page' => 20
        ];

        $request = SearchDecksRequest::create('/test', 'GET', $data);
        $request->setUserResolver(fn() => $this->user);

        $validator = validator($data, $request->rules(), $request->messages());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('q', $validator->errors()->toArray());
    }

    public function test_validation_passes_with_max_length_query()
    {
        $this->actingAs($this->user);

        $data = [
            'q' => str_repeat('a', 255), // 255 characters
            'per_page' => 20
        ];

        $request = SearchDecksRequest::create('/test', 'GET', $data);
        $request->setUserResolver(fn() => $this->user);

        $validator = validator($data, $request->rules(), $request->messages());

        $this->assertFalse($validator->fails());
    }

    public function test_validation_fails_with_invalid_per_page()
    {
        $this->actingAs($this->user);

        $invalidValues = [0, -1, 51, 'invalid'];

        foreach ($invalidValues as $value) {
            $data = [
                'q' => 'test',
                'per_page' => $value
            ];

            $request = SearchDecksRequest::create('/test', 'GET', $data);
            $request->setUserResolver(fn() => $this->user);

            $validator = validator($data, $request->rules(), $request->messages());

            $this->assertTrue(
                $validator->fails(),
                "Validation should fail for per_page value: {$value}"
            );
            $this->assertArrayHasKey('per_page', $validator->errors()->toArray());
        }
    }

    public function test_validation_passes_with_valid_per_page()
    {
        $this->actingAs($this->user);

        $validValues = [1, 15, 25, 50];

        foreach ($validValues as $value) {
            $data = [
                'q' => 'test',
                'per_page' => $value
            ];

            $request = SearchDecksRequest::create('/test', 'GET', $data);
            $request->setUserResolver(fn() => $this->user);

            $validator = validator($data, $request->rules(), $request->messages());

            $this->assertFalse(
                $validator->fails(),
                "Validation should pass for per_page value: {$value}"
            );
        }
    }

    public function test_validation_fails_with_invalid_page()
    {
        $this->actingAs($this->user);

        $invalidValues = [0, -1, 'invalid'];

        foreach ($invalidValues as $value) {
            $data = [
                'q' => 'test',
                'page' => $value
            ];

            $request = SearchDecksRequest::create('/test', 'GET', $data);
            $request->setUserResolver(fn() => $this->user);

            $validator = validator($data, $request->rules(), $request->messages());

            $this->assertTrue(
                $validator->fails(),
                "Validation should fail for page value: {$value}"
            );
            $this->assertArrayHasKey('page', $validator->errors()->toArray());
        }
    }

    public function test_validation_passes_without_optional_fields()
    {
        $this->actingAs($this->user);

        $data = [
            'q' => 'test'
        ];

        $request = SearchDecksRequest::create('/test', 'GET', $data);
        $request->setUserResolver(fn() => $this->user);

        $validator = validator($data, $request->rules(), $request->messages());

        $this->assertFalse($validator->fails());
    }

    public function test_custom_attributes_are_correct()
    {
        $request = new SearchDecksRequest();
        $expectedAttributes = [
            'q' => 'search query',
            'per_page' => 'items per page',
            'page' => 'page number',
        ];

        $this->assertEquals($expectedAttributes, $request->attributes());
    }

    public function test_custom_messages_are_correct()
    {
        $request = new SearchDecksRequest();
        $expectedMessages = [
            'q.required' => __('validation.search.query_required'),
            'q.string' => __('validation.search.query_string'),
            'q.min' => __('validation.search.query_min'),
            'q.max' => __('validation.search.query_max'),
            'per_page.integer' => __('validation.search.per_page_integer'),
            'per_page.min' => __('validation.search.per_page_min'),
            'per_page.max' => __('validation.search.per_page_max'),
            'page.integer' => __('validation.search.page_integer'),
            'page.min' => __('validation.search.page_min'),
        ];

        $this->assertEquals($expectedMessages, $request->messages());
    }

    public function test_prepare_for_validation_sets_default_per_page()
    {
        $this->actingAs($this->user);

        // Create request without per_page
        $data = ['q' => 'test'];

        $request = SearchDecksRequest::create('/test', 'GET', $data);
        $request->setUserResolver(fn() => $this->user);

        // Manually call prepareForValidation since it's protected
        $reflection = new \ReflectionClass($request);
        $method = $reflection->getMethod('prepareForValidation');
        $method->setAccessible(true);
        $method->invoke($request);

        $this->assertEquals(15, $request->input('per_page'));
    }

    public function test_prepare_for_validation_preserves_existing_per_page()
    {
        $this->actingAs($this->user);

        $data = [
            'q' => 'test',
            'per_page' => 25
        ];

        $request = SearchDecksRequest::create('/test', 'GET', $data);
        $request->setUserResolver(fn() => $this->user);

        // Manually call prepareForValidation
        $reflection = new \ReflectionClass($request);
        $method = $reflection->getMethod('prepareForValidation');
        $method->setAccessible(true);
        $method->invoke($request);

        $this->assertEquals(25, $request->input('per_page'));
    }

    public function test_prepare_for_validation_fixes_invalid_page()
    {
        $this->actingAs($this->user);

        // Create request with invalid page
        $data = [
            'q' => 'test',
            'page' => 0
        ];

        $request = SearchDecksRequest::create('/test', 'GET', $data);
        $request->setUserResolver(fn() => $this->user);

        // Manually call prepareForValidation
        $reflection = new \ReflectionClass($request);
        $method = $reflection->getMethod('prepareForValidation');
        $method->setAccessible(true);
        $method->invoke($request);

        $this->assertEquals(1, $request->input('page'));
    }

    public function test_helper_methods()
    {
        $this->actingAs($this->user);

        $data = [
            'q' => 'spanish vocabulary',
            'per_page' => 20,
            'page' => 2
        ];

        $request = SearchDecksRequest::create('/test', 'GET', $data);
        $request->setUserResolver(fn() => $this->user);

        // Manually set the validated data
        $reflection = new \ReflectionClass($request);
        $property = $reflection->getProperty('validator');
        $property->setAccessible(true);
        $validator = validator($data, $request->rules());
        $property->setValue($request, $validator);

        $this->assertEquals('spanish vocabulary', $request->getQuery());
        $this->assertEquals(20, $request->getPerPage());
        $this->assertEquals(2, $request->getPage());
    }

    public function test_helper_methods_with_defaults()
    {
        $this->actingAs($this->user);

        $data = [
            'q' => 'test'
            // No per_page or page provided
        ];

        $request = SearchDecksRequest::create('/test', 'GET', $data);
        $request->setUserResolver(fn() => $this->user);

        // Call prepareForValidation to set defaults
        $reflection = new \ReflectionClass($request);
        $method = $reflection->getMethod('prepareForValidation');
        $method->setAccessible(true);
        $method->invoke($request);

        // Manually set the validated data with defaults
        $dataWithDefaults = array_merge($data, ['per_page' => 15]);
        $property = $reflection->getProperty('validator');
        $property->setAccessible(true);
        $validator = validator($dataWithDefaults, $request->rules());
        $property->setValue($request, $validator);

        $this->assertEquals('test', $request->getQuery());
        $this->assertEquals(15, $request->getPerPage()); // default
        $this->assertEquals(1, $request->getPage()); // default
    }

    public function test_validation_passes_with_unicode_query()
    {
        $this->actingAs($this->user);

        $data = [
            'q' => 'español français 中文'
        ];

        $request = SearchDecksRequest::create('/test', 'GET', $data);
        $request->setUserResolver(fn() => $this->user);

        $validator = validator($data, $request->rules(), $request->messages());

        $this->assertFalse($validator->fails());
    }
}
