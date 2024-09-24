<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Reflection\DocBlock;

use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\Types\Context;
use ReflectionClass;
use ReflectionClassConstant;
use ReflectionMethod;
use ReflectionProperty;

interface DocBlockFactory
{
    /**
     * Fetches a DocBlock object from a ReflectionMethod
     */
    public function create(
        ReflectionClass|ReflectionMethod|ReflectionProperty|ReflectionClassConstant $reflector,
        Context|null $context = null,
    ): DocBlock;

    public function createContext(ReflectionClass|ReflectionMethod|ReflectionProperty|ReflectionClassConstant $reflector): Context;
}
