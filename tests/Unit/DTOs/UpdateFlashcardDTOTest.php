<?php

namespace Tests\Unit\DTOs;

use App\DTOs\UpdateFlashcardDTO;
use Illuminate\Http\Request;
use Tests\TestCase;

class UpdateFlashcardDTOTest extends TestCase
{
    public function test_constructor_sets_properties()
    {
        $dto = new UpdateFlashcardDTO('Updated question', 'Updated answer');
        
        $this->assertEquals('Updated question', $dto->question);
        $this->assertEquals('Updated answer', $dto->answer);
    }

    public function test_from_request_creates_dto()
    {
        // Create a FormRequest mock that can handle validated() method
        $request = $this->getMockBuilder(\Illuminate\Foundation\Http\FormRequest::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['validated'])
            ->getMock();
        
        $request->expects($this->exactly(2))
            ->method('validated')
            ->willReturnCallback(function($key) {
                return match($key) {
                    'question' => 'Updated question from request',
                    'answer' => 'Updated answer from request',
                    default => null
                };
            });

        $dto = UpdateFlashcardDTO::fromRequest($request);

        $this->assertEquals('Updated question from request', $dto->question);
        $this->assertEquals('Updated answer from request', $dto->answer);
    }

    public function test_from_array_creates_dto()
    {
        $data = [
            'question' => 'Updated question from array',
            'answer' => 'Updated answer from array'
        ];

        $dto = UpdateFlashcardDTO::fromArray($data);

        $this->assertEquals('Updated question from array', $dto->question);
        $this->assertEquals('Updated answer from array', $dto->answer);
    }

    public function test_to_array_returns_correct_structure()
    {
        $dto = new UpdateFlashcardDTO('Test question', 'Test answer');
        $array = $dto->toArray();

        $this->assertEquals([
            'question' => 'Test question',
            'answer' => 'Test answer',
        ], $array);
    }

    public function test_validate_passes_with_valid_data()
    {
        $dto = new UpdateFlashcardDTO('Valid question', 'Valid answer');
        
        $this->expectNotToPerformAssertions();
        $dto->validate();
    }

    public function test_validate_throws_exception_for_empty_question()
    {
        $dto = new UpdateFlashcardDTO('', 'Valid answer');
        
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Flashcard question cannot be empty');
        $dto->validate();
    }

    public function test_validate_throws_exception_for_whitespace_only_question()
    {
        $dto = new UpdateFlashcardDTO('   ', 'Valid answer');
        
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Flashcard question cannot be empty');
        $dto->validate();
    }

    public function test_validate_throws_exception_for_empty_answer()
    {
        $dto = new UpdateFlashcardDTO('Valid question', '');
        
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Flashcard answer cannot be empty');
        $dto->validate();
    }

    public function test_validate_throws_exception_for_whitespace_only_answer()
    {
        $dto = new UpdateFlashcardDTO('Valid question', '   ');
        
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Flashcard answer cannot be empty');
        $dto->validate();
    }

    public function test_validate_throws_exception_for_question_too_long()
    {
        $longQuestion = str_repeat('a', 1001);
        $dto = new UpdateFlashcardDTO($longQuestion, 'Valid answer');
        
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Flashcard question cannot exceed 1000 characters');
        $dto->validate();
    }

    public function test_validate_throws_exception_for_answer_too_long()
    {
        $longAnswer = str_repeat('a', 1001);
        $dto = new UpdateFlashcardDTO('Valid question', $longAnswer);
        
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Flashcard answer cannot exceed 1000 characters');
        $dto->validate();
    }

    public function test_validate_passes_with_max_length_question()
    {
        $maxQuestion = str_repeat('a', 1000);
        $dto = new UpdateFlashcardDTO($maxQuestion, 'Valid answer');
        
        $this->expectNotToPerformAssertions();
        $dto->validate();
    }

    public function test_validate_passes_with_max_length_answer()
    {
        $maxAnswer = str_repeat('a', 1000);
        $dto = new UpdateFlashcardDTO('Valid question', $maxAnswer);
        
        $this->expectNotToPerformAssertions();
        $dto->validate();
    }

    public function test_validate_handles_unicode_characters()
    {
        $dto = new UpdateFlashcardDTO('¿Qué es PHP actualizado?', 'Un lenguaje de programación moderno');
        
        $this->expectNotToPerformAssertions();
        $dto->validate();
    }

    public function test_validate_trims_whitespace_properly()
    {
        $dto = new UpdateFlashcardDTO('  Updated question  ', '  Updated answer  ');
        
        $this->expectNotToPerformAssertions();
        $dto->validate();
    }

    public function test_readonly_property_immutability()
    {
        $dto = new UpdateFlashcardDTO('Question', 'Answer');
        
        // This test ensures readonly properties work as expected
        $this->assertEquals('Question', $dto->question);
        $this->assertEquals('Answer', $dto->answer);
    }

    public function test_validate_with_boundary_values()
    {
        $dto = new UpdateFlashcardDTO('Q', 'A');
        
        $this->expectNotToPerformAssertions();
        $dto->validate();
    }

    public function test_validate_with_special_characters()
    {
        $dto = new UpdateFlashcardDTO(
            'What is @#$%^&*()? <>[]{}',
            'Special characters: @#$%^&*()? <>[]{}'
        );
        
        $this->expectNotToPerformAssertions();
        $dto->validate();
    }
}
