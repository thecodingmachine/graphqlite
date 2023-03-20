<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Mappers;

/**
 * An object containing a description of ALL extend annotations relevant to GlobTypeMapper for a given class.
 *
 * @internal
 */
final class GlobExtendAnnotationsCache
{
    public function __construct(
        private string|null $extendTypeClassName,
        private string $extendTypeName,
    )
    {
    }

    public function getExtendTypeClassName(): string|null
    {
        return $this->extendTypeClassName;
    }

    public function getExtendTypeName(): string
    {
        return $this->extendTypeName;
    }
}
