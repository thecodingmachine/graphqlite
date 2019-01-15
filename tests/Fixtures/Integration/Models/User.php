<?php


namespace TheCodingMachine\GraphQL\Controllers\Fixtures\Integration\Models;

use TheCodingMachine\GraphQL\Controllers\Annotations\Field;
use TheCodingMachine\GraphQL\Controllers\Annotations\Type;

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
