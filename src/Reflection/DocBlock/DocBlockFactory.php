<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Reflection\DocBlock;

use phpDocumentor\Reflection\DocBlock;
use Reflector;

interface DocBlockFactory
{
    public function createFromReflector(Reflector $reflector): DocBlock;
}
