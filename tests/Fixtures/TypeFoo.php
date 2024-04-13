<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures;

use TheCodingMachine\GraphQLite\Annotations\SourceField;
use TheCodingMachine\GraphQLite\Annotations\Type;

/**
 * A type whose class name does not END with Type
 */
#[Type(class: TestObject::class)]
#[SourceField(name: 'test')]
class TypeFoo
{
}
