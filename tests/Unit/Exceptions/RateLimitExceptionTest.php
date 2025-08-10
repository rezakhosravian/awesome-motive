<?php

namespace Tests\Unit\Exceptions;

use App\Enums\ApiStatusCode;
use App\Exceptions\RateLimitException;
use Tests\TestCase;

class RateLimitExceptionTest extends TestCase
{
    public function test_constructor_sets_properties()
    {
        $exception = new RateLimitException('Rate limit exceeded', 120, 'CUSTOM_RATE_LIMIT');

        $this->assertEquals('Rate limit exceeded', $exception->getMessage());
        $this->assertEquals(120, $exception->getRetryAfter());
        $this->assertEquals('CUSTOM_RATE_LIMIT', $exception->getErrorCode());
        $this->assertEquals(429, $exception->getCode());
    }

    public function test_constructor_with_defaults()
    {
        $exception = new RateLimitException();

        $this->assertEquals(60, $exception->getRetryAfter());
        $this->assertEquals('RATE_LIMIT_EXCEEDED', $exception->getErrorCode());
        $this->assertEquals(429, $exception->getCode());
    }

    public function test_constructor_with_partial_defaults()
    {
        $exception = new RateLimitException('Custom message', 300);

        $this->assertEquals('Custom message', $exception->getMessage());
        $this->assertEquals(300, $exception->getRetryAfter());
        $this->assertEquals('RATE_LIMIT_EXCEEDED', $exception->getErrorCode());
    }

    public function test_get_api_status_code()
    {
        $exception = new RateLimitException();

        $this->assertEquals(ApiStatusCode::TOO_MANY_REQUESTS, $exception->getApiStatusCode());
    }

    public function test_api_token_limit_static_factory()
    {
        $exception = RateLimitException::apiTokenLimit('Token limit exceeded');

        $this->assertEquals('Token limit exceeded', $exception->getMessage());
        $this->assertEquals(3600, $exception->getRetryAfter()); // 1 hour
        $this->assertEquals('API_TOKEN_LIMIT_EXCEEDED', $exception->getErrorCode());
        $this->assertEquals(ApiStatusCode::TOO_MANY_REQUESTS, $exception->getApiStatusCode());
        $this->assertEquals(429, $exception->getCode());
    }

    public function test_api_token_limit_with_default_message()
    {
        $exception = RateLimitException::apiTokenLimit();

        $this->assertEquals(3600, $exception->getRetryAfter());
        $this->assertEquals('API_TOKEN_LIMIT_EXCEEDED', $exception->getErrorCode());
    }

    public function test_request_limit_static_factory()
    {
        $exception = RateLimitException::requestLimit('Request limit exceeded', 180);

        $this->assertEquals('Request limit exceeded', $exception->getMessage());
        $this->assertEquals(180, $exception->getRetryAfter());
        $this->assertEquals('REQUEST_RATE_LIMIT_EXCEEDED', $exception->getErrorCode());
        $this->assertEquals(ApiStatusCode::TOO_MANY_REQUESTS, $exception->getApiStatusCode());
    }

    public function test_request_limit_with_default_message()
    {
        $exception = RateLimitException::requestLimit();

        $this->assertEquals(60, $exception->getRetryAfter());
        $this->assertEquals('REQUEST_RATE_LIMIT_EXCEEDED', $exception->getErrorCode());
    }

    public function test_request_limit_with_custom_retry_after()
    {
        $exception = RateLimitException::requestLimit(null, 240);

        $this->assertEquals(240, $exception->getRetryAfter());
        $this->assertEquals('REQUEST_RATE_LIMIT_EXCEEDED', $exception->getErrorCode());
    }

    public function test_exception_inheritance()
    {
        $exception = new RateLimitException();

        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertInstanceOf(\App\Exceptions\ApiExceptionInterface::class, $exception);
    }

    public function test_constructor_with_null_message()
    {
        $exception = new RateLimitException(null, 60);

        // Should use default message from translation
        $this->assertIsString($exception->getMessage());
        $this->assertEquals(60, $exception->getRetryAfter());
    }

    public function test_constructor_without_custom_error_code()
    {
        $exception = new RateLimitException('Test message', 90, null);

        $this->assertEquals('RATE_LIMIT_EXCEEDED', $exception->getErrorCode());
    }

    public function test_all_getters_return_correct_types()
    {
        $exception = new RateLimitException('Message', 120, 'CODE');

        $this->assertIsString($exception->getErrorCode());
        $this->assertIsInt($exception->getRetryAfter());
        $this->assertInstanceOf(ApiStatusCode::class, $exception->getApiStatusCode());
    }

    public function test_retry_after_values()
    {
        $exception1 = new RateLimitException(null, 0);
        $this->assertEquals(0, $exception1->getRetryAfter());

        $exception2 = new RateLimitException(null, 1);
        $this->assertEquals(1, $exception2->getRetryAfter());

        $exception3 = new RateLimitException(null, 86400); // 24 hours
        $this->assertEquals(86400, $exception3->getRetryAfter());
    }

    public function test_http_code_is_429()
    {
        $exception = new RateLimitException();
        
        $this->assertEquals(429, $exception->getCode());
    }
}
