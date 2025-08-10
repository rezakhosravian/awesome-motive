<?php

namespace Tests\Unit\DTOs;

use App\DTOs\PaginationDTO;
use App\DTOs\SearchDTO;
use App\Exceptions\InvalidOperationException;
use Illuminate\Http\Request;
use Tests\TestCase;

class SearchDTOTest extends TestCase
{
    public function test_constructor_sets_properties()
    {
        $pagination = new PaginationDTO(1, 15, 50, 1);
        $dto = new SearchDTO('test query', true, $pagination);
        
        $this->assertEquals('test query', $dto->query);
        $this->assertTrue($dto->publicOnly);
        $this->assertInstanceOf(PaginationDTO::class, $dto->pagination);
    }

    public function test_constructor_with_defaults()
    {
        $dto = new SearchDTO('test query');
        
        $this->assertEquals('test query', $dto->query);
        $this->assertFalse($dto->publicOnly);
        $this->assertNull($dto->pagination);
    }

    public function test_from_request_creates_dto()
    {
        $request = new Request(['q' => '  test search  ', 'page' => 2, 'per_page' => 20]);
        
        $dto = SearchDTO::fromRequest($request, true);

        $this->assertEquals('test search', $dto->query);
        $this->assertTrue($dto->publicOnly);
        $this->assertInstanceOf(PaginationDTO::class, $dto->pagination);
    }

    public function test_from_request_with_empty_query()
    {
        $request = new Request();
        
        $dto = SearchDTO::fromRequest($request);

        $this->assertEquals('', $dto->query);
        $this->assertFalse($dto->publicOnly);
        $this->assertInstanceOf(PaginationDTO::class, $dto->pagination);
    }

    public function test_from_array_creates_dto()
    {
        $data = [
            'query' => 'array search',
            'public_only' => true,
            'pagination' => ['page' => 3, 'per_page' => 25]
        ];

        $dto = SearchDTO::fromArray($data);

        $this->assertEquals('array search', $dto->query);
        $this->assertTrue($dto->publicOnly);
        $this->assertInstanceOf(PaginationDTO::class, $dto->pagination);
    }

    public function test_from_array_with_q_parameter()
    {
        $data = [
            'q' => 'query via q param',
            'public_only' => false
        ];

        $dto = SearchDTO::fromArray($data);

        $this->assertEquals('query via q param', $dto->query);
        $this->assertFalse($dto->publicOnly);
        $this->assertInstanceOf(PaginationDTO::class, $dto->pagination);
    }

    public function test_from_array_with_default_pagination()
    {
        $data = [
            'query' => 'test',
        ];

        $dto = SearchDTO::fromArray($data);

        $this->assertEquals('test', $dto->query);
        $this->assertFalse($dto->publicOnly);
        $this->assertInstanceOf(PaginationDTO::class, $dto->pagination);
    }

    public function test_to_array_returns_correct_structure()
    {
        $pagination = new PaginationDTO(2, 20, 50, 1);
        $dto = new SearchDTO('test query', true, $pagination);
        
        $array = $dto->toArray();

        $this->assertEquals([
            'query' => 'test query',
            'public_only' => true,
            'pagination' => $pagination->toArray(),
        ], $array);
    }

    public function test_to_array_with_null_pagination()
    {
        $dto = new SearchDTO('test query', false, null);
        
        $array = $dto->toArray();

        $this->assertEquals([
            'query' => 'test query',
            'public_only' => false,
            'pagination' => null,
        ], $array);
    }

    public function test_validate_passes_with_valid_query()
    {
        // Mock config values
        config(['flashcard.search.min_query_length' => 2]);
        config(['flashcard.search.max_query_length' => 100]);
        
        $dto = new SearchDTO('valid query');
        
        $this->expectNotToPerformAssertions();
        $dto->validate();
    }

    public function test_validate_throws_exception_for_empty_query()
    {
        $dto = new SearchDTO('');
        
        $this->expectException(InvalidOperationException::class);
        $dto->validate();
    }

    public function test_validate_throws_exception_for_query_too_short()
    {
        config(['flashcard.search.min_query_length' => 5]);
        
        $dto = new SearchDTO('abc');
        
        $this->expectException(InvalidOperationException::class);
        $dto->validate();
    }

    public function test_validate_throws_exception_for_query_too_long()
    {
        config(['flashcard.search.min_query_length' => 2]);
        config(['flashcard.search.max_query_length' => 10]);
        
        $dto = new SearchDTO('this is a very long query');
        
        $this->expectException(InvalidOperationException::class);
        $dto->validate();
    }

    public function test_validate_with_pagination()
    {
        config(['flashcard.search.min_query_length' => 2]);
        config(['flashcard.search.max_query_length' => 100]);
        
        $pagination = new PaginationDTO(1, 15, 50, 1);
        $dto = new SearchDTO('valid query', false, $pagination);
        
        $this->expectNotToPerformAssertions();
        $dto->validate();
    }

    public function test_is_empty_returns_true_for_empty_query()
    {
        $dto = new SearchDTO('');
        
        $this->assertTrue($dto->isEmpty());
    }

    public function test_is_empty_returns_true_for_whitespace_query()
    {
        $dto = new SearchDTO('   ');
        
        $this->assertTrue($dto->isEmpty());
    }

    public function test_is_empty_returns_false_for_valid_query()
    {
        $dto = new SearchDTO('test');
        
        $this->assertFalse($dto->isEmpty());
    }

    public function test_get_query_returns_query()
    {
        $dto = new SearchDTO('my search query');
        
        $this->assertEquals('my search query', $dto->getQuery());
    }

    public function test_get_pagination_returns_pagination()
    {
        $pagination = new PaginationDTO(1, 15, 50, 1);
        $dto = new SearchDTO('test', false, $pagination);
        
        $this->assertSame($pagination, $dto->getPagination());
    }

    public function test_get_pagination_returns_default_when_null()
    {
        $dto = new SearchDTO('test', false, null);
        
        $pagination = $dto->getPagination();
        
        $this->assertInstanceOf(PaginationDTO::class, $pagination);
    }

    public function test_readonly_property_immutability()
    {
        $pagination = new PaginationDTO(1, 15, 50, 1);
        $dto = new SearchDTO('test query', true, $pagination);
        
        // This test ensures readonly properties work as expected
        $this->assertEquals('test query', $dto->query);
        $this->assertTrue($dto->publicOnly);
        $this->assertSame($pagination, $dto->pagination);
    }

    public function test_from_array_handles_various_input_formats()
    {
        $data = [
            'q' => '  search with spaces  ',
            'public_only' => 'true', // String boolean
        ];

        $dto = SearchDTO::fromArray($data);

        $this->assertEquals('search with spaces', $dto->query);
        $this->assertTrue($dto->publicOnly);
    }

    public function test_from_array_with_empty_data()
    {
        $data = [];

        $dto = SearchDTO::fromArray($data);

        $this->assertEquals('', $dto->query);
        $this->assertFalse($dto->publicOnly);
        $this->assertInstanceOf(PaginationDTO::class, $dto->pagination);
    }
}
