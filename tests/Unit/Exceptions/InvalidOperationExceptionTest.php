<?php

namespace Tests\Unit\Exceptions;

use App\Enums\ApiStatusCode;
use App\Exceptions\InvalidOperationException;
use Tests\TestCase;

class InvalidOperationExceptionTest extends TestCase
{
    public function test_constructor_sets_properties()
    {
        $exception = new InvalidOperationException(
            'test_operation',
            'Test message',
            ['reason1' => 'value1'],
            'TEST_ERROR_CODE'
        );

        $this->assertEquals('test_operation', $exception->getOperation());
        $this->assertEquals('Test message', $exception->getMessage());
        $this->assertEquals(['reason1' => 'value1'], $exception->getReasons());
        $this->assertEquals('TEST_ERROR_CODE', $exception->getErrorCode());
        $this->assertEquals(400, $exception->getCode()); // BAD_REQUEST http code
    }

    public function test_constructor_with_defaults()
    {
        $exception = new InvalidOperationException('test_operation');

        $this->assertEquals('test_operation', $exception->getOperation());
        $this->assertEquals('Invalid operation: test_operation', $exception->getMessage());
        $this->assertEquals([], $exception->getReasons());
        $this->assertEquals('INVALID_OPERATION', $exception->getErrorCode());
        $this->assertEquals(400, $exception->getCode());
    }

    public function test_get_api_status_code()
    {
        $exception = new InvalidOperationException('test_operation');

        $this->assertEquals(ApiStatusCode::BAD_REQUEST, $exception->getApiStatusCode());
    }

    public function test_study_deck_static_factory()
    {
        $exception = InvalidOperationException::studyDeck('Custom study message', ['deck_id' => 123]);

        $this->assertEquals('study_deck', $exception->getOperation());
        $this->assertEquals('Custom study message', $exception->getMessage());
        $this->assertEquals(['deck_id' => 123], $exception->getReasons());
        $this->assertEquals('DECK_STUDY_INVALID', $exception->getErrorCode());
        $this->assertEquals(ApiStatusCode::BAD_REQUEST, $exception->getApiStatusCode());
    }

    public function test_study_deck_with_default_message()
    {
        $exception = InvalidOperationException::studyDeck();

        $this->assertEquals('study_deck', $exception->getOperation());
        $this->assertEquals('DECK_STUDY_INVALID', $exception->getErrorCode());
        $this->assertEquals([], $exception->getReasons());
    }

    public function test_delete_deck_with_flashcards_static_factory()
    {
        $exception = InvalidOperationException::deleteDeckWithFlashcards('Cannot delete deck with cards');

        $this->assertEquals('delete_deck', $exception->getOperation());
        $this->assertEquals('Cannot delete deck with cards', $exception->getMessage());
        $this->assertEquals(['has_flashcards' => true], $exception->getReasons());
        $this->assertEquals('DECK_DELETE_HAS_FLASHCARDS', $exception->getErrorCode());
        $this->assertEquals(ApiStatusCode::BAD_REQUEST, $exception->getApiStatusCode());
    }

    public function test_delete_deck_with_flashcards_default_message()
    {
        $exception = InvalidOperationException::deleteDeckWithFlashcards();

        $this->assertEquals('delete_deck', $exception->getOperation());
        $this->assertEquals('DECK_DELETE_HAS_FLASHCARDS', $exception->getErrorCode());
        $this->assertEquals(['has_flashcards' => true], $exception->getReasons());
    }

    public function test_archived_resource_static_factory()
    {
        $exception = InvalidOperationException::archivedResource('deck', 'Cannot modify archived deck');

        $this->assertEquals('operate_on_deck', $exception->getOperation());
        $this->assertEquals('Cannot modify archived deck', $exception->getMessage());
        $this->assertEquals(['archived' => true], $exception->getReasons());
        $this->assertEquals('ARCHIVED_RESOURCE_OPERATION', $exception->getErrorCode());
        $this->assertEquals(ApiStatusCode::BAD_REQUEST, $exception->getApiStatusCode());
    }

    public function test_archived_resource_with_default_message()
    {
        $exception = InvalidOperationException::archivedResource('flashcard');

        $this->assertEquals('operate_on_flashcard', $exception->getOperation());
        $this->assertEquals('Cannot perform operation on archived flashcard', $exception->getMessage());
        $this->assertEquals(['archived' => true], $exception->getReasons());
        $this->assertEquals('ARCHIVED_RESOURCE_OPERATION', $exception->getErrorCode());
    }

    public function test_insufficient_permissions_static_factory()
    {
        $exception = InvalidOperationException::insufficientPermissions('access_deck', 'Access denied to deck');

        $this->assertEquals('access_deck', $exception->getOperation());
        $this->assertEquals('Access denied to deck', $exception->getMessage());
        $this->assertEquals(['permission_denied' => true], $exception->getReasons());
        $this->assertEquals('INSUFFICIENT_PERMISSIONS', $exception->getErrorCode());
        $this->assertEquals(ApiStatusCode::BAD_REQUEST, $exception->getApiStatusCode());
    }

    public function test_insufficient_permissions_with_default_message()
    {
        $exception = InvalidOperationException::insufficientPermissions('view_private_deck');

        $this->assertEquals('view_private_deck', $exception->getOperation());
        $this->assertEquals('INSUFFICIENT_PERMISSIONS', $exception->getErrorCode());
        $this->assertEquals(['permission_denied' => true], $exception->getReasons());
    }

    public function test_exception_inheritance()
    {
        $exception = new InvalidOperationException('test_operation');

        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertInstanceOf(\App\Exceptions\ApiExceptionInterface::class, $exception);
    }

    public function test_constructor_with_null_message()
    {
        $exception = new InvalidOperationException('custom_operation', null);

        $this->assertEquals('Invalid operation: custom_operation', $exception->getMessage());
        $this->assertEquals('custom_operation', $exception->getOperation());
    }

    public function test_constructor_with_empty_reasons()
    {
        $exception = new InvalidOperationException('test_operation', 'Test message', []);

        $this->assertEquals([], $exception->getReasons());
    }

    public function test_constructor_without_custom_error_code()
    {
        $exception = new InvalidOperationException('test_operation', 'Test message', ['reason' => 'value'], null);

        $this->assertEquals('INVALID_OPERATION', $exception->getErrorCode());
    }

    public function test_all_getters_return_correct_types()
    {
        $exception = new InvalidOperationException(
            'operation',
            'message',
            ['key' => 'value'],
            'CODE'
        );

        $this->assertIsString($exception->getOperation());
        $this->assertIsString($exception->getErrorCode());
        $this->assertIsArray($exception->getReasons());
        $this->assertInstanceOf(ApiStatusCode::class, $exception->getApiStatusCode());
    }
}
