<?php


namespace TheCodingMachine\GraphQL\Controllers;

use Doctrine\Common\Annotations\AnnotationReader;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\OutputType;
use GraphQL\Type\Definition\Type;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use TheCodingMachine\GraphQL\Controllers\Fixtures\TestObject;
use TheCodingMachine\GraphQL\Controllers\Fixtures\TestObject2;
use TheCodingMachine\GraphQL\Controllers\Mappers\CannotMapTypeException;
use TheCodingMachine\GraphQL\Controllers\Mappers\RecursiveTypeMapper;
use TheCodingMachine\GraphQL\Controllers\Mappers\TypeMapperInterface;
use TheCodingMachine\GraphQL\Controllers\Registry\EmptyContainer;
use TheCodingMachine\GraphQL\Controllers\Registry\Registry;
use TheCodingMachine\GraphQL\Controllers\Security\VoidAuthenticationService;
use TheCodingMachine\GraphQL\Controllers\Security\VoidAuthorizationService;

abstract class AbstractQueryProviderTest extends TestCase
{
    private $testObjectType;
    private $testObjectType2;
    private $inputTestObjectType;
    private $typeMapper;
    private $hydrator;
    private $registry;

    protected function getTestObjectType()
    {
        if ($this->testObjectType === null) {
            $this->testObjectType = new ObjectType([
                'name'    => 'TestObject',
                'fields'  => [
                    'test'   => Type::string(),
                ],
            ]);
        }
        return $this->testObjectType;
    }

    protected function getTestObjectType2()
    {
        if ($this->testObjectType2 === null) {
            $this->testObjectType2 = new ObjectType([
                'name'    => 'TestObject2',
                'fields'  => [
                    'test'   => Type::string(),
                ],
            ]);
        }
        return $this->testObjectType2;
    }

    protected function getInputTestObjectType()
    {
        if ($this->inputTestObjectType === null) {
            $this->inputTestObjectType = new InputObjectType([
                'name'    => 'TestObject',
                'fields'  => [
                    'test'   => Type::string(),
                ],
            ]);
        }
        return $this->inputTestObjectType;
    }

    protected function getTypeMapper()
    {
        if ($this->typeMapper === null) {
            $this->typeMapper = new RecursiveTypeMapper(new class($this->getTestObjectType(), $this->getTestObjectType2(), $this->getInputTestObjectType()) implements TypeMapperInterface {
                /**
                 * @var ObjectType
                 */
                private $testObjectType;
                /**
                 * @var ObjectType
                 */
                private $testObjectType2;
                /**
                 * @var InputObjectType
                 */
                private $inputTestObjectType;

                public function __construct(ObjectType $testObjectType, ObjectType $testObjectType2, InputObjectType $inputTestObjectType)
                {
                    $this->testObjectType = $testObjectType;
                    $this->testObjectType2 = $testObjectType2;
                    $this->inputTestObjectType = $inputTestObjectType;
                }

                public function mapClassToType(string $className): OutputType
                {
                    if ($className === TestObject::class) {
                        return $this->testObjectType;
                    } elseif ($className === TestObject2::class) {
                        return $this->testObjectType2;
                    } else {
                        throw CannotMapTypeException::createForType($className);
                    }
                }

                public function mapClassToInputType(string $className): InputType
                {
                    if ($className === TestObject::class) {
                        return $this->inputTestObjectType;
                    } else {
                        throw CannotMapTypeException::createForInputType($className);
                    }
                }

                public function canMapClassToType(string $className): bool
                {
                    return $className === TestObject::class || $className === TestObject2::class;
                }

                /**
                 * Returns true if this type mapper can map the $className FQCN to a GraphQL input type.
                 *
                 * @param string $className
                 * @return bool
                 */
                public function canMapClassToInputType(string $className): bool
                {
                    return $className === TestObject::class || $className === TestObject2::class;
                }
            });
        }
        return $this->typeMapper;
    }

    protected function getHydrator()
    {
        if ($this->hydrator === null) {
            $this->hydrator = new class implements HydratorInterface {
                public function hydrate(array $data, InputType $type)
                {
                    return new TestObject($data['test']);
                }
            };
        }
        return $this->hydrator;
    }

    protected function getRegistry()
    {
        if ($this->registry === null) {
            $this->registry = $this->buildRegistry(new EmptyContainer());
        }
        return $this->registry;
    }

    protected function buildRegistry(ContainerInterface $container)
    {
        $reader = new AnnotationReader();
        return new Registry($container,
                new VoidAuthorizationService(),
                new VoidAuthenticationService(),
                $reader,
                $this->getTypeMapper(),
                $this->getHydrator());
    }
}
