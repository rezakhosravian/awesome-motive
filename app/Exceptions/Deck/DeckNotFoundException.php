<?php

namespace App\Exceptions\Deck;

use Exception;
use App\Enums\ApiStatusCode;
use App\Exceptions\ApiExceptionInterface;

class DeckNotFoundException extends Exception implements ApiExceptionInterface
{
    protected string $errorCode = 'DECK_NOT_FOUND';
    
    public function __construct(string $message = null, int $deckId = null)
    {
        $message = $message ?? __('api.decks.not_found');
        
        if ($deckId) {
            $message .= " (ID: {$deckId})";
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
