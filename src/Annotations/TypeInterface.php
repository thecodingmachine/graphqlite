<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Annotations;

/**
 * Applies to all GraphQL "Type" annotations (Type and Input)
 */
interface TypeInterface
{
    /**
     * If the Type is handled by itself.
     */
    public function isSelfType(): bool;

    public function setClass(string $className): void;
}
