<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Reflection\DocBlock;

use phpDocumentor\Reflection\DocBlock;
use ReflectionClass;
use ReflectionClassConstant;
use ReflectionMethod;
use ReflectionProperty;

interface DocBlockFactory
{
    public function createFromReflector(ReflectionClass|ReflectionMethod|ReflectionProperty|ReflectionClassConstant $reflector): DocBlock;
}
