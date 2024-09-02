<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Reflection\DocBlock;

use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlockFactory as DocBlockFactoryConcrete;
use phpDocumentor\Reflection\DocBlockFactoryInterface;
use phpDocumentor\Reflection\Types\Context;
use phpDocumentor\Reflection\Types\ContextFactory;
use ReflectionClass;
use ReflectionClassConstant;
use ReflectionMethod;
use ReflectionProperty;

class PhpDocumentorDocBlockFactory implements DocBlockFactory
{
    public function __construct(
        private readonly DocBlockFactoryInterface $docBlockFactory,
        private readonly ContextFactory $contextFactory,
    )
    {
    }

    public static function default(): self
    {
        return new self(
            DocBlockFactoryConcrete::createInstance(),
            new ContextFactory(),
        );
    }

    public function create(
        ReflectionClass|ReflectionMethod|ReflectionProperty|ReflectionClassConstant $reflector,
        Context|null $context = null,
    ): DocBlock
    {
        $docblock = $reflector->getDocComment() ?: '/** */';

        return $this->docBlockFactory->create(
            $docblock,
            $context ?? $this->createContext($reflector),
        );
    }

    public function createContext(ReflectionClass|ReflectionMethod|ReflectionProperty|ReflectionClassConstant $reflector): Context
    {
        return $this->contextFactory->createFromReflector($reflector);
    }
}
