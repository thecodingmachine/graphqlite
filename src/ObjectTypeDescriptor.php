<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite;

use ReflectionClass;
use TheCodingMachine\GraphQLite\Directives\ObjectTypeDirective;
use TheCodingMachine\GraphQLite\Types\MutableObjectType;
use TheCodingMachine\GraphQLite\Utils\Cloneable;

/**
 * Carries a {@see MutableObjectType} through the {@see Middlewares\ObjectTypeMiddlewarePipe} so
 * {@see Directives\ObjectTypeDirective}s can decorate the type before it's registered.
 */
class ObjectTypeDescriptor
{
    use Cloneable;

    /**
     * @param ReflectionClass<object> $reflectionClass
     * @param list<ObjectTypeDirective> $directives
     */
    public function __construct(
        private readonly ReflectionClass $reflectionClass,
        private readonly MutableObjectType $type,
        private readonly array $directives = [],
    ) {
    }

    /** @return ReflectionClass<object> */
    public function getReflectionClass(): ReflectionClass
    {
        return $this->reflectionClass;
    }

    public function getType(): MutableObjectType
    {
        return $this->type;
    }

    /** @return list<ObjectTypeDirective> */
    public function getDirectives(): array
    {
        return $this->directives;
    }

    /** @param list<ObjectTypeDirective> $directives */
    public function withDirectives(array $directives): self
    {
        return $this->with(directives: $directives);
    }
}
