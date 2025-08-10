<?php

namespace App\Exceptions\Flashcard;

use Exception;
use App\Enums\ApiStatusCode;
use App\Exceptions\ApiExceptionInterface;

class FlashcardAccessDeniedException extends Exception implements ApiExceptionInterface
{
    protected string $errorCode = 'FLASHCARD_ACCESS_DENIED';
    
    public function __construct(string $message = null, int $flashcardId = null)
    {
        $message = $message ?? __('api.flashcards.update_forbidden');
        
        if ($flashcardId) {
            $message .= " (Flashcard ID: {$flashcardId})";
        }
        
        parent::__construct($message, ApiStatusCode::FORBIDDEN->httpCode());
    }
    
    public function getApiStatusCode(): ApiStatusCode
    {
        return ApiStatusCode::FORBIDDEN;
    }
    
    public function getErrorCode(): string
    {
        return $this->errorCode;
    }
}
