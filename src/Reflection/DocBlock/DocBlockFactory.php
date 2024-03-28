<?php

namespace TheCodingMachine\GraphQLite\Reflection\DocBlock;

use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\Types\Context;
use Reflector;

interface DocBlockFactory
{
    public function createFromReflector(Reflector $reflector): DocBlock;
}