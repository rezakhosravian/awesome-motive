<?php

namespace Tests\Unit\Exceptions\Deck;

use Tests\TestCase;
use App\Exceptions\Deck\DeckNotFoundException;
use App\Exceptions\Deck\DeckAccessDeniedException;
use App\Exceptions\Deck\DeckValidationException;
use App\Enums\ApiStatusCode;

class DeckExceptionsTest extends TestCase
{
    public function test_deck_not_found_exception()
    {
        $exception = new DeckNotFoundException('Custom message', 123);

        $this->assertEquals('Custom message (ID: 123)', $exception->getMessage());
        $this->assertEquals(404, $exception->getCode());
        $this->assertEquals(ApiStatusCode::NOT_FOUND, $exception->getApiStatusCode());
        $this->assertEquals('DECK_NOT_FOUND', $exception->getErrorCode());
    }

    public function test_deck_not_found_exception_with_defaults()
    {
        $exception = new DeckNotFoundException();

        $this->assertEquals(404, $exception->getCode());
        $this->assertEquals(ApiStatusCode::NOT_FOUND, $exception->getApiStatusCode());
        $this->assertEquals('DECK_NOT_FOUND', $exception->getErrorCode());
    }

    public function test_deck_access_denied_exception()
    {
        $exception = new DeckAccessDeniedException('Access denied', 456);

        $this->assertEquals('Access denied (Deck ID: 456)', $exception->getMessage());
        $this->assertEquals(403, $exception->getCode());
        $this->assertEquals(ApiStatusCode::FORBIDDEN, $exception->getApiStatusCode());
        $this->assertEquals('DECK_ACCESS_DENIED', $exception->getErrorCode());
    }

    public function test_deck_access_denied_exception_with_defaults()
    {
        $exception = new DeckAccessDeniedException();

        $this->assertEquals(403, $exception->getCode());
        $this->assertEquals(ApiStatusCode::FORBIDDEN, $exception->getApiStatusCode());
        $this->assertEquals('DECK_ACCESS_DENIED', $exception->getErrorCode());
    }

    public function test_deck_validation_exception()
    {
        $validationErrors = ['name' => ['Name is required'], 'description' => ['Too long']];
        $exception = new DeckValidationException('Validation failed', $validationErrors);

        $this->assertEquals('Validation failed', $exception->getMessage());
        $this->assertEquals(422, $exception->getCode());
        $this->assertEquals(ApiStatusCode::VALIDATION_ERROR, $exception->getApiStatusCode());
        $this->assertEquals('DECK_VALIDATION_ERROR', $exception->getErrorCode());
        $this->assertEquals($validationErrors, $exception->getValidationErrors());
    }

    public function test_deck_validation_exception_with_defaults()
    {
        $exception = new DeckValidationException();

        $this->assertEquals(422, $exception->getCode());
        $this->assertEquals(ApiStatusCode::VALIDATION_ERROR, $exception->getApiStatusCode());
        $this->assertEquals('DECK_VALIDATION_ERROR', $exception->getErrorCode());
        $this->assertEquals([], $exception->getValidationErrors());
    }
}
