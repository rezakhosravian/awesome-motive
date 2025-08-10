<?php

namespace Tests\Unit\Services;

use App\Enums\ApiStatusCode;
use App\Services\Api\ApiResponseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class ApiResponseServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock translations
        $this->app->instance('translator', new class {
            public function get($key, $replace = [], $locale = null)
            {
                return match($key) {
                    'api.responses.success' => 'Request completed successfully.',
                    'api.responses.created' => 'Resource created successfully.',
                    'api.responses.updated' => 'Resource updated successfully.',
                    'api.responses.deleted' => 'Resource deleted successfully.',
                    'api.responses.unauthorized' => 'Authentication credentials are required or invalid.',
                    'api.responses.not_found' => 'The requested resource was not found.',
                    'api.responses.error' => 'An error occurred while processing your request.',
                    'api.responses.validation_error' => 'The given data was invalid.',
                    default => $key
                };
            }
        });
    }

    public function test_basic_success_response()
    {
        $response = ApiResponseService::success(['key' => 'value']);
        
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('success', $data['status']);
        $this->assertEquals('Request completed successfully.', $data['message']);
        $this->assertArrayHasKey('timestamp', $data);
        $this->assertEquals(['key' => 'value'], $data['data']);
    }

    public function test_created_response()
    {
        $response = ApiResponseService::created(['id' => 1]);
        
        $this->assertEquals(201, $response->getStatusCode());
        
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('created', $data['status']);
        $this->assertEquals('Resource created successfully.', $data['message']);
    }

    public function test_updated_response()
    {
        $response = ApiResponseService::updated(['id' => 1, 'name' => 'Updated']);
        
        $this->assertEquals(200, $response->getStatusCode());
        
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('updated', $data['status']);
        $this->assertEquals('Resource updated successfully.', $data['message']);
    }

    public function test_deleted_response()
    {
        $response = ApiResponseService::deleted();
        
        $this->assertEquals(200, $response->getStatusCode());
        
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('deleted', $data['status']);
        $this->assertEquals('Resource deleted successfully.', $data['message']);
        $this->assertArrayNotHasKey('data', $data);
    }

    public function test_error_response()
    {
        $response = ApiResponseService::error('Something went wrong', 400);
        
        $this->assertEquals(400, $response->getStatusCode());
        
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('error', $data['status']);
        $this->assertEquals('Something went wrong', $data['message']);
        $this->assertArrayHasKey('timestamp', $data);
    }

    public function test_unauthorized_response()
    {
        $response = ApiResponseService::unauthorized();
        
        $this->assertEquals(401, $response->getStatusCode());
        
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('unauthorized', $data['status']);
        $this->assertEquals('Authentication credentials are required or invalid.', $data['message']);
    }

    public function test_not_found_response()
    {
        $response = ApiResponseService::notFound();
        
        $this->assertEquals(404, $response->getStatusCode());
        
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('not_found', $data['status']);
        $this->assertEquals('The requested resource was not found.', $data['message']);
    }

    public function test_validation_error_response()
    {
        $errors = ['email' => ['Email is required'], 'name' => ['Name must be string']];
        $response = ApiResponseService::validationError($errors);
        
        $this->assertEquals(422, $response->getStatusCode());
        
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('validation_error', $data['status']);
        $this->assertEquals('The given data was invalid.', $data['message']);
        $this->assertEquals($errors, $data['errors']);
    }

    public function test_builder_pattern()
    {
        $response = ApiResponseService::make()
            ->status(ApiStatusCode::SUCCESS)
            ->data(['test' => 'data'])
            ->meta(['extra' => 'info'])
            ->message('Custom message')
            ->build();
        
        $this->assertEquals(200, $response->getStatusCode());
        
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('success', $data['status']);
        $this->assertEquals('Custom message', $data['message']);
        $this->assertEquals(['test' => 'data'], $data['data']);
        $this->assertEquals(['extra' => 'info'], $data['meta']);
    }

    public function test_paginated_response()
    {
        $items = collect([['id' => 1], ['id' => 2], ['id' => 3]]);
        $paginator = new LengthAwarePaginator(
            $items,
            10,
            5,
            1,
            ['path' => '/test']
        );
        
        $response = ApiResponseService::paginated($paginator);
        
        $this->assertEquals(200, $response->getStatusCode());
        
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('success', $data['status']);
        $this->assertArrayHasKey('pagination', $data);
        $this->assertArrayHasKey('current_page', $data['pagination']);
        $this->assertArrayHasKey('total', $data['pagination']);
        $this->assertEquals($items->toArray(), $data['data']);
    }

    public function test_response_includes_timestamp()
    {
        $response = ApiResponseService::success();
        
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('timestamp', $data);
        $this->assertMatchesRegularExpression('/\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/', $data['timestamp']);
    }

    public function test_with_errors_array()
    {
        $errors = ['field1' => 'Error 1', 'field2' => 'Error 2'];
        
        $response = ApiResponseService::make()
            ->status(ApiStatusCode::ERROR)
            ->errors($errors)
            ->build();
        
        $data = json_decode($response->getContent(), true);
        $this->assertEquals($errors, $data['errors']);
    }

    public function test_custom_message_overrides_default()
    {
        $customMessage = 'Custom success message';
        $response = ApiResponseService::success(null, $customMessage);
        
        $data = json_decode($response->getContent(), true);
        $this->assertEquals($customMessage, $data['message']);
    }
}