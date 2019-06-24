<?php


namespace TheCodingMachine\GraphQLite\Mappers\Parameters\Result;


use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\OutputType;
use GraphQL\Type\Definition\Type;


class Success implements Result
{
    /**
     * @var Type
     */
    private $type;

    /**
     * Success constructor.
     * @param (OutputType&GraphQLType)|(InputType&GraphQLType) $type
     */
    public function __construct(Type $type)
    {
        $this->type = $type;
    }

    /**
     * @return Type
     */
    public function getType(): Type
    {
        return $this->type;
    }

    /**
     * If the result is an error, an exception is thrown
     */
    public function throwIfError(): void
    {
        // Do not throw anything, this is a success.
    }
}
