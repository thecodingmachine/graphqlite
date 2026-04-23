<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\MutationInterfaceFreeze\Types;

use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Type;

#[Type]
class ConcreteResult implements ResultInterface
{
    public function __construct(private readonly string $message)
    {
    }

    #[Field]
    public function getMessage(): string
    {
        return $this->message;
    }

    #[Field]
    public function getExtra(): string
    {
        return 'extra';
    }
}
