<?php
namespace TheCodingMachine\GraphQL\Controllers\Types;


use TheCodingMachine\GraphQL\Controllers\GraphQLException;

/**
 * A class that maps to the GraphQL ID type.
 */
class ID
{
    private $value;

    public function __construct($value)
    {
        if (! is_scalar($value) && (! is_object($value) || ! method_exists($value, '__toString'))) {
            throw new GraphQLException('ID constructor cannot be passed a non scalar value.');
        }
        $this->value = $value;
    }

    /**
     * @return bool|float|int|string
     */
    public function val()
    {
        return $this->value;
    }

    public function __toString()
    {
        return (string) $this->value;
    }
}
