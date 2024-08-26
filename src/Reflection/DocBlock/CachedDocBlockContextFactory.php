<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Reflection\DocBlock;

use phpDocumentor\Reflection\Types\Context;
use ReflectionClass;
use Reflector;
use TheCodingMachine\CacheUtils\ClassBoundCacheContractInterface;

class CachedDocBlockContextFactory implements DocBlockContextFactory
{
    public function __construct(
        private readonly ClassBoundCacheContractInterface $classBoundCacheContract,
        private readonly DocBlockContextFactory $contextFactory,
    )
    {
    }

    public function createFromReflector(Reflector $reflector): Context
    {
        $reflector = $reflector instanceof ReflectionClass ? $reflector : $reflector->getDeclaringClass();

        return $this->classBoundCacheContract->get(
            $reflector,
            fn () => $this->contextFactory->createFromReflector($reflector),
            'reflection.docBlockContext',
        );
    }
}
