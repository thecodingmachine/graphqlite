<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\InterfaceAsOutputType\Types;

use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Type;

#[Type]
class Book implements ProductInterface
{
    public function __construct(private string $name)
    {
    }

    public function getName(): string
    {
        return $this->name;
    }

    #[Field]
    public function getAuthor(): string
    {
        return 'Franz Kafka';
    }
}
