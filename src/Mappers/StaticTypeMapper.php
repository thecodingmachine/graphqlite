<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Mappers;

use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\NamedType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\OutputType;
use GraphQL\Type\Definition\Type;
use TheCodingMachine\GraphQLite\Mappers\Proxys\MutableInterfaceTypeAdapter;
use TheCodingMachine\GraphQLite\Mappers\Proxys\MutableObjectTypeAdapter;
use TheCodingMachine\GraphQLite\Types\MutableInterface;
use TheCodingMachine\GraphQLite\Types\MutableInterfaceType;
use TheCodingMachine\GraphQLite\Types\MutableObjectType;
use TheCodingMachine\GraphQLite\Types\ResolvableMutableInputInterface;

use function array_keys;
use function array_map;
use function array_reduce;

/**
 * A simple implementation of the TypeMapperInterface that expects mapping to be passed in a setter.
 */
final class StaticTypeMapper implements TypeMapperInterface
{
    /** @var array<string,MutableObjectType|MutableInterfaceType> */
    private readonly array $types;

    /** @var array<string,Type&((ResolvableMutableInputInterface&InputObjectType)|MutableObjectType|MutableInterfaceType)> */
    private readonly array $notMappedTypes;

    /**
     * @param array<string,ObjectType|InterfaceType> $types An array mapping a fully qualified class name to the matching TypeInterface
     * @param array<string,ResolvableMutableInputInterface &InputObjectType> $inputTypes An array mapping a fully qualified class name to the matching InputTypeInterface
     * @param array<int,Type&((ResolvableMutableInputInterface&InputObjectType)|MutableObjectType|MutableInterfaceType)> $notMappedTypes An array containing ObjectType or ResolvableMutableInputInterface instances that are not mapped by default to any class.
     *                              ObjectType not linked to any type by default will have to be accessed using the outputType attribute of the annotations.
     */
    public function __construct(
        array $types = [],
        private readonly array $inputTypes = [],
        array $notMappedTypes = [],
    )
    {
        $this->types = array_map(fn (ObjectType|InterfaceType $type) => $this->castOutputTypeToMutable($type), $types);
        $this->notMappedTypes = array_reduce($notMappedTypes, function ($result, Type $type) {
            if ($type instanceof ObjectType || $type instanceof InterfaceType) {
                $type = $this->castOutputTypeToMutable($type);
            }

            $result[$type->name] = $type;

            return $result;
        }, []);
    }

    private function castOutputTypeToMutable(ObjectType|InterfaceType $type): MutableObjectType|MutableInterfaceType
    {
        if ($type instanceof ObjectType && ! $type instanceof MutableObjectType) {
            return new MutableObjectTypeAdapter($type);
        }
        if ($type instanceof InterfaceType && ! $type instanceof MutableInterfaceType) {
            return new MutableInterfaceTypeAdapter($type);
        }
        return $type;
    }

    /**
     * Returns true if this type mapper can map the $className FQCN to a GraphQL type.
     */
    public function canMapClassToType(string $className): bool
    {
        return isset($this->types[$className]);
    }

    /**
     * Maps a PHP fully qualified class name to a GraphQL type.
     *
     * @param class-string<object> $className
     *
     * @throws CannotMapTypeException
     */
    public function mapClassToType(string $className, OutputType|null $subType): MutableInterface
    {
        // TODO: add support for $subType
        if ($subType !== null) {
            throw CannotMapTypeException::createForType($className);
        }

        if (isset($this->types[$className])) {
            return $this->types[$className];
        }
        throw CannotMapTypeException::createForType($className);
    }

    /**
     * Returns the list of classes that have matching input GraphQL types.
     *
     * @return array<int,string>
     */
    public function getSupportedClasses(): array
    {
        return array_keys($this->types);
    }

    /**
     * Returns true if this type mapper can map the $className FQCN to a GraphQL input type.
     */
    public function canMapClassToInputType(string $className): bool
    {
        return isset($this->inputTypes[$className]);
    }

    /**
     * Maps a PHP fully qualified class name to a GraphQL input type.
     *
     * @param class-string<object> $className
     *
     * @return ResolvableMutableInputInterface&InputObjectType
     *
     * @throws CannotMapTypeExceptionInterface
     */
    public function mapClassToInputType(string $className): ResolvableMutableInputInterface
    {
        if (isset($this->inputTypes[$className])) {
            return $this->inputTypes[$className];
        }
        throw CannotMapTypeException::createForInputType($className);
    }

    /**
     * Returns a GraphQL type by name (can be either an input or output type)
     *
     * @param string $typeName The name of the GraphQL type
     *
     * @return NamedType&Type&((ResolvableMutableInputInterface&InputObjectType)|MutableObjectType|MutableInterfaceType)
     *
     * @throws CannotMapTypeExceptionInterface
     */
    public function mapNameToType(string $typeName): Type&NamedType
    {
        if (isset($this->notMappedTypes[$typeName])) {
            return $this->notMappedTypes[$typeName];
        }
        foreach ($this->types as $type) {
            if ($type->name === $typeName) {
                return $type;
            }
        }
        foreach ($this->inputTypes as $inputType) {
            if ($inputType->name === $typeName) {
                return $inputType;
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
        foreach ($this->types as $type) {
            if ($type->name === $typeName) {
                return true;
            }
        }
        foreach ($this->inputTypes as $inputType) {
            if ($inputType->name === $typeName) {
                return true;
            }
        }

        return isset($this->notMappedTypes[$typeName]);
    }

    /**
     * Returns true if this type mapper can extend an existing type for the $className FQCN
     *
     * @param MutableInterface&(MutableObjectType|MutableInterfaceType) $type
     */
    public function canExtendTypeForClass(string $className, MutableInterface $type): bool
    {
        return false;
    }

    /**
     * Extends the existing GraphQL type that is mapped to $className.
     *
     * @param class-string<object> $className
     * @param MutableInterface&(MutableObjectType|MutableInterfaceType) $type
     *
     * @throws CannotMapTypeExceptionInterface
     */
    public function extendTypeForClass(string $className, MutableInterface $type): void
    {
        throw CannotMapTypeException::createForExtendType($className, $type);
    }

    /**
     * Returns true if this type mapper can extend an existing type for the $typeName GraphQL type
     *
     * @param MutableInterface&(MutableObjectType|MutableInterfaceType) $type
     */
    public function canExtendTypeForName(string $typeName, MutableInterface $type): bool
    {
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
        throw CannotMapTypeException::createForExtendName($typeName, $type);
    }

    /**
     * Returns true if this type mapper can decorate an existing input type for the $typeName GraphQL input type
     */
    public function canDecorateInputTypeForName(string $typeName, ResolvableMutableInputInterface $type): bool
    {
        return false;
    }

    /**
     * Decorates the existing GraphQL input type that is mapped to the $typeName GraphQL input type.
     *
     * @param ResolvableMutableInputInterface&InputObjectType $type
     *
     * @throws CannotMapTypeException
     */
    public function decorateInputTypeForName(string $typeName, ResolvableMutableInputInterface $type): void
    {
        throw CannotMapTypeException::createForDecorateName($typeName, $type);
    }
}
