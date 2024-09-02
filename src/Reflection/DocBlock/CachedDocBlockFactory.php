<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Reflection\DocBlock;

use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\Types\Context;
use ReflectionClass;
use ReflectionClassConstant;
use ReflectionMethod;
use ReflectionProperty;
use TheCodingMachine\GraphQLite\Cache\ClassBoundCache;

use function md5;

/**
 * Creates DocBlocks and puts these in cache.
 */
class CachedDocBlockFactory implements DocBlockFactory
{
    public function __construct(
        private readonly ClassBoundCache $classBoundCache,
        private readonly DocBlockFactory $docBlockFactory,
    )
    {
    }

    public function create(
        ReflectionClass|ReflectionMethod|ReflectionProperty|ReflectionClassConstant $reflector,
        Context|null $context = null,
    ): DocBlock {
        $class = $reflector instanceof ReflectionClass ? $reflector : $reflector->getDeclaringClass();

        return $this->classBoundCache->get(
            $class,
            fn () => $this->docBlockFactory->create($reflector, $context ?? $this->createContext($class)),
            'reflection.docBlock.' . md5($reflector::class . '.' . $reflector->getName()),
        );
    }

    public function createContext(ReflectionClass|ReflectionMethod|ReflectionProperty|ReflectionClassConstant $reflector): Context
    {
        $reflector = $reflector instanceof ReflectionClass ? $reflector : $reflector->getDeclaringClass();

        return $this->classBoundCache->get(
            $reflector,
            fn () => $this->docBlockFactory->createContext($reflector),
            'reflection.docBlockContext',
        );
    }
}
