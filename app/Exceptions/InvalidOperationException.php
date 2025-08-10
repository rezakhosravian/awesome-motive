<?php

namespace App\Exceptions;

use App\Enums\ApiStatusCode;
use Exception;

/**
 * Exception thrown when an operation violates business rules
 */
class InvalidOperationException extends Exception implements ApiExceptionInterface
{
    protected string $errorCode = 'INVALID_OPERATION';
    protected string $operation;
    protected array $reasons = [];
    
    public function __construct(
        string $operation,
        string $message = null, 
        array $reasons = [],
        string $errorCode = null
    ) {
        $this->operation = $operation;
        $this->reasons = $reasons;
        
        if ($errorCode) {
            $this->errorCode = $errorCode;
        }
        
        $message = $message ?? "Invalid operation: {$operation}";
        
        parent::__construct($message, ApiStatusCode::BAD_REQUEST->httpCode());
    }
    
    public function getApiStatusCode(): ApiStatusCode
    {
        return ApiStatusCode::BAD_REQUEST;
    }
    
    public function getErrorCode(): string
    {
        return $this->errorCode;
    }
    
    public function getOperation(): string
    {
        return $this->operation;
    }
    
    public function getReasons(): array
    {
        return $this->reasons;
    }
    
    /**
     * Create an exception for deck study operations
     */
    public static function studyDeck(string $message = null, array $reasons = []): self
    {
        return new self(
            'study_deck',
            $message ?? __('api.decks.cannot_study'),
            $reasons,
            'DECK_STUDY_INVALID'
        );
    }
    
    /**
     * Create an exception for deck deletion when it has flashcards
     */
    public static function deleteDeckWithFlashcards(string $message = null): self
    {
        return new self(
            'delete_deck',
            $message ?? __('api.decks.cannot_delete_with_flashcards'),
            ['has_flashcards' => true],
            'DECK_DELETE_HAS_FLASHCARDS'
        );
    }
    
    /**
     * Create an exception for operations on archived resources
     */
    public static function archivedResource(string $resourceType, string $message = null): self
    {
        return new self(
            "operate_on_{$resourceType}",
            $message ?? "Cannot perform operation on archived {$resourceType}",
            ['archived' => true],
            'ARCHIVED_RESOURCE_OPERATION'
        );
    }
    
    /**
     * Create an exception for insufficient permissions
     */
    public static function insufficientPermissions(string $operation, string $message = null): self
    {
        return new self(
            $operation,
            $message ?? __('api.responses.forbidden'),
            ['permission_denied' => true],
            'INSUFFICIENT_PERMISSIONS'
        );
    }
}
