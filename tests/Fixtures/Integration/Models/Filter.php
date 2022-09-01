<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\Integration\Models;

use TheCodingMachine\GraphQLite\Annotations\Factory;

class Filter
{
    /**
     * @param string[]|int[] $values
     */
    public function __construct(private array $values)
    {
    }

    /**
     * @return string[]|int[]
     */
    public function getValues(): array
    {
        return $this->values;
    }

    /**
     * @param string[]|int[] $values
     */
    public function mergeValues(array $values): void
    {
        $this->values = [...$this->values, ...$values];
    }

    /**
     * @param string[] $values
     *
     * @Factory()
     */
    public static function create(array $values = []): self
    {
        return new self($values);
    }
}
