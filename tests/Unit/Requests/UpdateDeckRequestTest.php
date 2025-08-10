<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\UpdateDeckRequest;
use App\Models\Deck;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class UpdateDeckRequestTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $otherUser;
    protected Deck $deck;
    protected Deck $otherUserDeck;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->otherUser = User::factory()->create();
        
        $this->deck = Deck::factory()->create([
            'user_id' => $this->user->id
        ]);
        
        $this->otherUserDeck = Deck::factory()->create([
            'user_id' => $this->otherUser->id
        ]);
    }

    // Validation Rules Tests
    public function test_validation_rules_are_correct()
    {
        $request = new UpdateDeckRequest();
        
        $expectedRules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'is_public' => 'boolean',
        ];
        
        $this->assertEquals($expectedRules, $request->rules());
    }

    public function test_validation_passes_with_valid_data()
    {
        $request = new UpdateDeckRequest();
        $validator = Validator::make([
            'name' => 'Updated Test Deck',
            'description' => 'Updated description for testing',
            'is_public' => true
        ], $request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_validation_passes_with_minimal_data()
    {
        $request = new UpdateDeckRequest();
        $validator = Validator::make([
            'name' => 'Updated Test Deck'
        ], $request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_validation_fails_without_name()
    {
        $request = new UpdateDeckRequest();
        $validator = Validator::make([
            'description' => 'Updated description for testing',
            'is_public' => true
        ], $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    public function test_validation_fails_with_empty_name()
    {
        $request = new UpdateDeckRequest();
        $validator = Validator::make([
            'name' => '',
            'description' => 'Updated description for testing',
            'is_public' => true
        ], $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    public function test_validation_fails_with_name_too_long()
    {
        $request = new UpdateDeckRequest();
        $validator = Validator::make([
            'name' => str_repeat('a', 256), // 256 characters (exceeds 255 limit)
            'description' => 'Updated description for testing',
            'is_public' => true
        ], $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    public function test_validation_passes_with_max_length_name()
    {
        $request = new UpdateDeckRequest();
        $validator = Validator::make([
            'name' => str_repeat('a', 255), // Exactly 255 characters
            'description' => 'Updated description for testing',
            'is_public' => true
        ], $request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_validation_fails_with_non_string_name()
    {
        $request = new UpdateDeckRequest();
        $validator = Validator::make([
            'name' => 123, // Not a string
            'description' => 'Updated description for testing',
            'is_public' => true
        ], $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    public function test_validation_passes_with_null_description()
    {
        $request = new UpdateDeckRequest();
        $validator = Validator::make([
            'name' => 'Updated Test Deck',
            'description' => null,
            'is_public' => true
        ], $request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_validation_passes_with_empty_description()
    {
        $request = new UpdateDeckRequest();
        $validator = Validator::make([
            'name' => 'Updated Test Deck',
            'description' => '',
            'is_public' => true
        ], $request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_validation_fails_with_description_too_long()
    {
        $request = new UpdateDeckRequest();
        $validator = Validator::make([
            'name' => 'Updated Test Deck',
            'description' => str_repeat('a', 1001), // 1001 characters (exceeds 1000 limit)
            'is_public' => true
        ], $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('description', $validator->errors()->toArray());
    }

    public function test_validation_passes_with_max_length_description()
    {
        $request = new UpdateDeckRequest();
        $validator = Validator::make([
            'name' => 'Updated Test Deck',
            'description' => str_repeat('a', 1000), // Exactly 1000 characters
            'is_public' => true
        ], $request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_validation_fails_with_non_string_description()
    {
        $request = new UpdateDeckRequest();
        $validator = Validator::make([
            'name' => 'Updated Test Deck',
            'description' => 123, // Not a string
            'is_public' => true
        ], $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('description', $validator->errors()->toArray());
    }

    public function test_validation_passes_with_boolean_true_is_public()
    {
        $request = new UpdateDeckRequest();
        $validator = Validator::make([
            'name' => 'Updated Test Deck',
            'description' => 'Updated description for testing',
            'is_public' => true
        ], $request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_validation_passes_with_boolean_false_is_public()
    {
        $request = new UpdateDeckRequest();
        $validator = Validator::make([
            'name' => 'Updated Test Deck',
            'description' => 'Updated description for testing',
            'is_public' => false
        ], $request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_validation_passes_with_string_boolean_is_public()
    {
        $request = new UpdateDeckRequest();
        $validator = Validator::make([
            'name' => 'Updated Test Deck',
            'description' => 'Updated description for testing',
            'is_public' => '1'
        ], $request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_validation_passes_with_integer_boolean_is_public()
    {
        $request = new UpdateDeckRequest();
        $validator = Validator::make([
            'name' => 'Updated Test Deck',
            'description' => 'Updated description for testing',
            'is_public' => 0
        ], $request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_validation_fails_with_invalid_boolean_is_public()
    {
        $request = new UpdateDeckRequest();
        $validator = Validator::make([
            'name' => 'Updated Test Deck',
            'description' => 'Updated description for testing',
            'is_public' => 'invalid'
        ], $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('is_public', $validator->errors()->toArray());
    }

    public function test_validation_passes_without_is_public()
    {
        $request = new UpdateDeckRequest();
        $validator = Validator::make([
            'name' => 'Updated Test Deck',
            'description' => 'Updated description for testing'
        ], $request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_validation_passes_with_unicode_characters()
    {
        $request = new UpdateDeckRequest();
        $validator = Validator::make([
            'name' => 'دیتابیس لاراول', // Persian text
            'description' => 'توضیحات فارسی برای تست',
            'is_public' => true
        ], $request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_validation_passes_with_special_characters()
    {
        $request = new UpdateDeckRequest();
        $validator = Validator::make([
            'name' => 'Deck #1 - "Laravel" & PHP!',
            'description' => 'A deck with special chars: @#$%^&*()[]{}',
            'is_public' => true
        ], $request->rules());

        $this->assertTrue($validator->passes());
    }

    // Custom Attributes Tests
    public function test_custom_attributes_are_correct()
    {
        $request = new UpdateDeckRequest();
        
        $expectedAttributes = [
            'name' => 'deck name',
            'description' => 'deck description',
            'is_public' => 'visibility setting',
        ];
        
        $this->assertEquals($expectedAttributes, $request->attributes());
    }

    // Prepare For Validation Tests (testing boolean conversion through feature tests)

    // Edge Cases
    public function test_validation_passes_with_minimal_content()
    {
        $request = new UpdateDeckRequest();
        $validator = Validator::make([
            'name' => 'D'
        ], $request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_validation_passes_with_whitespace_in_fields()
    {
        $request = new UpdateDeckRequest();
        $validator = Validator::make([
            'name' => '  Updated Test Deck  ',
            'description' => '  Updated description with spaces  ',
            'is_public' => true
        ], $request->rules());

        $this->assertTrue($validator->passes());
    }
}