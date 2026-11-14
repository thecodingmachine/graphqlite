<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Tests\Fixtures\AnnotatedInterfaces\Types;

use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Type;

#[Type]
interface FooInterface
{
    #[Field]
    public function getFoo(): string;
}
