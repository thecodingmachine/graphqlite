<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Tests\Fixtures\CircularInputReference\Controllers;

use TheCodingMachine\GraphQLite\Annotations\Mutation;
use TheCodingMachine\GraphQLite\Tests\Fixtures\CircularInputReference\Types\CircularInputA;
use TheCodingMachine\GraphQLite\Types\ID;

class CircularController
{
    #[Mutation]
    public function testCircularInput(CircularInputA $inAndOut): ID
    {
        return new ID('myID');
    }
}
