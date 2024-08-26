<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\Integration\Models;

use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Type;

#[Type]
class SpecialProduct
{
    public function __construct(private string $name, private float $price)
    {
    }

    #[Field]
    public function getSpecial(): string
    {
        return 'unicorn';
    }

    #[Field]
    public function getName(): string
    {
        return $this->name;
    }

    #[Field]
    public function getPrice(): float
    {
        return $this->price;
    }
}
