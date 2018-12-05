<?php


namespace TheCodingMachine\GraphQL\Controllers\Fixtures\Integration\Models;


class Contact
{
    /**
     * @var string
     */
    private $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    public function getManager(): ?Contact
    {
        return null;
    }
}
