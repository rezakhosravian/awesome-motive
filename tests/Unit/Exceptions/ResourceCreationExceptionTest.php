<?php

namespace Tests\Unit\Exceptions;

use App\Enums\ApiStatusCode;
use App\Exceptions\ResourceCreationException;
use Tests\TestCase;

class ResourceCreationExceptionTest extends TestCase
{
    public function test_constructor_sets_properties()
    {
        $context = ['database_error' => 'Connection failed'];
        $exception = new ResourceCreationException(
            'test_resource',
            'Creation failed',
            $context,
            'CUSTOM_CREATION_ERROR'
        );

        $this->assertEquals('test_resource', $exception->getResourceType());
        $this->assertEquals('Creation failed', $exception->getMessage());
        $this->assertEquals($context, $exception->getContext());
        $this->assertEquals('CUSTOM_CREATION_ERROR', $exception->getErrorCode());
        $this->assertEquals(400, $exception->getCode()); // ERROR http code (ApiStatusCode::ERROR = 400)
    }

    public function test_constructor_with_defaults()
    {
        $exception = new ResourceCreationException('user');

        $this->assertEquals('user', $exception->getResourceType());
        $this->assertEquals('Failed to create user', $exception->getMessage());
        $this->assertEquals([], $exception->getContext());
        $this->assertEquals('RESOURCE_CREATION_FAILED', $exception->getErrorCode());
        $this->assertEquals(400, $exception->getCode());
    }

    public function test_constructor_with_partial_defaults()
    {
        $exception = new ResourceCreationException('deck', 'Custom creation message');

        $this->assertEquals('deck', $exception->getResourceType());
        $this->assertEquals('Custom creation message', $exception->getMessage());
        $this->assertEquals([], $exception->getContext());
        $this->assertEquals('RESOURCE_CREATION_FAILED', $exception->getErrorCode());
    }

    public function test_get_api_status_code()
    {
        $exception = new ResourceCreationException('test_resource');

        $this->assertEquals(ApiStatusCode::ERROR, $exception->getApiStatusCode());
    }

    public function test_deck_static_factory()
    {
        $context = ['validation_errors' => ['name' => 'required']];
        $exception = ResourceCreationException::deck('Deck creation failed', $context);

        $this->assertEquals('deck', $exception->getResourceType());
        $this->assertEquals('Deck creation failed', $exception->getMessage());
        $this->assertEquals($context, $exception->getContext());
        $this->assertEquals('DECK_CREATION_FAILED', $exception->getErrorCode());
        $this->assertEquals(ApiStatusCode::ERROR, $exception->getApiStatusCode());
    }

    public function test_deck_with_default_message()
    {
        $exception = ResourceCreationException::deck();

        $this->assertEquals('deck', $exception->getResourceType());
        $this->assertEquals('DECK_CREATION_FAILED', $exception->getErrorCode());
        $this->assertEquals([], $exception->getContext());
    }

    public function test_deck_with_context_only()
    {
        $context = ['user_id' => 123];
        $exception = ResourceCreationException::deck(null, $context);

        $this->assertEquals('deck', $exception->getResourceType());
        $this->assertEquals($context, $exception->getContext());
        $this->assertEquals('DECK_CREATION_FAILED', $exception->getErrorCode());
    }

    public function test_flashcard_static_factory()
    {
        $context = ['deck_id' => 456, 'error' => 'Invalid question'];
        $exception = ResourceCreationException::flashcard('Flashcard creation failed', $context);

        $this->assertEquals('flashcard', $exception->getResourceType());
        $this->assertEquals('Flashcard creation failed', $exception->getMessage());
        $this->assertEquals($context, $exception->getContext());
        $this->assertEquals('FLASHCARD_CREATION_FAILED', $exception->getErrorCode());
        $this->assertEquals(ApiStatusCode::ERROR, $exception->getApiStatusCode());
    }

    public function test_flashcard_with_default_message()
    {
        $exception = ResourceCreationException::flashcard();

        $this->assertEquals('flashcard', $exception->getResourceType());
        $this->assertEquals('FLASHCARD_CREATION_FAILED', $exception->getErrorCode());
        $this->assertEquals([], $exception->getContext());
    }

    public function test_api_token_static_factory()
    {
        $context = ['user_id' => 789, 'limit_reached' => true];
        $exception = ResourceCreationException::apiToken('API token creation failed', $context);

        $this->assertEquals('api_token', $exception->getResourceType());
        $this->assertEquals('API token creation failed', $exception->getMessage());
        $this->assertEquals($context, $exception->getContext());
        $this->assertEquals('API_TOKEN_CREATION_FAILED', $exception->getErrorCode());
        $this->assertEquals(ApiStatusCode::ERROR, $exception->getApiStatusCode());
    }

    public function test_api_token_with_default_message()
    {
        $exception = ResourceCreationException::apiToken();

        $this->assertEquals('api_token', $exception->getResourceType());
        $this->assertEquals('API_TOKEN_CREATION_FAILED', $exception->getErrorCode());
        $this->assertEquals([], $exception->getContext());
    }

    public function test_exception_inheritance()
    {
        $exception = new ResourceCreationException('test_resource');

        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertInstanceOf(\App\Exceptions\ApiExceptionInterface::class, $exception);
    }

    public function test_constructor_with_null_message()
    {
        $exception = new ResourceCreationException('product', null);

        $this->assertEquals('Failed to create product', $exception->getMessage());
        $this->assertEquals('product', $exception->getResourceType());
    }

    public function test_constructor_with_empty_context()
    {
        $exception = new ResourceCreationException('service', 'Creation failed', []);

        $this->assertEquals([], $exception->getContext());
    }

    public function test_constructor_without_custom_error_code()
    {
        $exception = new ResourceCreationException('resource', 'Failed', ['key' => 'value'], null);

        $this->assertEquals('RESOURCE_CREATION_FAILED', $exception->getErrorCode());
    }

    public function test_all_getters_return_correct_types()
    {
        $context = ['error' => 'Database connection failed'];
        $exception = new ResourceCreationException(
            'test_type',
            'Test message',
            $context,
            'TEST_CODE'
        );

        $this->assertIsString($exception->getResourceType());
        $this->assertIsString($exception->getErrorCode());
        $this->assertIsArray($exception->getContext());
        $this->assertInstanceOf(ApiStatusCode::class, $exception->getApiStatusCode());
    }

    public function test_context_with_complex_data()
    {
        $complexContext = [
            'user_data' => ['id' => 1, 'name' => 'Test User'],
            'validation_errors' => [
                'field1' => ['error1', 'error2'],
                'field2' => ['error3']
            ],
            'timestamp' => '2023-01-01 12:00:00',
            'nested' => [
                'level1' => [
                    'level2' => 'deep value'
                ]
            ]
        ];

        $exception = new ResourceCreationException('complex_resource', 'Failed', $complexContext);

        $this->assertEquals($complexContext, $exception->getContext());
        $this->assertEquals('complex_resource', $exception->getResourceType());
    }

    public function test_http_code_is_400()
    {
        $exception = new ResourceCreationException('test');
        
        $this->assertEquals(400, $exception->getCode());
    }
}
