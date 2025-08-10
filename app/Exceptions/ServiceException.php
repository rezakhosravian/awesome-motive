<?php

namespace App\Exceptions;

use App\Enums\ApiStatusCode;
use Exception;

/**
 * General service layer exception for business logic errors
 */
class ServiceException extends Exception implements ApiExceptionInterface
{
    protected string $errorCode = 'SERVICE_ERROR';
    
    public function __construct(
        string $message = null, 
        string $errorCode = null,
        ApiStatusCode $statusCode = ApiStatusCode::ERROR
    ) {
        if ($errorCode) {
            $this->errorCode = $errorCode;
        }
        
        $message = $message ?? __('api.responses.error');
        
        parent::__construct($message, $statusCode->httpCode());
    }
    
    public function getApiStatusCode(): ApiStatusCode
    {
        return match($this->getCode()) {
            400 => ApiStatusCode::BAD_REQUEST,
            401 => ApiStatusCode::UNAUTHORIZED,
            403 => ApiStatusCode::FORBIDDEN,
            404 => ApiStatusCode::NOT_FOUND,
            422 => ApiStatusCode::VALIDATION_ERROR,
            500 => ApiStatusCode::SERVER_ERROR,
            default => ApiStatusCode::ERROR,
        };
    }
    
    public function getErrorCode(): string
    {
        return $this->errorCode;
    }
    
    /**
     * Create a bad request service exception
     */
    public static function badRequest(string $message = null, string $errorCode = 'BAD_REQUEST'): self
    {
        return new self($message ?? __('api.responses.bad_request'), $errorCode, ApiStatusCode::BAD_REQUEST);
    }
    
    /**
     * Create a not found service exception
     */
    public static function notFound(string $message = null, string $errorCode = 'NOT_FOUND'): self
    {
        return new self($message ?? __('api.responses.not_found'), $errorCode, ApiStatusCode::NOT_FOUND);
    }
    
    /**
     * Create a forbidden service exception
     */
    public static function forbidden(string $message = null, string $errorCode = 'FORBIDDEN'): self
    {
        return new self($message ?? __('api.responses.forbidden'), $errorCode, ApiStatusCode::FORBIDDEN);
    }
}
