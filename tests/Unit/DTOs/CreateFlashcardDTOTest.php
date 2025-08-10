<?php

namespace Tests\Unit\DTOs;

use App\DTOs\CreateFlashcardDTO;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Tests\TestCase;

class CreateFlashcardDTOTest extends TestCase
{
    public function test_constructor_sets_properties()
    {
        $dto = new CreateFlashcardDTO('What is PHP?', 'A programming language', 1);
        
        $this->assertEquals('What is PHP?', $dto->question);
        $this->assertEquals('A programming language', $dto->answer);
        $this->assertEquals(1, $dto->deckId);
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
                    'question' => 'What is Laravel?',
                    'answer' => 'A PHP framework',
                    default => null
                };
            });

        $dto = CreateFlashcardDTO::fromRequest($request, 2);

        $this->assertEquals('What is Laravel?', $dto->question);
        $this->assertEquals('A PHP framework', $dto->answer);
        $this->assertEquals(2, $dto->deckId);
    }

    public function test_from_array_creates_dto()
    {
        $data = [
            'question' => 'What is testing?',
            'answer' => 'Verification of code behavior'
        ];

        $dto = CreateFlashcardDTO::fromArray($data, 3);

        $this->assertEquals('What is testing?', $dto->question);
        $this->assertEquals('Verification of code behavior', $dto->answer);
        $this->assertEquals(3, $dto->deckId);
    }

    public function test_to_array_returns_correct_structure()
    {
        $dto = new CreateFlashcardDTO('Question', 'Answer', 4);
        $array = $dto->toArray();

        $this->assertEquals([
            'question' => 'Question',
            'answer' => 'Answer',
            'deck_id' => 4,
        ], $array);
    }

    public function test_validate_passes_with_valid_data()
    {
        $dto = new CreateFlashcardDTO('Valid question', 'Valid answer', 1);
        
        $this->expectNotToPerformAssertions();
        $dto->validate();
    }

    public function test_validate_throws_exception_for_empty_question()
    {
        $dto = new CreateFlashcardDTO('', 'Valid answer', 1);
        
        $this->expectException(\InvalidArgumentException::class);
        $dto->validate();
    }

    public function test_validate_throws_exception_for_whitespace_only_question()
    {
        $dto = new CreateFlashcardDTO('   ', 'Valid answer', 1);
        
        $this->expectException(\InvalidArgumentException::class);
        $dto->validate();
    }

    public function test_validate_throws_exception_for_empty_answer()
    {
        $dto = new CreateFlashcardDTO('Valid question', '', 1);
        
        $this->expectException(\InvalidArgumentException::class);
        $dto->validate();
    }

    public function test_validate_throws_exception_for_whitespace_only_answer()
    {
        $dto = new CreateFlashcardDTO('Valid question', '   ', 1);
        
        $this->expectException(\InvalidArgumentException::class);
        $dto->validate();
    }

    public function test_validate_throws_exception_for_question_too_long()
    {
        $longQuestion = str_repeat('a', 1001);
        $dto = new CreateFlashcardDTO($longQuestion, 'Valid answer', 1);
        
        $this->expectException(\InvalidArgumentException::class);
        $dto->validate();
    }

    public function test_validate_throws_exception_for_answer_too_long()
    {
        $longAnswer = str_repeat('a', 1001);
        $dto = new CreateFlashcardDTO('Valid question', $longAnswer, 1);
        
        $this->expectException(\InvalidArgumentException::class);
        $dto->validate();
    }

    public function test_validate_passes_with_max_length_question()
    {
        $maxQuestion = str_repeat('a', 1000);
        $dto = new CreateFlashcardDTO($maxQuestion, 'Valid answer', 1);
        
        $this->expectNotToPerformAssertions();
        $dto->validate();
    }

    public function test_validate_passes_with_max_length_answer()
    {
        $maxAnswer = str_repeat('a', 1000);
        $dto = new CreateFlashcardDTO('Valid question', $maxAnswer, 1);
        
        $this->expectNotToPerformAssertions();
        $dto->validate();
    }

    public function test_validate_handles_unicode_characters()
    {
        $dto = new CreateFlashcardDTO('¿Qué es PHP?', 'Un lenguaje de programación', 1);
        
        $this->expectNotToPerformAssertions();
        $dto->validate();
    }

    public function test_validate_trims_whitespace_properly()
    {
        $dto = new CreateFlashcardDTO('  Valid question  ', '  Valid answer  ', 1);
        
        $this->expectNotToPerformAssertions();
        $dto->validate();
    }

    public function test_readonly_property_immutability()
    {
        $dto = new CreateFlashcardDTO('Question', 'Answer', 1);
        
        $this->assertEquals('Question', $dto->question);
        $this->assertEquals('Answer', $dto->answer);
        $this->assertEquals(1, $dto->deckId);
    }
}
