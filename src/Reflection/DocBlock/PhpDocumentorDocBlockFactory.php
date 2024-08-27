<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Reflection\DocBlock;

use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlockFactoryInterface;
use ReflectionClass;
use ReflectionClassConstant;
use ReflectionMethod;
use ReflectionProperty;

class PhpDocumentorDocBlockFactory implements DocBlockFactory
{
    public function __construct(
        private readonly DocBlockFactoryInterface $docBlockFactory,
        private readonly DocBlockContextFactory $docBlockContextFactory,
    )
    {
    }

    public function createFromReflector(ReflectionClass|ReflectionMethod|ReflectionProperty|ReflectionClassConstant $reflector): DocBlock
    {
        $docblock = $reflector->getDocComment() ?: '/** */';
        $context = $this->docBlockContextFactory->createFromReflector($reflector);

        return $this->docBlockFactory->create($docblock, $context);
    }
}
