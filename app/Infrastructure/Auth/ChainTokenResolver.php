<?php

namespace App\Infrastructure\Auth;

use Illuminate\Http\Request;

class ChainTokenResolver implements TokenResolverInterface
{
    /** @var TokenResolverInterface[] */
    private array $resolvers;

    public function __construct(TokenResolverInterface ...$resolvers)
    {
        $this->resolvers = $resolvers;
    }

    public function resolve(Request $request): ?string
    {
        foreach ($this->resolvers as $resolver) {
            $token = $resolver->resolve($request);
            if (!empty($token)) {
                return $token;
            }
        }
        return null;
    }
}
