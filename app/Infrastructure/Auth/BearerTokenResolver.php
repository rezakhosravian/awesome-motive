<?php

namespace App\Infrastructure\Auth;

use Illuminate\Http\Request;

class BearerTokenResolver implements TokenResolverInterface
{
    public function resolve(Request $request): ?string
    {
        $authorizationHeader = $request->header('Authorization');
        if ($authorizationHeader && str_starts_with($authorizationHeader, 'Bearer ')) {
            return substr($authorizationHeader, 7);
        }
        return null;
    }
}
