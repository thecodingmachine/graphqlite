<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\InterfaceAsOutputType\Types;

use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Type;

#[Type(name: 'Product')]
interface ProductInterface
{
    #[Field]
    public function getName(): string;
}
