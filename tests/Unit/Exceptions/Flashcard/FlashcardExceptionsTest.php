<?php

namespace Tests\Unit\Exceptions\Flashcard;

use Tests\TestCase;
use App\Exceptions\Flashcard\FlashcardNotFoundException;
use App\Exceptions\Flashcard\FlashcardAccessDeniedException;
use App\Enums\ApiStatusCode;

class FlashcardExceptionsTest extends TestCase
{
    public function test_flashcard_not_found_exception()
    {
        $exception = new FlashcardNotFoundException('Flashcard missing', 789);
        
        $this->assertEquals('Flashcard missing (ID: 789)', $exception->getMessage());
        $this->assertEquals(404, $exception->getCode());
        $this->assertEquals(ApiStatusCode::NOT_FOUND, $exception->getApiStatusCode());
        $this->assertEquals('FLASHCARD_NOT_FOUND', $exception->getErrorCode());
    }

    public function test_flashcard_not_found_exception_with_defaults()
    {
        $exception = new FlashcardNotFoundException();
        
        $this->assertEquals(404, $exception->getCode());
        $this->assertEquals(ApiStatusCode::NOT_FOUND, $exception->getApiStatusCode());
        $this->assertEquals('FLASHCARD_NOT_FOUND', $exception->getErrorCode());
    }

    public function test_flashcard_access_denied_exception()
    {
        $exception = new FlashcardAccessDeniedException('No permission', 101);
        
        $this->assertEquals('No permission (Flashcard ID: 101)', $exception->getMessage());
        $this->assertEquals(403, $exception->getCode());
        $this->assertEquals(ApiStatusCode::FORBIDDEN, $exception->getApiStatusCode());
        $this->assertEquals('FLASHCARD_ACCESS_DENIED', $exception->getErrorCode());
    }

    public function test_flashcard_access_denied_exception_with_defaults()
    {
        $exception = new FlashcardAccessDeniedException();
        
        $this->assertEquals(403, $exception->getCode());
        $this->assertEquals(ApiStatusCode::FORBIDDEN, $exception->getApiStatusCode());
        $this->assertEquals('FLASHCARD_ACCESS_DENIED', $exception->getErrorCode());
    }
}
