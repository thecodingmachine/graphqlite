<?php

namespace TheCodingMachine\GraphQLite\Reflection\DocBlock;

use phpDocumentor\Reflection\Types\Context;
use Reflector;

interface DocBlockContextFactory
{
    public function createFromReflector(Reflector $reflector): Context;
}