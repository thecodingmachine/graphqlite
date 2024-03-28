<?php

namespace TheCodingMachine\GraphQLite\Reflection\DocBlock;

use phpDocumentor\Reflection\Types\Context;
use phpDocumentor\Reflection\Types\ContextFactory as ContextFactoryConcrete;
use Reflector;

class PhpDocumentorDocBlockContextFactory implements DocBlockContextFactory
{
    public function __construct(
        private readonly ContextFactoryConcrete $contextFactory,
    )
    {
    }

    public function createFromReflector(Reflector $reflector): Context
    {
        return $this->contextFactory->createFromReflector($reflector);
    }
}