<?php

namespace App\Exceptions\Auth;

use Exception;
use App\Enums\ApiStatusCode;
use App\Exceptions\ApiExceptionInterface;

class InvalidApiTokenException extends Exception implements ApiExceptionInterface
{
    protected string $errorCode = 'INVALID_API_TOKEN';
    
    public function __construct(string $message = null)
    {
        $message = $message ?? __('api.auth.unauthorized');
        
        parent::__construct($message, ApiStatusCode::UNAUTHORIZED->httpCode());
    }
    
    public function getApiStatusCode(): ApiStatusCode
    {
        return ApiStatusCode::UNAUTHORIZED;
    }
    
    public function getErrorCode(): string
    {
        return $this->errorCode;
    }
}
