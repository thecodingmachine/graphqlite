<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\InputOutputNameConflict\Types;

use TheCodingMachine\GraphQLite\Annotations\Factory;
use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Type;

#[Type(name: 'InAndOut')]
class InAndOut
{
    public function __construct(private string $value)
    {
    }

    #[Field]
    public function getValue(): string
    {
        return $this->value;
    }

    #[Factory(name: 'InAndOut')]
    public static function create(string $value): self
    {
        return new self($value);
    }
}
