<?php

namespace App\Exceptions\Deck;

use Exception;
use App\Enums\ApiStatusCode;
use App\Exceptions\ApiExceptionInterface;

class DeckAccessDeniedException extends Exception implements ApiExceptionInterface
{
    protected string $errorCode = 'DECK_ACCESS_DENIED';
    
    public function __construct(string $message = null, int $deckId = null)
    {
        $message = $message ?? __('api.decks.update_forbidden');
        
        if ($deckId) {
            $message .= " (Deck ID: {$deckId})";
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
