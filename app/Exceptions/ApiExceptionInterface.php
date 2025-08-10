<?php

namespace App\Exceptions;

use App\Enums\ApiStatusCode;

interface ApiExceptionInterface
{
    /**
     * Get the API status code for this exception
     */
    public function getApiStatusCode(): ApiStatusCode;
    
    /**
     * Get the specific error code for this exception
     */
    public function getErrorCode(): string;
}
