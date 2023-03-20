<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Middlewares;

use TheCodingMachine\GraphQLite\Fixtures\Integration\Models\Contact;
use TheCodingMachine\GraphQLite\Fixtures\Integration\Types\ExtendedContactType;

/**
 * Resolves a field's value on a type.
 *
 * @internal
 */
interface ResolverInterface
{
    public function toString(): string;

    /**
     * Returns the object that the field will be resolved on. For example, when resolving
     * the {@see ExtendedContactType::uppercaseName()} field, the source is a {@see Contact}
     * object, but execution source will be an instance of {@see ExtendedContactType}.
     */
    public function executionSource(object|null $source): object;

    public function __invoke(object|null $source, mixed ...$args): mixed;
}
