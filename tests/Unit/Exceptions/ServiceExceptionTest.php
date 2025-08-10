<?php

namespace Tests\Unit\Exceptions;

use App\Enums\ApiStatusCode;
use App\Exceptions\ServiceException;
use Tests\TestCase;

class ServiceExceptionTest extends TestCase
{
    public function test_constructor_sets_properties()
    {
        $exception = new ServiceException('Service error occurred', 'CUSTOM_SERVICE_ERROR', ApiStatusCode::BAD_REQUEST);

        $this->assertEquals('Service error occurred', $exception->getMessage());
        $this->assertEquals('CUSTOM_SERVICE_ERROR', $exception->getErrorCode());
        $this->assertEquals(400, $exception->getCode()); // BAD_REQUEST http code
    }

    public function test_constructor_with_defaults()
    {
        $exception = new ServiceException();

        $this->assertEquals('SERVICE_ERROR', $exception->getErrorCode());
        $this->assertEquals(400, $exception->getCode()); // ERROR default http code (ApiStatusCode::ERROR = 400)
    }

    public function test_constructor_with_partial_defaults()
    {
        $exception = new ServiceException('Custom service message', 'CUSTOM_CODE');

        $this->assertEquals('Custom service message', $exception->getMessage());
        $this->assertEquals('CUSTOM_CODE', $exception->getErrorCode());
        $this->assertEquals(400, $exception->getCode());
    }

    public function test_get_api_status_code_with_bad_request()
    {
        $exception = new ServiceException('Error', null, ApiStatusCode::BAD_REQUEST);

        $this->assertEquals(ApiStatusCode::BAD_REQUEST, $exception->getApiStatusCode());
    }

    public function test_get_api_status_code_with_unauthorized()
    {
        $exception = new ServiceException('Error', null, ApiStatusCode::UNAUTHORIZED);

        $this->assertEquals(ApiStatusCode::UNAUTHORIZED, $exception->getApiStatusCode());
    }

    public function test_get_api_status_code_with_forbidden()
    {
        $exception = new ServiceException('Error', null, ApiStatusCode::FORBIDDEN);

        $this->assertEquals(ApiStatusCode::FORBIDDEN, $exception->getApiStatusCode());
    }

    public function test_get_api_status_code_with_not_found()
    {
        $exception = new ServiceException('Error', null, ApiStatusCode::NOT_FOUND);

        $this->assertEquals(ApiStatusCode::NOT_FOUND, $exception->getApiStatusCode());
    }

    public function test_get_api_status_code_with_validation_error()
    {
        $exception = new ServiceException('Error', null, ApiStatusCode::VALIDATION_ERROR);

        $this->assertEquals(ApiStatusCode::VALIDATION_ERROR, $exception->getApiStatusCode());
    }

    public function test_get_api_status_code_with_server_error()
    {
        $exception = new ServiceException('Error', null, ApiStatusCode::SERVER_ERROR);

        $this->assertEquals(ApiStatusCode::SERVER_ERROR, $exception->getApiStatusCode());
    }

    public function test_get_api_status_code_with_unknown_code()
    {
        // Create exception with custom HTTP code not mapped in match statement
        $exception = new ServiceException('Error', null, ApiStatusCode::ERROR);
        // Manually set a different code to test default case
        $reflection = new \ReflectionClass($exception);
        $property = $reflection->getProperty('code');
        $property->setAccessible(true);
        $property->setValue($exception, 999);

        $this->assertEquals(ApiStatusCode::ERROR, $exception->getApiStatusCode());
    }

    public function test_bad_request_static_factory()
    {
        $exception = ServiceException::badRequest('Bad request occurred', 'CUSTOM_BAD_REQUEST');

        $this->assertEquals('Bad request occurred', $exception->getMessage());
        $this->assertEquals('CUSTOM_BAD_REQUEST', $exception->getErrorCode());
        $this->assertEquals(ApiStatusCode::BAD_REQUEST, $exception->getApiStatusCode());
        $this->assertEquals(400, $exception->getCode());
    }

    public function test_bad_request_with_defaults()
    {
        $exception = ServiceException::badRequest();

        $this->assertEquals('BAD_REQUEST', $exception->getErrorCode());
        $this->assertEquals(ApiStatusCode::BAD_REQUEST, $exception->getApiStatusCode());
    }

    public function test_not_found_static_factory()
    {
        $exception = ServiceException::notFound('Resource not found', 'CUSTOM_NOT_FOUND');

        $this->assertEquals('Resource not found', $exception->getMessage());
        $this->assertEquals('CUSTOM_NOT_FOUND', $exception->getErrorCode());
        $this->assertEquals(ApiStatusCode::NOT_FOUND, $exception->getApiStatusCode());
        $this->assertEquals(404, $exception->getCode());
    }

    public function test_not_found_with_defaults()
    {
        $exception = ServiceException::notFound();

        $this->assertEquals('NOT_FOUND', $exception->getErrorCode());
        $this->assertEquals(ApiStatusCode::NOT_FOUND, $exception->getApiStatusCode());
    }

    public function test_forbidden_static_factory()
    {
        $exception = ServiceException::forbidden('Access forbidden', 'CUSTOM_FORBIDDEN');

        $this->assertEquals('Access forbidden', $exception->getMessage());
        $this->assertEquals('CUSTOM_FORBIDDEN', $exception->getErrorCode());
        $this->assertEquals(ApiStatusCode::FORBIDDEN, $exception->getApiStatusCode());
        $this->assertEquals(403, $exception->getCode());
    }

    public function test_forbidden_with_defaults()
    {
        $exception = ServiceException::forbidden();

        $this->assertEquals('FORBIDDEN', $exception->getErrorCode());
        $this->assertEquals(ApiStatusCode::FORBIDDEN, $exception->getApiStatusCode());
    }

    public function test_exception_inheritance()
    {
        $exception = new ServiceException();

        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertInstanceOf(\App\Exceptions\ApiExceptionInterface::class, $exception);
    }

    public function test_constructor_with_null_message()
    {
        $exception = new ServiceException(null, 'CODE');

        // Should use default message from translation
        $this->assertIsString($exception->getMessage());
        $this->assertEquals('CODE', $exception->getErrorCode());
    }

    public function test_constructor_without_custom_error_code()
    {
        $exception = new ServiceException('Test message', null);

        $this->assertEquals('SERVICE_ERROR', $exception->getErrorCode());
    }

    public function test_all_getters_return_correct_types()
    {
        $exception = new ServiceException('Message', 'CODE', ApiStatusCode::ERROR);

        $this->assertIsString($exception->getErrorCode());
        $this->assertInstanceOf(ApiStatusCode::class, $exception->getApiStatusCode());
    }

    public function test_api_status_code_mapping_comprehensive()
    {
        $mappings = [
            400 => ApiStatusCode::BAD_REQUEST,
            401 => ApiStatusCode::UNAUTHORIZED, 
            403 => ApiStatusCode::FORBIDDEN,
            404 => ApiStatusCode::NOT_FOUND,
            422 => ApiStatusCode::VALIDATION_ERROR,
            500 => ApiStatusCode::SERVER_ERROR,
        ];

        foreach ($mappings as $httpCode => $expectedStatus) {
            // Create exception and manually set the code to test mapping
            $exception = new ServiceException();
            $reflection = new \ReflectionClass($exception);
            $property = $reflection->getProperty('code');
            $property->setAccessible(true);
            $property->setValue($exception, $httpCode);

            $this->assertEquals($expectedStatus, $exception->getApiStatusCode(), 
                "Failed mapping for HTTP code {$httpCode}");
        }
    }

    public function test_default_error_status_code()
    {
        $exception = new ServiceException('Test message');
        
        $this->assertEquals(400, $exception->getCode());
        $this->assertEquals(ApiStatusCode::BAD_REQUEST, $exception->getApiStatusCode());
    }

    public function test_static_factory_methods_with_null_messages()
    {
        $badRequest = ServiceException::badRequest(null, 'CODE');
        $notFound = ServiceException::notFound(null, 'CODE');
        $forbidden = ServiceException::forbidden(null, 'CODE');

        $this->assertIsString($badRequest->getMessage());
        $this->assertIsString($notFound->getMessage());
        $this->assertIsString($forbidden->getMessage());
    }
}
