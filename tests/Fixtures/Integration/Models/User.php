<?php


namespace TheCodingMachine\GraphQL\Controllers\Fixtures\Integration\Models;


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
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }
}
