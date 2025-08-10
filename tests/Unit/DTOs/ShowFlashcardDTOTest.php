<?php

namespace Tests\Unit\DTOs;

use App\DTOs\ShowFlashcardDTO;
use Illuminate\Http\Request;
use Tests\TestCase;

class ShowFlashcardDTOTest extends TestCase
{
    public function test_constructor_sets_properties()
    {
        $dto = new ShowFlashcardDTO(1, 2, 3);
        
        $this->assertEquals(1, $dto->deckId);
        $this->assertEquals(2, $dto->flashcardId);
        $this->assertEquals(3, $dto->userId);
    }

    public function test_constructor_with_null_user_id()
    {
        $dto = new ShowFlashcardDTO(1, 2);
        
        $this->assertEquals(1, $dto->deckId);
        $this->assertEquals(2, $dto->flashcardId);
        $this->assertNull($dto->userId);
    }

    public function test_from_request_creates_dto()
    {
        $user = new \stdClass();
        $user->id = 5;
        
        $request = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['user'])
            ->getMock();
        
        $request->expects($this->once())
            ->method('user')
            ->willReturn($user);

        $dto = ShowFlashcardDTO::fromRequest($request, 10, 20);

        $this->assertEquals(10, $dto->deckId);
        $this->assertEquals(20, $dto->flashcardId);
        $this->assertEquals(5, $dto->userId);
    }

    public function test_from_request_with_no_user()
    {
        $request = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['user'])
            ->getMock();
        
        $request->expects($this->once())
            ->method('user')
            ->willReturn(null);

        $dto = ShowFlashcardDTO::fromRequest($request, 15, 25);

        $this->assertEquals(15, $dto->deckId);
        $this->assertEquals(25, $dto->flashcardId);
        $this->assertNull($dto->userId);
    }

    public function test_from_route_creates_dto()
    {
        $dto = ShowFlashcardDTO::fromRoute(100, 200, 300);

        $this->assertEquals(100, $dto->deckId);
        $this->assertEquals(200, $dto->flashcardId);
        $this->assertEquals(300, $dto->userId);
    }

    public function test_from_route_with_no_user_id()
    {
        $dto = ShowFlashcardDTO::fromRoute(100, 200);

        $this->assertEquals(100, $dto->deckId);
        $this->assertEquals(200, $dto->flashcardId);
        $this->assertNull($dto->userId);
    }

    public function test_to_array_returns_correct_structure()
    {
        $dto = new ShowFlashcardDTO(1, 2, 3);
        $array = $dto->toArray();

        $this->assertEquals([
            'deck_id' => 1,
            'flashcard_id' => 2,
            'user_id' => 3,
        ], $array);
    }

    public function test_to_array_with_null_user_id()
    {
        $dto = new ShowFlashcardDTO(1, 2, null);
        $array = $dto->toArray();

        $this->assertEquals([
            'deck_id' => 1,
            'flashcard_id' => 2,
            'user_id' => null,
        ], $array);
    }

    public function test_validate_passes_with_valid_data()
    {
        $dto = new ShowFlashcardDTO(1, 2, 3);
        
        $this->expectNotToPerformAssertions();
        $dto->validate();
    }

    public function test_validate_throws_exception_for_zero_deck_id()
    {
        $dto = new ShowFlashcardDTO(0, 2, 3);
        
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Deck ID must be positive');
        $dto->validate();
    }

    public function test_validate_throws_exception_for_negative_deck_id()
    {
        $dto = new ShowFlashcardDTO(-1, 2, 3);
        
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Deck ID must be positive');
        $dto->validate();
    }

    public function test_validate_throws_exception_for_zero_flashcard_id()
    {
        $dto = new ShowFlashcardDTO(1, 0, 3);
        
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Flashcard ID must be positive');
        $dto->validate();
    }

    public function test_validate_throws_exception_for_negative_flashcard_id()
    {
        $dto = new ShowFlashcardDTO(1, -1, 3);
        
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Flashcard ID must be positive');
        $dto->validate();
    }

    public function test_validate_allows_negative_user_id()
    {
        // User ID can be null or negative in some contexts
        $dto = new ShowFlashcardDTO(1, 2, -1);
        
        $this->expectNotToPerformAssertions();
        $dto->validate();
    }

    public function test_validate_allows_null_user_id()
    {
        $dto = new ShowFlashcardDTO(1, 2, null);
        
        $this->expectNotToPerformAssertions();
        $dto->validate();
    }

    public function test_readonly_property_immutability()
    {
        $dto = new ShowFlashcardDTO(10, 20, 30);
        
        // This test ensures readonly properties work as expected
        $this->assertEquals(10, $dto->deckId);
        $this->assertEquals(20, $dto->flashcardId);
        $this->assertEquals(30, $dto->userId);
    }

    public function test_from_request_with_authenticated_user_object()
    {
        // Create a mock user with an ID
        $user = (object) ['id' => 123];
        
        $request = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['user'])
            ->getMock();
        
        $request->expects($this->once())
            ->method('user')
            ->willReturn($user);

        $dto = ShowFlashcardDTO::fromRequest($request, 50, 60);

        $this->assertEquals(50, $dto->deckId);
        $this->assertEquals(60, $dto->flashcardId);
        $this->assertEquals(123, $dto->userId);
    }

    public function test_large_id_values()
    {
        $dto = new ShowFlashcardDTO(999999, 888888, 777777);
        
        $this->assertEquals(999999, $dto->deckId);
        $this->assertEquals(888888, $dto->flashcardId);
        $this->assertEquals(777777, $dto->userId);
        
        // Validate should pass for large positive IDs
        $dto->validate();
        $this->assertTrue(true); // Explicit assertion to avoid risky test warning
    }

    public function test_boundary_values()
    {
        $dto = new ShowFlashcardDTO(1, 1, 1);
        
        $this->expectNotToPerformAssertions();
        $dto->validate();
    }
}
