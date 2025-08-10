<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\StoreFlashcardRequest;
use App\Models\Deck;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreFlashcardRequestTest extends TestCase
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

    // Authorization Tests (tested through feature tests for better route mocking)

    // Validation Rules Tests
    public function test_validation_rules_are_correct()
    {
        $request = new StoreFlashcardRequest();
        
        $expectedRules = [
            'question' => 'required|string|max:1000',
            'answer' => 'required|string|max:1000',
        ];
        
        $this->assertEquals($expectedRules, $request->rules());
    }

    public function test_validation_passes_with_valid_data()
    {
        $request = new StoreFlashcardRequest();
        $validator = Validator::make([
            'question' => 'What is Laravel?',
            'answer' => 'A PHP Framework'
        ], $request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_validation_fails_without_question()
    {
        $request = new StoreFlashcardRequest();
        $validator = Validator::make([
            'answer' => 'A PHP Framework'
        ], $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('question', $validator->errors()->toArray());
    }

    public function test_validation_fails_without_answer()
    {
        $request = new StoreFlashcardRequest();
        $validator = Validator::make([
            'question' => 'What is Laravel?'
        ], $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('answer', $validator->errors()->toArray());
    }

    public function test_validation_fails_with_empty_question()
    {
        $request = new StoreFlashcardRequest();
        $validator = Validator::make([
            'question' => '',
            'answer' => 'A PHP Framework'
        ], $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('question', $validator->errors()->toArray());
    }

    public function test_validation_fails_with_empty_answer()
    {
        $request = new StoreFlashcardRequest();
        $validator = Validator::make([
            'question' => 'What is Laravel?',
            'answer' => ''
        ], $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('answer', $validator->errors()->toArray());
    }

    public function test_validation_fails_with_question_too_long()
    {
        $request = new StoreFlashcardRequest();
        $validator = Validator::make([
            'question' => str_repeat('a', 1001), // 1001 characters
            'answer' => 'A PHP Framework'
        ], $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('question', $validator->errors()->toArray());
    }

    public function test_validation_fails_with_answer_too_long()
    {
        $request = new StoreFlashcardRequest();
        $validator = Validator::make([
            'question' => 'What is Laravel?',
            'answer' => str_repeat('a', 1001) // 1001 characters
        ], $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('answer', $validator->errors()->toArray());
    }

    public function test_validation_passes_with_max_length_question()
    {
        $request = new StoreFlashcardRequest();
        $validator = Validator::make([
            'question' => str_repeat('a', 1000), // Exactly 1000 characters
            'answer' => 'A PHP Framework'
        ], $request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_validation_passes_with_max_length_answer()
    {
        $request = new StoreFlashcardRequest();
        $validator = Validator::make([
            'question' => 'What is Laravel?',
            'answer' => str_repeat('a', 1000) // Exactly 1000 characters
        ], $request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_validation_fails_with_non_string_question()
    {
        $request = new StoreFlashcardRequest();
        $validator = Validator::make([
            'question' => 123, // Not a string
            'answer' => 'A PHP Framework'
        ], $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('question', $validator->errors()->toArray());
    }

    public function test_validation_fails_with_non_string_answer()
    {
        $request = new StoreFlashcardRequest();
        $validator = Validator::make([
            'question' => 'What is Laravel?',
            'answer' => 123 // Not a string
        ], $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('answer', $validator->errors()->toArray());
    }

    // Custom Attributes Tests
    public function test_custom_attributes_are_correct()
    {
        $request = new StoreFlashcardRequest();
        
        $expectedAttributes = [
            'question' => 'flashcard question',
            'answer' => 'flashcard answer',
        ];
        
        $this->assertEquals($expectedAttributes, $request->attributes());
    }

    // Custom Messages Tests
    public function test_custom_messages_are_correct()
    {
        $request = new StoreFlashcardRequest();
        
        $expectedMessages = [
            'question.required' => 'Please provide a question for the flashcard.',
            'answer.required' => 'Please provide an answer for the flashcard.',
            'question.max' => 'The question cannot exceed 1000 characters.',
            'answer.max' => 'The answer cannot exceed 1000 characters.',
        ];
        
        $this->assertEquals($expectedMessages, $request->messages());
    }

    public function test_custom_message_is_used_for_required_question()
    {
        $request = new StoreFlashcardRequest();
        $validator = Validator::make([
            'answer' => 'A PHP Framework'
        ], $request->rules(), $request->messages(), $request->attributes());

        $this->assertFalse($validator->passes());
        $this->assertContains(
            'Please provide a question for the flashcard.',
            $validator->errors()->get('question')
        );
    }

    public function test_custom_message_is_used_for_required_answer()
    {
        $request = new StoreFlashcardRequest();
        $validator = Validator::make([
            'question' => 'What is Laravel?'
        ], $request->rules(), $request->messages(), $request->attributes());

        $this->assertFalse($validator->passes());
        $this->assertContains(
            'Please provide an answer for the flashcard.',
            $validator->errors()->get('answer')
        );
    }

    public function test_custom_message_is_used_for_question_max_length()
    {
        $request = new StoreFlashcardRequest();
        $validator = Validator::make([
            'question' => str_repeat('a', 1001),
            'answer' => 'A PHP Framework'
        ], $request->rules(), $request->messages(), $request->attributes());

        $this->assertFalse($validator->passes());
        $this->assertContains(
            'The question cannot exceed 1000 characters.',
            $validator->errors()->get('question')
        );
    }

    public function test_custom_message_is_used_for_answer_max_length()
    {
        $request = new StoreFlashcardRequest();
        $validator = Validator::make([
            'question' => 'What is Laravel?',
            'answer' => str_repeat('a', 1001)
        ], $request->rules(), $request->messages(), $request->attributes());

        $this->assertFalse($validator->passes());
        $this->assertContains(
            'The answer cannot exceed 1000 characters.',
            $validator->errors()->get('answer')
        );
    }
}