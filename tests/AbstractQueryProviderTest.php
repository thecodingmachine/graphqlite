<?php


namespace TheCodingMachine\GraphQL\Controllers;

use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\AnnotationReader as DoctrineAnnotationReader;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\OutputType;
use GraphQL\Type\Definition\StringType;
use GraphQL\Type\Definition\Type;
use Mouf\Picotainer\Picotainer;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Component\Cache\Simple\ArrayCache;
use TheCodingMachine\GraphQL\Controllers\Fixtures\TestObject;
use TheCodingMachine\GraphQL\Controllers\Fixtures\TestObject2;
use TheCodingMachine\GraphQL\Controllers\Mappers\CannotMapTypeException;
use TheCodingMachine\GraphQL\Controllers\Mappers\RecursiveTypeMapper;
use TheCodingMachine\GraphQL\Controllers\Mappers\RecursiveTypeMapperInterface;
use TheCodingMachine\GraphQL\Controllers\Mappers\TypeMapperInterface;
use TheCodingMachine\GraphQL\Controllers\Containers\EmptyContainer;
use TheCodingMachine\GraphQL\Controllers\Containers\BasicAutoWiringContainer;
use TheCodingMachine\GraphQL\Controllers\Reflection\CachedDocBlockFactory;
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
    private $typeGenerator;
    private $controllerQueryProviderFactory;
    private $annotationReader;

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

                public function mapClassToType(string $className, RecursiveTypeMapperInterface $recursiveTypeMapper): ObjectType
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

                /**
                 * Returns the list of classes that have matching input GraphQL types.
                 *
                 * @return string[]
                 */
                public function getSupportedClasses(): array
                {
                    return [TestObject::class, TestObject2::class];
                }

                /**
                 * Returns a GraphQL type by name (can be either an input or output type)
                 *
                 * @param string $typeName The name of the GraphQL type
                 * @return Type&(InputType|OutputType)
                 * @throws CannotMapTypeException
                 */
                public function mapNameToType(string $typeName, RecursiveTypeMapperInterface $recursiveTypeMapper): Type
                {
                    switch ($typeName) {
                        case 'TestObject':
                            return $this->testObjectType;
                        case 'TestObject2':
                            return $this->testObjectType2;
                        default:
                            throw CannotMapTypeException::createForName($typeName);
                    }
                }

                /**
                 * Returns true if this type mapper can map the $typeName GraphQL name to a GraphQL type.
                 *
                 * @param string $typeName The name of the GraphQL type
                 * @return bool
                 */
                public function canMapNameToType(string $typeName): bool
                {
                    return $typeName === 'TestObject' || $typeName === 'TestObject2';
                }
            }, new NamingStrategy(), new ArrayCache());
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
            $this->registry = $this->buildAutoWiringContainer(new Picotainer([
                'customOutput' => function() {
                    return new StringType();
                }
            ]));
        }
        return $this->registry;
    }

    protected function buildAutoWiringContainer(ContainerInterface $container): BasicAutoWiringContainer
    {
        return new BasicAutoWiringContainer($container);
    }

    protected function getAnnotationReader(): AnnotationReader
    {
        if ($this->annotationReader === null) {
            $this->annotationReader = new AnnotationReader(new DoctrineAnnotationReader());
        }
        return $this->annotationReader;
    }

    protected function buildControllerQueryProvider($controller)
    {
        return new ControllerQueryProvider(
            $controller,
            $this->getAnnotationReader(),
            $this->getTypeMapper(),
            $this->getHydrator(),
            new VoidAuthenticationService(),
            new VoidAuthorizationService(),
            $this->getRegistry(),
            new CachedDocBlockFactory(new ArrayCache())
        );
    }

    protected function getTypeGenerator(): TypeGenerator
    {
        if ($this->typeGenerator === null) {
            $this->typeGenerator = new TypeGenerator($this->getAnnotationReader(), $this->getControllerQueryProviderFactory());
        }
        return $this->typeGenerator;
    }

    protected function getControllerQueryProviderFactory(): ControllerQueryProviderFactory
    {
        if ($this->controllerQueryProviderFactory === null) {
            $this->controllerQueryProviderFactory = new ControllerQueryProviderFactory($this->getAnnotationReader(),
                $this->getHydrator(),
                new VoidAuthenticationService(),
                new VoidAuthorizationService(),
                $this->getRegistry(),
                new CachedDocBlockFactory(new ArrayCache()));
        }
        return $this->controllerQueryProviderFactory;
    }
}
