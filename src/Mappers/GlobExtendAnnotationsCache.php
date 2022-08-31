<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Mappers;

/**
 * An object containing a description of ALL extend annotations relevant to GlobTypeMapper for a given class.
 *
 * @internal
 */
class GlobExtendAnnotationsCache
{
    private ?string $extendTypeClassName = null;

    private string $extendTypeName;

    public function setExtendType(?string $className, string $typeName): void
    {
        $this->extendTypeClassName = $className;
        $this->extendTypeName = $typeName;
    }

    public function getExtendTypeClassName(): ?string
    {
        return $this->extendTypeClassName;
    }

    public function getExtendTypeName(): string
    {
        return $this->extendTypeName;
    }
}
