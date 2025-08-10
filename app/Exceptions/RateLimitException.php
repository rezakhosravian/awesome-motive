<?php

namespace App\Exceptions;

use App\Enums\ApiStatusCode;
use Exception;

/**
 * Exception thrown when rate limits are exceeded
 */
class RateLimitException extends Exception implements ApiExceptionInterface
{
    protected string $errorCode = 'RATE_LIMIT_EXCEEDED';
    protected int $retryAfter;
    
    public function __construct(
        string $message = null, 
        int $retryAfter = 60,
        string $errorCode = null
    ) {
        if ($errorCode) {
            $this->errorCode = $errorCode;
        }
        
        $this->retryAfter = $retryAfter;
        $message = $message ?? __('api.auth.rate_limit_exceeded');
        
        parent::__construct($message, 429);
    }
    
    public function getApiStatusCode(): ApiStatusCode
    {
        return ApiStatusCode::TOO_MANY_REQUESTS;
    }
    
    public function getErrorCode(): string
    {
        return $this->errorCode;
    }
    
    public function getRetryAfter(): int
    {
        return $this->retryAfter;
    }
    
    /**
     * Create a rate limit exception for API tokens
     */
    public static function apiTokenLimit(string $message = null): self
    {
        return new self(
            $message ?? __('api.tokens.rate_limit_exceeded'),
            3600, // 1 hour
            'API_TOKEN_LIMIT_EXCEEDED'
        );
    }
    
    /**
     * Create a rate limit exception for requests
     */
    public static function requestLimit(string $message = null, int $retryAfter = 60): self
    {
        return new self(
            $message ?? __('api.auth.rate_limit_exceeded'),
            $retryAfter,
            'REQUEST_RATE_LIMIT_EXCEEDED'
        );
    }
}
