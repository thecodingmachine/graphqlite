<?php

namespace TheCodingMachine\GraphQLite\Fixtures\Integration\Models;

use DateTimeInterface;
use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Input;
use TheCodingMachine\GraphQLite\Annotations\Type;

#[Type, Input]
class Preferences
{
    #[Field(inputType: "Int!")]
    private int $id;

    /**
     * @var string[]
     */
    #[Field(inputType: "[String!]!")]
    private array $options;

    #[Field(inputType: "Boolean!")]
    private bool $enabled;

    #[Field(inputType: "String!")]
    private string $name;

    public function __construct(int $id, array $options, bool $enabled, string $name)
    {
        $this->id = $id;
        $this->options = $options;
        $this->enabled = $enabled;
        $this->name = $name;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
