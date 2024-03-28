<?php

namespace TheCodingMachine\GraphQLite\Reflection\DocBlock;

use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlockFactory as DocBlockFactoryConcrete;
use Reflector;

class PhpDocumentorDocBlockFactory implements DocBlockFactory
{
    public function __construct(
        private readonly DocBlockFactoryConcrete $docBlockFactory,
        private readonly DocBlockContextFactory $docBlockContextFactory,
    )
    {
    }

    public function createFromReflector(Reflector $reflector): DocBlock
    {
        $docblock = $reflector->getDocComment() ?: '/** */';
        $context = $this->docBlockContextFactory->createFromReflector($reflector);

        return $this->docBlockFactory->create($docblock, $context);
    }
}