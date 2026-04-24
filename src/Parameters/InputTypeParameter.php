<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Parameters;

use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use TheCodingMachine\GraphQLite\Types\ArgumentResolver;
use TheCodingMachine\GraphQLite\Types\ResolvableMutableInputObjectType;

use function array_key_exists;

class InputTypeParameter implements InputTypeParameterInterface
{
    public function __construct(
        private readonly string $name,
        private readonly InputType&Type $type,
        private readonly string|null $description,
        private readonly bool $hasDefaultValue,
        private readonly mixed $defaultValue,
        private readonly bool $defaultValueImplicit,
        private readonly ArgumentResolver $argumentResolver,
    )
    {
    }

    /** @param array<string, mixed> $args */
    public function resolve(object|null $source, array $args, mixed $context, ResolveInfo $info): mixed
    {
        if (array_key_exists($this->name, $args)) {
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
        // Unfortunately, we can't treat Undefined as a regular kind of default value. In this context,
        // $defaultValue refers to the default value on GraphQL level - e.g. the value that's printed
        // into the schema, returned in introspection and substituted by webonyx/graphql when a GraphQL
        // query is executed. Unlike regular defaults, this one shouldn't be treated as such -
        // because GraphQL itself doesn't have a concept of undefined values, at least not on Schema level.
        // It would fail to serialize during printing/introspection.
        return $this->hasDefaultValue && ! $this->defaultValueImplicit;
    }

    public function getDefaultValue(): mixed
    {
        return ! $this->defaultValueImplicit ? $this->defaultValue : null;
    }

    public function getDescription(): string
    {
        return $this->description ?? '';
    }
}
