<?php

namespace Tests\Unit\Enums;

use App\Enums\ApiStatusCode;
use Tests\TestCase;

class ApiStatusCodeTest extends TestCase
{
    public function test_status_code_values()
    {
        $this->assertEquals('success', ApiStatusCode::SUCCESS->value);
        $this->assertEquals('error', ApiStatusCode::ERROR->value);
        $this->assertEquals('validation_error', ApiStatusCode::VALIDATION_ERROR->value);
        $this->assertEquals('unauthorized', ApiStatusCode::UNAUTHORIZED->value);
        $this->assertEquals('forbidden', ApiStatusCode::FORBIDDEN->value);
        $this->assertEquals('not_found', ApiStatusCode::NOT_FOUND->value);
        $this->assertEquals('server_error', ApiStatusCode::SERVER_ERROR->value);
        $this->assertEquals('created', ApiStatusCode::CREATED->value);
        $this->assertEquals('updated', ApiStatusCode::UPDATED->value);
        $this->assertEquals('deleted', ApiStatusCode::DELETED->value);
    }

    public function test_http_code_mappings()
    {
        $this->assertEquals(200, ApiStatusCode::SUCCESS->httpCode());
        $this->assertEquals(201, ApiStatusCode::CREATED->httpCode());
        $this->assertEquals(200, ApiStatusCode::UPDATED->httpCode());
        $this->assertEquals(200, ApiStatusCode::DELETED->httpCode());
        $this->assertEquals(422, ApiStatusCode::VALIDATION_ERROR->httpCode());
        $this->assertEquals(401, ApiStatusCode::UNAUTHORIZED->httpCode());
        $this->assertEquals(403, ApiStatusCode::FORBIDDEN->httpCode());
        $this->assertEquals(404, ApiStatusCode::NOT_FOUND->httpCode());
        $this->assertEquals(500, ApiStatusCode::SERVER_ERROR->httpCode());
        $this->assertEquals(400, ApiStatusCode::ERROR->httpCode());
    }

    public function test_message_keys()
    {
        $this->assertEquals('api.responses.success', ApiStatusCode::SUCCESS->message());
        $this->assertEquals('api.responses.created', ApiStatusCode::CREATED->message());
        $this->assertEquals('api.responses.updated', ApiStatusCode::UPDATED->message());
        $this->assertEquals('api.responses.deleted', ApiStatusCode::DELETED->message());
        $this->assertEquals('api.responses.validation_error', ApiStatusCode::VALIDATION_ERROR->message());
        $this->assertEquals('api.responses.unauthorized', ApiStatusCode::UNAUTHORIZED->message());
        $this->assertEquals('api.responses.forbidden', ApiStatusCode::FORBIDDEN->message());
        $this->assertEquals('api.responses.not_found', ApiStatusCode::NOT_FOUND->message());
        $this->assertEquals('api.responses.server_error', ApiStatusCode::SERVER_ERROR->message());
        $this->assertEquals('api.responses.error', ApiStatusCode::ERROR->message());
    }

    public function test_all_enum_cases_have_valid_http_codes()
    {
        foreach (ApiStatusCode::cases() as $status) {
            $httpCode = $status->httpCode();
            $this->assertIsInt($httpCode);
            $this->assertGreaterThanOrEqual(200, $httpCode);
            $this->assertLessThanOrEqual(599, $httpCode);
        }
    }

    public function test_all_enum_cases_have_valid_message_keys()
    {
        foreach (ApiStatusCode::cases() as $status) {
            $messageKey = $status->message();
            $this->assertIsString($messageKey);
            $this->assertStringStartsWith('api.', $messageKey);
        }
    }
}