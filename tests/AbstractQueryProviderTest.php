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
use TheCodingMachine\GraphQL\Controllers\Fixtures\TestObjectWithRecursiveList;
use TheCodingMachine\GraphQL\Controllers\Fixtures\Types\TestFactory;
use TheCodingMachine\GraphQL\Controllers\Hydrators\HydratorInterface;
use TheCodingMachine\GraphQL\Controllers\Mappers\CannotMapTypeException;
use TheCodingMachine\GraphQL\Controllers\Mappers\RecursiveTypeMapper;
use TheCodingMachine\GraphQL\Controllers\Mappers\RecursiveTypeMapperInterface;
use TheCodingMachine\GraphQL\Controllers\Mappers\TypeMapperInterface;
use TheCodingMachine\GraphQL\Controllers\Containers\EmptyContainer;
use TheCodingMachine\GraphQL\Controllers\Containers\BasicAutoWiringContainer;
use TheCodingMachine\GraphQL\Controllers\Reflection\CachedDocBlockFactory;
use TheCodingMachine\GraphQL\Controllers\Security\VoidAuthenticationService;
use TheCodingMachine\GraphQL\Controllers\Security\VoidAuthorizationService;
use TheCodingMachine\GraphQL\Controllers\Types\ResolvableInputObjectType;

abstract class AbstractQueryProviderTest extends TestCase
{
    private $testObjectType;
    private $testObjectType2;
    private $inputTestObjectType;
    private $inputTestObjectType2;
    private $typeMapper;
    private $hydrator;
    private $registry;
    private $typeGenerator;
    private $inputTypeGenerator;
    private $inputTypeUtils;
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
                'name'    => 'TestObjectInput',
                'fields'  => [
                    'test'   => Type::string(),
                ],
            ]);
        }
        return $this->inputTestObjectType;
    }

    /*protected function getInputTestObjectType2()
    {
        if ($this->inputTestObjectType2 === null) {
            $this->inputTestObjectType2 = new ResolvableInputObjectType('TestObjectInput2', $this->getControllerQueryProviderFactory(), $this->getTypeMapper(), new TestFactory(), 'myRecursiveFactory', $this->getHydrator(), null);
        }
        return $this->inputTestObjectType2;
    }*/

    protected function getTypeMapper()
    {
        if ($this->typeMapper === null) {
            $this->typeMapper = new RecursiveTypeMapper(new class($this->getTestObjectType(), $this->getTestObjectType2(), $this->getInputTestObjectType()/*, $this->getInputTestObjectType2()*/) implements TypeMapperInterface {
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
                /**
                 * @var InputObjectType
                 */
//                private $inputTestObjectType2;

                public function __construct(ObjectType $testObjectType, ObjectType $testObjectType2, InputObjectType $inputTestObjectType/*, InputObjectType $inputTestObjectType2*/)
                {
                    $this->testObjectType = $testObjectType;
                    $this->testObjectType2 = $testObjectType2;
                    $this->inputTestObjectType = $inputTestObjectType;
                    //$this->inputTestObjectType2 = $inputTestObjectType2;
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

                public function mapClassToInputType(string $className, RecursiveTypeMapperInterface $recursiveTypeMapper): InputObjectType
                {
                    if ($className === TestObject::class) {
                        return $this->inputTestObjectType;
                    } /*elseif ($className === TestObjectWithRecursiveList::class) {
                        return $this->inputTestObjectType2;
                    } */else {
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

    protected function getHydrator(): HydratorInterface
    {
        if ($this->hydrator === null) {
            $this->hydrator = new class implements HydratorInterface {
                public function hydrate(array $data, InputObjectType $type)
                {
                    return new TestObject($data['test']);
                }

                public function canHydrate(array $data, InputObjectType $type): bool
                {
                    return true;
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

    protected function buildFieldsBuilder(): FieldsBuilder
    {
        return new FieldsBuilder(
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
            $this->typeGenerator = new TypeGenerator($this->getAnnotationReader(), $this->getControllerQueryProviderFactory(), new NamingStrategy());
        }
        return $this->typeGenerator;
    }

    protected function getInputTypeGenerator(): InputTypeGenerator
    {
        if ($this->inputTypeGenerator === null) {
            $this->inputTypeGenerator = new InputTypeGenerator($this->getInputTypeUtils(), $this->getControllerQueryProviderFactory(), $this->getHydrator());
        }
        return $this->inputTypeGenerator;
    }

    protected function getInputTypeUtils(): InputTypeUtils
    {
        if ($this->inputTypeUtils === null) {
            $this->inputTypeUtils = new InputTypeUtils($this->getAnnotationReader(), new NamingStrategy());
        }
        return $this->inputTypeUtils;
    }

    protected function getControllerQueryProviderFactory(): FieldsBuilderFactory
    {
        if ($this->controllerQueryProviderFactory === null) {
            $this->controllerQueryProviderFactory = new FieldsBuilderFactory($this->getAnnotationReader(),
                $this->getHydrator(),
                new VoidAuthenticationService(),
                new VoidAuthorizationService(),
                $this->getRegistry(),
                new CachedDocBlockFactory(new ArrayCache()));
        }
        return $this->controllerQueryProviderFactory;
    }
}
