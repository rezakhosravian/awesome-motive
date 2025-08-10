<?php

namespace App\Infrastructure\Auth;

use Illuminate\Http\Request;

class ApiKeyHeaderResolver implements TokenResolverInterface
{
    public function resolve(Request $request): ?string
    {
        return $request->header('X-API-Key');
    }
}


