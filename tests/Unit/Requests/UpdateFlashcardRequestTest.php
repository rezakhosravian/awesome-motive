<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\UpdateFlashcardRequest;
use App\Models\Deck;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class UpdateFlashcardRequestTest extends TestCase
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
        $request = new UpdateFlashcardRequest();
        
        $expectedRules = [
            'question' => 'required|string|max:1000',
            'answer' => 'required|string|max:1000',
        ];
        
        $this->assertEquals($expectedRules, $request->rules());
    }

    public function test_validation_passes_with_valid_data()
    {
        $request = new UpdateFlashcardRequest();
        $validator = Validator::make([
            'question' => 'What is updated Laravel?',
            'answer' => 'An updated PHP Framework'
        ], $request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_validation_fails_without_question()
    {
        $request = new UpdateFlashcardRequest();
        $validator = Validator::make([
            'answer' => 'An updated PHP Framework'
        ], $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('question', $validator->errors()->toArray());
    }

    public function test_validation_fails_without_answer()
    {
        $request = new UpdateFlashcardRequest();
        $validator = Validator::make([
            'question' => 'What is updated Laravel?'
        ], $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('answer', $validator->errors()->toArray());
    }

    public function test_validation_fails_with_empty_question()
    {
        $request = new UpdateFlashcardRequest();
        $validator = Validator::make([
            'question' => '',
            'answer' => 'An updated PHP Framework'
        ], $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('question', $validator->errors()->toArray());
    }

    public function test_validation_fails_with_empty_answer()
    {
        $request = new UpdateFlashcardRequest();
        $validator = Validator::make([
            'question' => 'What is updated Laravel?',
            'answer' => ''
        ], $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('answer', $validator->errors()->toArray());
    }

    public function test_validation_fails_with_question_too_long()
    {
        $request = new UpdateFlashcardRequest();
        $validator = Validator::make([
            'question' => str_repeat('a', 1001), // 1001 characters
            'answer' => 'An updated PHP Framework'
        ], $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('question', $validator->errors()->toArray());
    }

    public function test_validation_fails_with_answer_too_long()
    {
        $request = new UpdateFlashcardRequest();
        $validator = Validator::make([
            'question' => 'What is updated Laravel?',
            'answer' => str_repeat('a', 1001) // 1001 characters
        ], $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('answer', $validator->errors()->toArray());
    }

    public function test_validation_passes_with_max_length_question()
    {
        $request = new UpdateFlashcardRequest();
        $validator = Validator::make([
            'question' => str_repeat('a', 1000), // Exactly 1000 characters
            'answer' => 'An updated PHP Framework'
        ], $request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_validation_passes_with_max_length_answer()
    {
        $request = new UpdateFlashcardRequest();
        $validator = Validator::make([
            'question' => 'What is updated Laravel?',
            'answer' => str_repeat('a', 1000) // Exactly 1000 characters
        ], $request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_validation_fails_with_non_string_question()
    {
        $request = new UpdateFlashcardRequest();
        $validator = Validator::make([
            'question' => 123, // Not a string
            'answer' => 'An updated PHP Framework'
        ], $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('question', $validator->errors()->toArray());
    }

    public function test_validation_fails_with_non_string_answer()
    {
        $request = new UpdateFlashcardRequest();
        $validator = Validator::make([
            'question' => 'What is updated Laravel?',
            'answer' => 123 // Not a string
        ], $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('answer', $validator->errors()->toArray());
    }

    public function test_validation_passes_with_unicode_characters()
    {
        $request = new UpdateFlashcardRequest();
        $validator = Validator::make([
            'question' => 'لاراول چیست؟', // Persian text
            'answer' => 'یک فریمورک PHP'
        ], $request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_validation_passes_with_special_characters()
    {
        $request = new UpdateFlashcardRequest();
        $validator = Validator::make([
            'question' => 'What is $variable = "Laravel"?',
            'answer' => 'A PHP variable assignment with framework name!'
        ], $request->rules());

        $this->assertTrue($validator->passes());
    }

    // Custom Attributes Tests
    public function test_custom_attributes_are_correct()
    {
        $request = new UpdateFlashcardRequest();
        
        $expectedAttributes = [
            'question' => 'flashcard question',
            'answer' => 'flashcard answer',
        ];
        
        $this->assertEquals($expectedAttributes, $request->attributes());
    }

    // Custom Messages Tests
    public function test_custom_messages_are_correct()
    {
        $request = new UpdateFlashcardRequest();
        
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
        $request = new UpdateFlashcardRequest();
        $validator = Validator::make([
            'answer' => 'An updated PHP Framework'
        ], $request->rules(), $request->messages(), $request->attributes());

        $this->assertFalse($validator->passes());
        $this->assertContains(
            'Please provide a question for the flashcard.',
            $validator->errors()->get('question')
        );
    }

    public function test_custom_message_is_used_for_required_answer()
    {
        $request = new UpdateFlashcardRequest();
        $validator = Validator::make([
            'question' => 'What is updated Laravel?'
        ], $request->rules(), $request->messages(), $request->attributes());

        $this->assertFalse($validator->passes());
        $this->assertContains(
            'Please provide an answer for the flashcard.',
            $validator->errors()->get('answer')
        );
    }

    public function test_custom_message_is_used_for_question_max_length()
    {
        $request = new UpdateFlashcardRequest();
        $validator = Validator::make([
            'question' => str_repeat('a', 1001),
            'answer' => 'An updated PHP Framework'
        ], $request->rules(), $request->messages(), $request->attributes());

        $this->assertFalse($validator->passes());
        $this->assertContains(
            'The question cannot exceed 1000 characters.',
            $validator->errors()->get('question')
        );
    }

    public function test_custom_message_is_used_for_answer_max_length()
    {
        $request = new UpdateFlashcardRequest();
        $validator = Validator::make([
            'question' => 'What is updated Laravel?',
            'answer' => str_repeat('a', 1001)
        ], $request->rules(), $request->messages(), $request->attributes());

        $this->assertFalse($validator->passes());
        $this->assertContains(
            'The answer cannot exceed 1000 characters.',
            $validator->errors()->get('answer')
        );
    }

    // Edge Cases
    public function test_validation_passes_with_minimal_content()
    {
        $request = new UpdateFlashcardRequest();
        $validator = Validator::make([
            'question' => 'Q?',
            'answer' => 'A.'
        ], $request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_validation_passes_with_whitespace_trimmed()
    {
        $request = new UpdateFlashcardRequest();
        $validator = Validator::make([
            'question' => '  What is Laravel?  ',
            'answer' => '  A PHP Framework  '
        ], $request->rules());

        $this->assertTrue($validator->passes());
    }
}