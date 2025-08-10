<?php

namespace App\Exceptions\Flashcard;

use Exception;
use App\Enums\ApiStatusCode;
use App\Exceptions\ApiExceptionInterface;

class FlashcardNotFoundException extends Exception implements ApiExceptionInterface
{
    protected string $errorCode = 'FLASHCARD_NOT_FOUND';
    
    public function __construct(string $message = null, int $flashcardId = null)
    {
        $message = $message ?? __('api.flashcards.not_found');
        
        if ($flashcardId) {
            $message .= " (ID: {$flashcardId})";
        }
        
        parent::__construct($message, ApiStatusCode::NOT_FOUND->httpCode());
    }
    
    public function getApiStatusCode(): ApiStatusCode
    {
        return ApiStatusCode::NOT_FOUND;
    }
    
    public function getErrorCode(): string
    {
        return $this->errorCode;
    }
}
