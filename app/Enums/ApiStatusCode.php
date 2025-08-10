<?php

namespace App\Enums;

enum ApiStatusCode: string
{
    case SUCCESS = 'success';
    case ERROR = 'error';
    case VALIDATION_ERROR = 'validation_error';
    case UNAUTHORIZED = 'unauthorized';
    case FORBIDDEN = 'forbidden';
    case NOT_FOUND = 'not_found';
    case SERVER_ERROR = 'server_error';
    case CREATED = 'created';
    case UPDATED = 'updated';
    case DELETED = 'deleted';
    case BAD_REQUEST = 'bad_request';
    case TOO_MANY_REQUESTS = 'too_many_requests';

    public function httpCode(): int
    {
        return match($this) {
            self::SUCCESS => 200,
            self::CREATED => 201,
            self::UPDATED => 200,
            self::DELETED => 200,
            self::VALIDATION_ERROR => 422,
            self::UNAUTHORIZED => 401,
            self::FORBIDDEN => 403,
            self::NOT_FOUND => 404,
            self::SERVER_ERROR => 500,
            self::BAD_REQUEST => 400,
            self::TOO_MANY_REQUESTS => 429,
            self::ERROR => 400,
        };
    }

    public function message(): string
    {
        return match($this) {
            self::SUCCESS => 'api.responses.success',
            self::CREATED => 'api.responses.created',
            self::UPDATED => 'api.responses.updated',
            self::DELETED => 'api.responses.deleted',
            self::VALIDATION_ERROR => 'api.responses.validation_error',
            self::UNAUTHORIZED => 'api.responses.unauthorized',
            self::FORBIDDEN => 'api.responses.forbidden',
            self::NOT_FOUND => 'api.responses.not_found',
            self::SERVER_ERROR => 'api.responses.server_error',
            self::TOO_MANY_REQUESTS => 'api.responses.too_many_requests',
            self::ERROR => 'api.responses.error',
            self::BAD_REQUEST => 'api.responses.bad_request',
        };
    }
}
