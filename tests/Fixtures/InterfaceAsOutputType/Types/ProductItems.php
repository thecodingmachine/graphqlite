<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\InterfaceAsOutputType\Types;

use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Type;

#[Type]
class ProductItems
{
    /** @param list<Book> $products */
    public function __construct(private array $products)
    {
    }

    // Referencing the interface by name via outputType resolves it early, which is what left the
    // interface "already registered" by the time getOutputTypes() reached its class.
    #[Field(outputType: '[Product]')]
    public function getItems(): array
    {
        return $this->products;
    }
}
