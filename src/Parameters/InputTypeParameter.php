<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Parameters;

use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use TheCodingMachine\GraphQLite\Types\ArgumentResolver;
use TheCodingMachine\GraphQLite\Types\ResolvableMutableInputObjectType;

class InputTypeParameter implements InputTypeParameterInterface
{
    /** @var string */
    private $name;
    /** @var InputType&Type */
    private $type;
    /** @var bool */
    private $doesHaveDefaultValue;
    /** @var mixed */
    private $defaultValue;
    /** @var ArgumentResolver */
    private $argumentResolver;

    /**
     * @param InputType&Type $type
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

        // Special case: if an argument is not provided for a factory BUT the factory can be instantiated without
        // passing any argument. Let's resolve that.
        if ($this->type instanceof ResolvableMutableInputObjectType && $this->type->isInstantiableWithoutParameters()) {
            return $this->argumentResolver->resolve($source, [], $context, $info, $this->type);
        }

        throw MissingArgumentException::create($this->name);
    }

    public function getType(): InputType
    {
        return $this->type;
    }

    public function hasDefaultValue(): bool
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
