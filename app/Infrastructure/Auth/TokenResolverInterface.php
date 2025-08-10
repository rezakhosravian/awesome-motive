<?php

namespace App\Infrastructure\Auth;

use Illuminate\Http\Request;

interface TokenResolverInterface
{
    public function resolve(Request $request): ?string;
}


