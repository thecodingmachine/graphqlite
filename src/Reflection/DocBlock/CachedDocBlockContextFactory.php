<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Reflection\DocBlock;

use phpDocumentor\Reflection\Types\Context;
use ReflectionClass;
use ReflectionClassConstant;
use ReflectionMethod;
use ReflectionProperty;
use TheCodingMachine\GraphQLite\Cache\ClassBoundCache;

class CachedDocBlockContextFactory implements DocBlockContextFactory
{
    public function __construct(
        private readonly ClassBoundCache $classBoundCache,
        private readonly DocBlockContextFactory $contextFactory,
    )
    {
    }

    public function createFromReflector(ReflectionClass|ReflectionMethod|ReflectionProperty|ReflectionClassConstant $reflector): Context
    {
        $reflector = $reflector instanceof ReflectionClass ? $reflector : $reflector->getDeclaringClass();

        return $this->classBoundCache->get(
            $reflector,
            fn () => $this->contextFactory->createFromReflector($reflector),
            'reflection.docBlockContext',
        );
    }
}
