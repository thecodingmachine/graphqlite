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
    public function __construct(
        private readonly string $name,
        private readonly InputType&Type $type,
        private readonly string|null $description,
        private readonly bool $hasDefaultValue,
        private readonly mixed $defaultValue,
        private readonly ArgumentResolver $argumentResolver,
    )
    {
    }

    /** @param array<string, mixed> $args */
    public function resolve(object|null $source, array $args, mixed $context, ResolveInfo $info): mixed
    {
        if (isset($args[$this->name])) {
            return $this->argumentResolver->resolve($source, $args[$this->name], $context, $info, $this->type);
        }

        if ($this->hasDefaultValue) {
            return $this->defaultValue;
        }

        // Special case: if an argument is not provided for a factory BUT the factory can be instantiated without
        // passing any argument. Let's resolve that.
        if ($this->type instanceof ResolvableMutableInputObjectType && $this->type->isInstantiableWithoutParameters()) {
            return $this->argumentResolver->resolve($source, [], $context, $info, $this->type);
        }

        throw MissingArgumentException::create($this->name);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): InputType&Type
    {
        return $this->type;
    }

    public function hasDefaultValue(): bool
    {
        return $this->hasDefaultValue;
    }

    public function getDefaultValue(): mixed
    {
        return $this->defaultValue;
    }

    public function getDescription(): string
    {
        return $this->description ?? '';
    }
}
