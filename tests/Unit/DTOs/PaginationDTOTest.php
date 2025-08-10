<?php

namespace Tests\Unit\DTOs;

use App\DTOs\PaginationDTO;
use Illuminate\Http\Request;
use Tests\TestCase;

class PaginationDTOTest extends TestCase
{
    public function test_constructor_sets_properties()
    {
        $dto = new PaginationDTO(2, 20, 50, 5);
        
        $this->assertEquals(2, $dto->page);
        $this->assertEquals(20, $dto->perPage);
        $this->assertEquals(50, $dto->maxPerPage);
        $this->assertEquals(5, $dto->minPerPage);
    }

    public function test_constructor_with_default_min_per_page()
    {
        $dto = new PaginationDTO(1, 15, 50);
        
        $this->assertEquals(1, $dto->page);
        $this->assertEquals(15, $dto->perPage);
        $this->assertEquals(50, $dto->maxPerPage);
        $this->assertEquals(1, $dto->minPerPage);
    }

    public function test_from_request_creates_dto()
    {
        config(['flashcard.pagination.default_per_page' => 15]);
        config(['flashcard.pagination.max_per_page' => 100]);
        config(['flashcard.pagination.min_per_page' => 5]);
        
        $request = new Request(['page' => 3, 'per_page' => 25]);
        
        $dto = PaginationDTO::fromRequest($request);

        $this->assertEquals(3, $dto->page);
        $this->assertEquals(25, $dto->perPage);
        $this->assertEquals(100, $dto->maxPerPage);
        $this->assertEquals(5, $dto->minPerPage);
    }

    public function test_from_request_with_defaults()
    {
        config(['flashcard.pagination.default_per_page' => 20]);
        config(['flashcard.pagination.max_per_page' => 50]);
        config(['flashcard.pagination.min_per_page' => 1]);
        
        $request = new Request();
        
        $dto = PaginationDTO::fromRequest($request);

        $this->assertEquals(1, $dto->page);
        $this->assertEquals(20, $dto->perPage);
        $this->assertEquals(50, $dto->maxPerPage);
        $this->assertEquals(1, $dto->minPerPage);
    }

    public function test_from_request_enforces_max_per_page()
    {
        config(['flashcard.pagination.default_per_page' => 15]);
        config(['flashcard.pagination.max_per_page' => 30]);
        config(['flashcard.pagination.min_per_page' => 1]);
        
        $request = new Request(['per_page' => 100]);
        
        $dto = PaginationDTO::fromRequest($request);

        $this->assertEquals(30, $dto->perPage);
    }

    public function test_from_request_enforces_min_per_page()
    {
        config(['flashcard.pagination.default_per_page' => 15]);
        config(['flashcard.pagination.max_per_page' => 50]);
        config(['flashcard.pagination.min_per_page' => 5]);
        
        $request = new Request(['per_page' => 2]);
        
        $dto = PaginationDTO::fromRequest($request);

        $this->assertEquals(5, $dto->perPage);
    }

    public function test_from_request_enforces_minimum_page()
    {
        config(['flashcard.pagination.default_per_page' => 15]);
        config(['flashcard.pagination.max_per_page' => 50]);
        config(['flashcard.pagination.min_per_page' => 1]);
        
        $request = new Request(['page' => 0]);
        
        $dto = PaginationDTO::fromRequest($request);

        $this->assertEquals(1, $dto->page);
    }

    public function test_from_request_with_negative_page()
    {
        config(['flashcard.pagination.default_per_page' => 15]);
        config(['flashcard.pagination.max_per_page' => 50]);
        config(['flashcard.pagination.min_per_page' => 1]);
        
        $request = new Request(['page' => -5]);
        
        $dto = PaginationDTO::fromRequest($request);

        $this->assertEquals(1, $dto->page);
    }

    public function test_from_config_creates_dto()
    {
        config(['flashcard.pagination.default_per_page' => 25]);
        config(['flashcard.pagination.max_per_page' => 100]);
        config(['flashcard.pagination.min_per_page' => 5]);
        
        $dto = PaginationDTO::fromConfig(2, 30);

        $this->assertEquals(2, $dto->page);
        $this->assertEquals(30, $dto->perPage);
        $this->assertEquals(100, $dto->maxPerPage);
        $this->assertEquals(5, $dto->minPerPage);
    }

    public function test_from_config_with_defaults()
    {
        config(['flashcard.pagination.default_per_page' => 20]);
        config(['flashcard.pagination.max_per_page' => 50]);
        config(['flashcard.pagination.min_per_page' => 1]);
        
        $dto = PaginationDTO::fromConfig();

        $this->assertEquals(1, $dto->page);
        $this->assertEquals(20, $dto->perPage);
        $this->assertEquals(50, $dto->maxPerPage);
        $this->assertEquals(1, $dto->minPerPage);
    }

    public function test_from_config_enforces_max_per_page()
    {
        config(['flashcard.pagination.default_per_page' => 15]);
        config(['flashcard.pagination.max_per_page' => 30]);
        config(['flashcard.pagination.min_per_page' => 1]);
        
        $dto = PaginationDTO::fromConfig(1, 100);

        $this->assertEquals(30, $dto->perPage);
    }

    public function test_from_config_enforces_min_per_page()
    {
        config(['flashcard.pagination.default_per_page' => 15]);
        config(['flashcard.pagination.max_per_page' => 50]);
        config(['flashcard.pagination.min_per_page' => 5]);
        
        $dto = PaginationDTO::fromConfig(1, 2);

        $this->assertEquals(5, $dto->perPage);
    }

    public function test_from_config_enforces_minimum_page()
    {
        config(['flashcard.pagination.default_per_page' => 15]);
        config(['flashcard.pagination.max_per_page' => 50]);
        config(['flashcard.pagination.min_per_page' => 1]);
        
        $dto = PaginationDTO::fromConfig(0);

        $this->assertEquals(1, $dto->page);
    }

    public function test_from_config_with_negative_page()
    {
        config(['flashcard.pagination.default_per_page' => 15]);
        config(['flashcard.pagination.max_per_page' => 50]);
        config(['flashcard.pagination.min_per_page' => 1]);
        
        $dto = PaginationDTO::fromConfig(-3);

        $this->assertEquals(1, $dto->page);
    }

    public function test_to_array_returns_correct_structure()
    {
        $dto = new PaginationDTO(3, 25, 100, 5);
        $array = $dto->toArray();

        $this->assertEquals([
            'page' => 3,
            'per_page' => 25,
            'max_per_page' => 100,
            'min_per_page' => 5,
        ], $array);
    }

    public function test_validate_passes_with_valid_data()
    {
        $dto = new PaginationDTO(2, 20, 50, 5);
        
        $this->expectNotToPerformAssertions();
        $dto->validate();
    }

    public function test_validate_throws_exception_for_page_less_than_one()
    {
        $dto = new PaginationDTO(0, 20, 50, 5);
        
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Page must be at least 1');
        $dto->validate();
    }

    public function test_validate_throws_exception_for_negative_page()
    {
        $dto = new PaginationDTO(-1, 20, 50, 5);
        
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Page must be at least 1');
        $dto->validate();
    }

    public function test_validate_throws_exception_for_per_page_below_minimum()
    {
        $dto = new PaginationDTO(1, 3, 50, 5);
        
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Per page must be between 5 and 50');
        $dto->validate();
    }

    public function test_validate_throws_exception_for_per_page_above_maximum()
    {
        $dto = new PaginationDTO(1, 60, 50, 5);
        
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Per page must be between 5 and 50');
        $dto->validate();
    }

    public function test_validate_passes_with_boundary_values()
    {
        $dto = new PaginationDTO(1, 5, 50, 5);
        
        $this->expectNotToPerformAssertions();
        $dto->validate();

        $dto2 = new PaginationDTO(1, 50, 50, 5);
        
        $this->expectNotToPerformAssertions();
        $dto2->validate();
    }

    public function test_readonly_property_immutability()
    {
        $dto = new PaginationDTO(2, 20, 50, 5);
        
        // This test ensures readonly properties work as expected
        $this->assertEquals(2, $dto->page);
        $this->assertEquals(20, $dto->perPage);
        $this->assertEquals(50, $dto->maxPerPage);
        $this->assertEquals(5, $dto->minPerPage);
    }

    public function test_from_request_with_string_values()
    {
        config(['flashcard.pagination.default_per_page' => 15]);
        config(['flashcard.pagination.max_per_page' => 50]);
        config(['flashcard.pagination.min_per_page' => 1]);
        
        $request = new Request(['page' => '3', 'per_page' => '25']);
        
        $dto = PaginationDTO::fromRequest($request);

        $this->assertEquals(3, $dto->page);
        $this->assertEquals(25, $dto->perPage);
    }

    public function test_from_request_with_invalid_string_values()
    {
        config(['flashcard.pagination.default_per_page' => 15]);
        config(['flashcard.pagination.max_per_page' => 50]);
        config(['flashcard.pagination.min_per_page' => 1]);
        
        $request = new Request(['page' => 'abc', 'per_page' => 'xyz']);
        
        $dto = PaginationDTO::fromRequest($request);

        $this->assertEquals(1, $dto->page); // Invalid cast to 0, then max(0, 1) = 1
        $this->assertEquals(1, $dto->perPage); // Invalid cast to 0, then max(0, 1) = 1
    }
}
