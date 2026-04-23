<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\MutationInterfaceFreeze\Types;

use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Type;

#[Type]
interface ResultInterface
{
    #[Field]
    public function getMessage(): string;
}
