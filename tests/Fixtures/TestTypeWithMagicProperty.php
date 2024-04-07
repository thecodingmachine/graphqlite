<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures;

use TheCodingMachine\GraphQLite\Annotations\MagicField;
use TheCodingMachine\GraphQLite\Annotations\Type;

#[Type]
#[MagicField(name: 'foo', outputType: 'String!', description: 'Test MagicField description')]
class TestTypeWithMagicProperty
{
    public function __get(string $var)
    {
        return 'foo';
    }
}
