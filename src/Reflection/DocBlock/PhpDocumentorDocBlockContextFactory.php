<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Reflection\DocBlock;

use phpDocumentor\Reflection\Types\Context;
use phpDocumentor\Reflection\Types\ContextFactory as ContextFactoryConcrete;
use ReflectionClass;
use ReflectionClassConstant;
use ReflectionMethod;
use ReflectionProperty;

class PhpDocumentorDocBlockContextFactory implements DocBlockContextFactory
{
    public function __construct(
        private readonly ContextFactoryConcrete $contextFactory,
    )
    {
    }

    public function createFromReflector(ReflectionClass|ReflectionMethod|ReflectionProperty|ReflectionClassConstant $reflector): Context
    {
        return $this->contextFactory->createFromReflector($reflector);
    }
}
