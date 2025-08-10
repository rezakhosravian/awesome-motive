<?php

namespace App\Exceptions\Deck;

use Exception;
use App\Enums\ApiStatusCode;
use App\Exceptions\ApiExceptionInterface;

class DeckValidationException extends Exception implements ApiExceptionInterface
{
    protected string $errorCode = 'DECK_VALIDATION_ERROR';
    protected array $validationErrors = [];
    
    public function __construct(string $message = null, array $validationErrors = [])
    {
        $this->validationErrors = $validationErrors;
        $message = $message ?? __('api.responses.validation_error');
        
        parent::__construct($message, ApiStatusCode::VALIDATION_ERROR->httpCode());
    }
    
    public function getApiStatusCode(): ApiStatusCode
    {
        return ApiStatusCode::VALIDATION_ERROR;
    }
    
    public function getErrorCode(): string
    {
        return $this->errorCode;
    }
    
    public function getValidationErrors(): array
    {
        return $this->validationErrors;
    }
}
