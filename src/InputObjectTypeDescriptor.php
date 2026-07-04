<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite;

use ReflectionClass;
use TheCodingMachine\GraphQLite\Directives\InputObjectTypeDirective;
use TheCodingMachine\GraphQLite\Types\MutableInputObjectType;
use TheCodingMachine\GraphQLite\Utils\Cloneable;

/**
 * Carries a {@see MutableInputObjectType} through the
 * {@see Middlewares\InputObjectTypeMiddlewarePipe} so {@see Directives\InputObjectTypeDirective}s can
 * decorate the type before it's registered.
 *
 * Both `#[Input]` types (`InputType`) and `#[Factory]` types (`ResolvableMutableInputObjectType`)
 * extend `MutableInputObjectType`, so either works here.
 */
class InputObjectTypeDescriptor
{
    use Cloneable;

    /**
     * @param ReflectionClass<object> $reflectionClass
     * @param list<InputObjectTypeDirective> $directives
     */
    public function __construct(
        private readonly ReflectionClass $reflectionClass,
        private readonly MutableInputObjectType $type,
        private readonly array $directives = [],
    ) {
    }

    /** @return ReflectionClass<object> */
    public function getReflectionClass(): ReflectionClass
    {
        return $this->reflectionClass;
    }

    public function getType(): MutableInputObjectType
    {
        return $this->type;
    }

    /** @return list<InputObjectTypeDirective> */
    public function getDirectives(): array
    {
        return $this->directives;
    }

    /** @param list<InputObjectTypeDirective> $directives */
    public function withDirectives(array $directives): self
    {
        return $this->with(directives: $directives);
    }
}
