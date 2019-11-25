<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Mappers;

use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\NamedType;
use GraphQL\Type\Definition\OutputType;
use GraphQL\Type\Definition\Type;
use TheCodingMachine\GraphQLite\Types\MutableInterface;
use TheCodingMachine\GraphQLite\Types\MutableInterfaceType;
use TheCodingMachine\GraphQLite\Types\MutableObjectType;
use TheCodingMachine\GraphQLite\Types\ResolvableMutableInputInterface;
use function array_map;
use function array_merge;
use function array_unique;

class CompositeTypeMapper implements TypeMapperInterface
{
    /** @var TypeMapperInterface[] */
    private $typeMappers = [];

    /**
     * The cache of supported classes.
     *
     * @var string[]
     */
    private $supportedClasses;

    public function addTypeMapper(TypeMapperInterface $typeMapper): void
    {
        $this->typeMappers[] = $typeMapper;
    }

    /**
     * Returns true if this type mapper can map the $className FQCN to a GraphQL type.
     */
    public function canMapClassToType(string $className): bool
    {
        foreach ($this->typeMappers as $typeMapper) {
            if ($typeMapper->canMapClassToType($className)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Maps a PHP fully qualified class name to a GraphQL type.
     *
     * @param (OutputType&Type)|null $subType
     *
     * @throws CannotMapTypeExceptionInterface
     */
    public function mapClassToType(string $className, ?OutputType $subType): MutableInterface
    {
        foreach ($this->typeMappers as $typeMapper) {
            if ($typeMapper->canMapClassToType($className)) {
                return $typeMapper->mapClassToType($className, $subType);
            }
        }
        throw CannotMapTypeException::createForType($className);
    }

    /**
     * Returns the list of classes that have matching input GraphQL types.
     *
     * @return string[]
     */
    public function getSupportedClasses(): array
    {
        if ($this->supportedClasses === null) {
            if ($this->typeMappers === []) {
                $this->supportedClasses = [];
            } else {
                $supportedClassesArrays = array_map(static function (TypeMapperInterface $typeMapper) {
                    return $typeMapper->getSupportedClasses();
                }, $this->typeMappers);
                $this->supportedClasses = array_unique(array_merge(...$supportedClassesArrays));
            }
        }

        return $this->supportedClasses;
    }

    /**
     * Returns true if this type mapper can map the $className FQCN to a GraphQL input type.
     */
    public function canMapClassToInputType(string $className): bool
    {
        foreach ($this->typeMappers as $typeMapper) {
            if ($typeMapper->canMapClassToInputType($className)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Maps a PHP fully qualified class name to a GraphQL input type.
     *
     * @return ResolvableMutableInputInterface&InputObjectType
     *
     * @throws CannotMapTypeExceptionInterface
     */
    public function mapClassToInputType(string $className): ResolvableMutableInputInterface
    {
        foreach ($this->typeMappers as $typeMapper) {
            if ($typeMapper->canMapClassToInputType($className)) {
                return $typeMapper->mapClassToInputType($className);
            }
        }
        throw CannotMapTypeException::createForInputType($className);
    }

    /**
     * Returns a GraphQL type by name (can be either an input or output type)
     *
     * @param string $typeName The name of the GraphQL type
     *
     * @return NamedType&Type&((ResolvableMutableInputInterface&InputObjectType)|MutableInterface)
     */
    public function mapNameToType(string $typeName): Type
    {
        foreach ($this->typeMappers as $typeMapper) {
            if ($typeMapper->canMapNameToType($typeName)) {
                return $typeMapper->mapNameToType($typeName);
            }
        }
        throw CannotMapTypeException::createForName($typeName);
    }

    /**
     * Returns true if this type mapper can map the $typeName GraphQL name to a GraphQL type.
     *
     * @param string $typeName The name of the GraphQL type
     */
    public function canMapNameToType(string $typeName): bool
    {
        foreach ($this->typeMappers as $typeMapper) {
            if ($typeMapper->canMapNameToType($typeName)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns true if this type mapper can extend an existing type for the $className FQCN
     *
     * @param MutableInterface&(MutableObjectType|MutableInterfaceType) $type
     */
    public function canExtendTypeForClass(string $className, MutableInterface $type): bool
    {
        foreach ($this->typeMappers as $typeMapper) {
            if ($typeMapper->canExtendTypeForClass($className, $type)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Extends the existing GraphQL type that is mapped to $className.
     *
     * @param MutableInterface&(MutableObjectType|MutableInterfaceType) $type
     *
     * @throws CannotMapTypeExceptionInterface
     */
    public function extendTypeForClass(string $className, MutableInterface $type): void
    {
        foreach ($this->typeMappers as $typeMapper) {
            if (! $typeMapper->canExtendTypeForClass($className, $type)) {
                continue;
            }

            $typeMapper->extendTypeForClass($className, $type);
        }
    }

    /**
     * Returns true if this type mapper can extend an existing type for the $typeName GraphQL type
     *
     * @param MutableInterface&(MutableObjectType|MutableInterfaceType) $type
     */
    public function canExtendTypeForName(string $typeName, MutableInterface $type): bool
    {
        foreach ($this->typeMappers as $typeMapper) {
            if ($typeMapper->canExtendTypeForName($typeName, $type)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Extends the existing GraphQL type that is mapped to the $typeName GraphQL type.
     *
     * @param MutableInterface&(MutableObjectType|MutableInterfaceType) $type
     *
     * @throws CannotMapTypeExceptionInterface
     */
    public function extendTypeForName(string $typeName, MutableInterface $type): void
    {
        foreach ($this->typeMappers as $typeMapper) {
            if (! $typeMapper->canExtendTypeForName($typeName, $type)) {
                continue;
            }

            $typeMapper->extendTypeForName($typeName, $type);
        }
    }

    /**
     * Returns true if this type mapper can decorate an existing input type for the $typeName GraphQL input type
     */
    public function canDecorateInputTypeForName(string $typeName, ResolvableMutableInputInterface $type): bool
    {
        foreach ($this->typeMappers as $typeMapper) {
            if ($typeMapper->canDecorateInputTypeForName($typeName, $type)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Decorates the existing GraphQL input type that is mapped to the $typeName GraphQL input type.
     *
     * @param ResolvableMutableInputInterface&InputObjectType $type
     */
    public function decorateInputTypeForName(string $typeName, ResolvableMutableInputInterface $type): void
    {
        foreach ($this->typeMappers as $typeMapper) {
            if (! $typeMapper->canDecorateInputTypeForName($typeName, $type)) {
                continue;
            }

            $typeMapper->decorateInputTypeForName($typeName, $type);
        }
    }
}
