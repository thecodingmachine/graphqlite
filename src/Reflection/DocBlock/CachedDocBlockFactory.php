<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Reflection\DocBlock;

use phpDocumentor\Reflection\DocBlock;
use ReflectionClass;
use Reflector;
use TheCodingMachine\CacheUtils\ClassBoundCacheContractInterface;

use function md5;

/**
 * Creates DocBlocks and puts these in cache.
 */
class CachedDocBlockFactory implements DocBlockFactory
{
    public function __construct(
        private readonly ClassBoundCacheContractInterface $classBoundCacheContract,
        private readonly DocBlockFactory $docBlockFactory,
    )
    {
    }

    public function createFromReflector(Reflector $reflector): DocBlock
    {
        $class = $reflector instanceof ReflectionClass ? $reflector : $reflector->getDeclaringClass();

        return $this->classBoundCacheContract->get(
            $class,
            fn () => $this->docBlockFactory->createFromReflector($reflector),
            'reflection.docBlock.' . md5($reflector::class . '.' . $reflector->getName()),
        );
    }
}
