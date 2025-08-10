<?php

namespace Tests\Unit\Exceptions\Auth;

use Tests\TestCase;
use App\Exceptions\Auth\InvalidApiTokenException;
use App\Enums\ApiStatusCode;

class AuthExceptionsTest extends TestCase
{
    public function test_invalid_api_token_exception()
    {
        $exception = new InvalidApiTokenException('Bad token');
        
        $this->assertEquals('Bad token', $exception->getMessage());
        $this->assertEquals(401, $exception->getCode());
        $this->assertEquals(ApiStatusCode::UNAUTHORIZED, $exception->getApiStatusCode());
        $this->assertEquals('INVALID_API_TOKEN', $exception->getErrorCode());
    }

    public function test_invalid_api_token_exception_with_defaults()
    {
        $exception = new InvalidApiTokenException();
        
        $this->assertEquals(401, $exception->getCode());
        $this->assertEquals(ApiStatusCode::UNAUTHORIZED, $exception->getApiStatusCode());
        $this->assertEquals('INVALID_API_TOKEN', $exception->getErrorCode());
    }
}
