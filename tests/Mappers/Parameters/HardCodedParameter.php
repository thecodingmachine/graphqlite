<?php


namespace TheCodingMachine\GraphQLite\Mappers\Parameters;


use GraphQL\Type\Definition\ResolveInfo;
use TheCodingMachine\GraphQLite\Parameters\ParameterInterface;

class HardCodedParameter implements ParameterInterface
{
    private $value;

    /**
     * @param mixed $value
     */
    public function __construct($value)
    {

        $this->value = $value;
    }

    public function resolve(?object $source, array $args,mixed $context, ResolveInfo $info):mixed
    {
        return $this->value;
    }
}