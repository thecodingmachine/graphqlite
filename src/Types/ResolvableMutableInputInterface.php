<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Types;

use GraphQL\Type\Definition\ResolveInfo;

/**
 * A GraphQL input object that can be resolved
 */
interface ResolvableMutableInputInterface extends MutableInputInterface
{
    /**
     * Resolves the arguments into an object.
     *
     * @param mixed[] $args
     * @param mixed   $context
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint
     */
    public function resolve(?object $source, array $args, $context, ResolveInfo $resolveInfo): object;

    /**
     * Decorates the call to the resolver with the $decorator.
     * The $decorator MUST receive the decorated object as first parameter and MUST return an object of a compatible type.
     * Additional parameters can be used to add fields.
     */
    public function decorate(callable $decorator): void;
}
