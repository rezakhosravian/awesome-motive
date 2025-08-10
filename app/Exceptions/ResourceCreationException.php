<?php

namespace App\Exceptions;

use App\Enums\ApiStatusCode;
use Exception;

/**
 * Exception thrown when resource creation fails
 */
class ResourceCreationException extends Exception implements ApiExceptionInterface
{
    protected string $errorCode = 'RESOURCE_CREATION_FAILED';
    protected string $resourceType;
    protected array $context = [];
    
    public function __construct(
        string $resourceType,
        string $message = null, 
        array $context = [],
        string $errorCode = null
    ) {
        $this->resourceType = $resourceType;
        $this->context = $context;
        
        if ($errorCode) {
            $this->errorCode = $errorCode;
        }
        
        $message = $message ?? "Failed to create {$resourceType}";
        
        parent::__construct($message, ApiStatusCode::ERROR->httpCode());
    }
    
    public function getApiStatusCode(): ApiStatusCode
    {
        return ApiStatusCode::ERROR;
    }
    
    public function getErrorCode(): string
    {
        return $this->errorCode;
    }
    
    public function getResourceType(): string
    {
        return $this->resourceType;
    }
    
    public function getContext(): array
    {
        return $this->context;
    }
    
    /**
     * Create a deck creation exception
     */
    public static function deck(string $message = null, array $context = []): self
    {
        return new self(
            'deck',
            $message ?? __('api.decks.creation_failed'),
            $context,
            'DECK_CREATION_FAILED'
        );
    }
    
    /**
     * Create a flashcard creation exception
     */
    public static function flashcard(string $message = null, array $context = []): self
    {
        return new self(
            'flashcard',
            $message ?? __('api.flashcards.creation_failed'),
            $context,
            'FLASHCARD_CREATION_FAILED'
        );
    }
    
    /**
     * Create an API token creation exception
     */
    public static function apiToken(string $message = null, array $context = []): self
    {
        return new self(
            'api_token',
            $message ?? __('api.tokens.creation_failed'),
            $context,
            'API_TOKEN_CREATION_FAILED'
        );
    }
}
