<?php


namespace TheCodingMachine\GraphQLite\Mappers;

/**
 * An object containing a description of ALL extend annotations relevant to GlobTypeMapper for a given class.
 *
 * @internal
 */
class GlobExtendAnnotationsCache
{
    /**
     * @var string|null
     */
    private $extendTypeClassName;

    /**
     * @var string|null
     */
    private $extendTypeName;

    public function setExtendType(string $className, string $typeName): void
    {
        $this->extendTypeClassName = $className;
        $this->extendTypeName = $typeName;
    }

    /**
     * @return string|null
     */
    public function getExtendTypeClassName(): ?string
    {
        return $this->extendTypeClassName;
    }

    /**
     * @return string|null
     */
    public function getExtendTypeName(): ?string
    {
        return $this->extendTypeName;
    }
}
