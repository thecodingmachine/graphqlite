<?php


namespace TheCodingMachine\GraphQLite\Fixtures\Integration\Models;


use function array_merge;
use TheCodingMachine\GraphQLite\Annotations\Factory;

class Filter
{
    /**
     * @var string[]|int[]
     */
    private $values;

    /**
     * Filter constructor.
     * @param string[]|int[] $values
     */
    public function __construct(array $values)
    {
        $this->values = $values;
    }

    /**
     * @return string[]|int[]
     */
    public function getValues(): array
    {
        return $this->values;
    }

    public function mergeValues(array $values)
    {
        $this->values = array_merge($this->values, $values);
    }

    /**
     * @Factory()
     * @param string[] $values
     */
    public static function create(array $values = []): self
    {
        return new self($values);
    }
}
