<?php


namespace TheCodingMachine\GraphQL\Controllers\Mappers;


use function array_flip;
use function get_parent_class;
use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\OutputType;
use GraphQL\Type\Definition\Type;
use TheCodingMachine\GraphQL\Controllers\Types\InterfaceFromObjectType;

/**
 * This class wraps a TypeMapperInterface into a RecursiveTypeMapperInterface.
 * While the wrapped class does only tests one given class, the recursive type mapper
 * tests the class and all its parents.
 */
class RecursiveTypeMapper implements RecursiveTypeMapperInterface
{
    /**
     * @var TypeMapperInterface
     */
    private $typeMapper;

    /**
     * An array mapping a class name to the MappedClass instance (useful to know if the class has children)
     *
     * @var array<string,MappedClass>|null
     */
    private $mappedClasses;

    /**
     * An array of interfaces OR object types if no interface matching.
     *
     * @var array<string,OutputType>
     */
    private $interfaces = [];

    public function __construct(TypeMapperInterface $typeMapper)
    {
        $this->typeMapper = $typeMapper;
    }

    /**
     * Returns true if this type mapper can map the $className FQCN to a GraphQL type.
     *
     * @param string $className The class name to look for (this function looks into parent classes if the class does not match a type).
     * @return bool
     */
    public function canMapClassToType(string $className): bool
    {
        return $this->findClosestMatchingParent($className) !== null;
    }

    /**
     * Maps a PHP fully qualified class name to a GraphQL type.
     *
     * @param string $className The class name to look for (this function looks into parent classes if the class does not match a type).
     * @return OutputType&Type
     * @throws CannotMapTypeException
     */
    public function mapClassToType(string $className): OutputType
    {
        $closestClassName = $this->findClosestMatchingParent($className);
        if ($closestClassName === null) {
            throw CannotMapTypeException::createForType($className);
        }
        return $this->typeMapper->mapClassToType($closestClassName);
    }

    /**
     * Returns the closest parent that can be mapped, or null if nothing can be matched.
     *
     * @param string $className
     * @return string|null
     */
    private function findClosestMatchingParent(string $className): ?string
    {
        do {
            if ($this->typeMapper->canMapClassToType($className)) {
                return $className;
            }
        } while ($className = get_parent_class($className));
        return null;
    }

    /**
     * Maps a PHP fully qualified class name to a GraphQL interface (or returns null if no interface is found).
     *
     * @param string $className The exact class name to look for (this function does not look into parent classes).
     * @return OutputType
     * @throws CannotMapTypeException
     */
    public function mapClassToInterfaceOrType(string $className): OutputType
    {
        $closestClassName = $this->findClosestMatchingParent($className);
        if ($closestClassName === null) {
            throw CannotMapTypeException::createForType($className);
        }
        if (!isset($this->interfaces[$closestClassName])) {
            $objectType = $this->typeMapper->mapClassToType($closestClassName);

            $supportedClasses = $this->getClassTree();
            if (!empty($supportedClasses[$closestClassName]->getChildren())) {
                // Cast as an interface
                $this->interfaces[$closestClassName] = new InterfaceFromObjectType($objectType, $this);
                return $this->interfaces[$closestClassName];
            } else {
                $this->interfaces[$closestClassName] = $objectType;
            }
        }
        return $this->interfaces[$closestClassName];
    }

    /**
     * @return array<string,MappedClass>
     */
    private function getClassTree(): array
    {
        if ($this->mappedClasses === null) {
            $supportedClasses = array_flip($this->typeMapper->getSupportedClasses());
            foreach ($supportedClasses as $supportedClass => $foo) {
                $this->getMappedClass($supportedClass, $supportedClasses);
            }
        }
        return $this->mappedClasses;
    }

    /**
     * @param string $className
     * @param array<string,int> $supportedClasses
     * @return MappedClass
     */
    private function getMappedClass(string $className, array $supportedClasses): MappedClass
    {
        if (!isset($this->mappedClasses[$className])) {
            $mappedClass = new MappedClass($className);
            $this->mappedClasses[$className] = $mappedClass;
            $parentClassName = $className;
            while ($parentClassName = get_parent_class($parentClassName)) {
                if (isset($supportedClasses[$parentClassName])) {
                    $parentMappedClass = $this->getMappedClass($parentClassName, $supportedClasses);
                    $mappedClass->setParent($parentMappedClass);
                    $parentMappedClass->addChild($mappedClass);
                    break;
                }
            }
        }
        return $this->mappedClasses[$className];
    }

    /**
     * Returns true if this type mapper can map the $className FQCN to a GraphQL input type.
     *
     * @param string $className
     * @return bool
     */
    public function canMapClassToInputType(string $className): bool
    {
        return $this->typeMapper->canMapClassToInputType($className);
    }

    /**
     * Maps a PHP fully qualified class name to a GraphQL input type.
     *
     * @param string $className
     * @return InputType&Type
     * @throws CannotMapTypeException
     */
    public function mapClassToInputType(string $className): InputType
    {
        return $this->typeMapper->mapClassToInputType($className);
    }
}
