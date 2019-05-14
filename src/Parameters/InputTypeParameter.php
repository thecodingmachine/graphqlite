<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Parameters;

use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\ResolveInfo;
use TheCodingMachine\GraphQLite\Types\ArgumentResolver;

/**
 * Typically the first parameter of "external" fields that will be filled with the Source object.
 */
class InputTypeParameter implements ParameterInterface
{
    /** @var string */
    private $name;
    /** @var InputType */
    private $type;
    /** @var bool */
    private $doesHaveDefaultValue;
    /** @var mixed */
    private $defaultValue;
    /** @var ArgumentResolver */
    private $argumentResolver;

    /**
     * @param mixed $defaultValue
     */
    public function __construct(string $name, InputType $type, bool $hasDefaultValue, $defaultValue, ArgumentResolver $argumentResolver)
    {
        $this->name                 = $name;
        $this->type                 = $type;
        $this->doesHaveDefaultValue = $hasDefaultValue;
        $this->defaultValue         = $defaultValue;
        $this->argumentResolver     = $argumentResolver;
    }

    /**
     * @param array<string, mixed> $args
     * @param mixed                $context
     *
     * @return mixed
     */
    public function resolve(?object $source, array $args, $context, ResolveInfo $info)
    {
        if (isset($args[$this->name])) {
            return $this->argumentResolver->resolve($source, $args[$this->name], $context, $info, $this->type);
        }

        if ($this->doesHaveDefaultValue) {
            return $this->defaultValue;
        }

        throw MissingArgumentException::create($this->name);
    }

    public function getType() : InputType
    {
        return $this->type;
    }

    public function hasDefaultValue() : bool
    {
        return $this->doesHaveDefaultValue;
    }

    /**
     * @return mixed
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }
}
