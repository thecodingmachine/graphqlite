<?php


namespace TheCodingMachine\GraphQLite\Fixtures\Integration\Models;

use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Type;

/**
 * @Type()
 */
class User extends Contact
{
    /**
     * @var string
     */
    private $email;

    public function __construct(string $name, string $email)
    {
        parent::__construct($name);
        $this->email = $email;
    }

    /**
     * @Field(name="email")
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }
}
