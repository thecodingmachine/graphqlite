<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\InterfaceAsOutputType\Types;

use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Input;

#[Input]
class CreateProductInput
{
    public function __construct(
        #[Field]
        public string $name,
    ) {
    }
}
