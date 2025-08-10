<?php

namespace Tests\Unit\Infrastructure\Pagination;

use App\Infrastructure\Pagination\PaginationParams;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;

class PaginationParamsTest extends TestCase
{
    public function test_constructor_sets_properties()
    {
        $params = new PaginationParams(10, 2);
        
        $this->assertEquals(10, $params->perPage);
        $this->assertEquals(2, $params->page);
    }

    public function test_from_request_with_default_values()
    {
        $request = new Request();
        
        $params = PaginationParams::fromRequest($request);
        
        $this->assertEquals(15, $params->perPage);
        $this->assertEquals(1, $params->page);
    }

    public function test_from_request_with_custom_defaults()
    {
        $request = new Request();
        
        $params = PaginationParams::fromRequest($request, 25, 100);
        
        $this->assertEquals(25, $params->perPage);
        $this->assertEquals(1, $params->page);
    }

    public function test_from_request_with_valid_input()
    {
        $request = new Request([
            'per_page' => 20,
            'page' => 3
        ]);
        
        $params = PaginationParams::fromRequest($request);
        
        $this->assertEquals(20, $params->perPage);
        $this->assertEquals(3, $params->page);
    }

    public function test_from_request_respects_max_per_page()
    {
        $request = new Request([
            'per_page' => 100,
            'page' => 1
        ]);
        
        $params = PaginationParams::fromRequest($request, 15, 50);
        
        $this->assertEquals(50, $params->perPage);
        $this->assertEquals(1, $params->page);
    }

    public function test_from_request_enforces_minimum_per_page()
    {
        $request = new Request([
            'per_page' => 0,
            'page' => 1
        ]);
        
        $params = PaginationParams::fromRequest($request);
        
        $this->assertEquals(1, $params->perPage);
        $this->assertEquals(1, $params->page);
    }

    public function test_from_request_enforces_minimum_page()
    {
        $request = new Request([
            'per_page' => 15,
            'page' => 0
        ]);
        
        $params = PaginationParams::fromRequest($request);
        
        $this->assertEquals(15, $params->perPage);
        $this->assertEquals(1, $params->page);
    }

    public function test_from_request_with_negative_values()
    {
        $request = new Request([
            'per_page' => -10,
            'page' => -5
        ]);
        
        $params = PaginationParams::fromRequest($request);
        
        $this->assertEquals(1, $params->perPage);
        $this->assertEquals(1, $params->page);
    }

    public function test_from_request_with_string_values()
    {
        $request = new Request([
            'per_page' => '25',
            'page' => '4'
        ]);
        
        $params = PaginationParams::fromRequest($request);
        
        $this->assertEquals(25, $params->perPage);
        $this->assertEquals(4, $params->page);
    }

    public function test_from_request_with_invalid_string_values()
    {
        $request = new Request([
            'per_page' => 'abc',
            'page' => 'xyz'
        ]);
        
        $params = PaginationParams::fromRequest($request);
        
        $this->assertEquals(1, $params->perPage); // Cast to int gives 0, then max(0, 1) = 1
        $this->assertEquals(1, $params->page); // Cast to int gives 0, then max(0, 1) = 1
    }

    public function test_from_request_boundary_values()
    {
        $request = new Request([
            'per_page' => 50,
            'page' => 1
        ]);
        
        $params = PaginationParams::fromRequest($request, 15, 50);
        
        $this->assertEquals(50, $params->perPage);
        $this->assertEquals(1, $params->page);
    }

    public function test_from_request_with_float_values()
    {
        $request = new Request([
            'per_page' => 25.7,
            'page' => 3.9
        ]);
        
        $params = PaginationParams::fromRequest($request);
        
        $this->assertEquals(25, $params->perPage); // Cast to int truncates
        $this->assertEquals(3, $params->page); // Cast to int truncates
    }
}
