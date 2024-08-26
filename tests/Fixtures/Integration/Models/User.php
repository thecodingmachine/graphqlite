<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\Integration\Models;

use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Type;

#[Type]
class User extends Contact
{
    public function __construct(string $name, private readonly string $email)
    {
        parent::__construct($name);
    }

    #[Field]
    public function getEmail(): string
    {
        return $this->email;
    }
}
