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
use TheCodingMachine\GraphQL\Controllers\Mappers\CannotMapTypeExceptionInterface;
use TheCodingMachine\GraphQL\Controllers\Mappers\RecursiveTypeMapper;
use TheCodingMachine\GraphQL\Controllers\Mappers\RecursiveTypeMapperInterface;
use TheCodingMachine\GraphQL\Controllers\Mappers\TypeMapperInterface;
use TheCodingMachine\GraphQL\Controllers\Containers\EmptyContainer;
use TheCodingMachine\GraphQL\Controllers\Containers\BasicAutoWiringContainer;
use TheCodingMachine\GraphQL\Controllers\Reflection\CachedDocBlockFactory;
use TheCodingMachine\GraphQL\Controllers\Security\VoidAuthenticationService;
use TheCodingMachine\GraphQL\Controllers\Security\VoidAuthorizationService;
use TheCodingMachine\GraphQL\Controllers\Types\MutableObjectType;
use TheCodingMachine\GraphQL\Controllers\Types\ResolvableInputObjectType;
use TheCodingMachine\GraphQL\Controllers\Types\TypeResolver;

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
    private $typeResolver;
    private $typeRegistry;

    protected function getTestObjectType(): MutableObjectType
    {
        if ($this->testObjectType === null) {
            $this->testObjectType = new MutableObjectType([
                'name'    => 'TestObject',
                'fields'  => [
                    'test'   => Type::string(),
                ],
            ]);
        }
        return $this->testObjectType;
    }

    protected function getTestObjectType2(): MutableObjectType
    {
        if ($this->testObjectType2 === null) {
            $this->testObjectType2 = new MutableObjectType([
                'name'    => 'TestObject2',
                'fields'  => [
                    'test'   => Type::string(),
                ],
            ]);
        }
        return $this->testObjectType2;
    }

    protected function getInputTestObjectType(): InputObjectType
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

                public function mapClassToType(string $className, ?OutputType $subType, RecursiveTypeMapperInterface $recursiveTypeMapper): MutableObjectType
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

                public function canMapClassToInputType(string $className): bool
                {
                    return $className === TestObject::class || $className === TestObject2::class;
                }

                public function getSupportedClasses(): array
                {
                    return [TestObject::class, TestObject2::class];
                }

                public function mapNameToType(string $typeName, RecursiveTypeMapperInterface $recursiveTypeMapper): Type
                {
                    switch ($typeName) {
                        case 'TestObject':
                            return $this->testObjectType;
                        case 'TestObject2':
                            return $this->testObjectType2;
                        case 'TestObjectInput':
                            return $this->inputTestObjectType;
                        default:
                            throw CannotMapTypeException::createForName($typeName);
                    }
                }

                public function canMapNameToType(string $typeName): bool
                {
                    return $typeName === 'TestObject' || $typeName === 'TestObject2' || $typeName === 'TestObjectInput';
                }

                public function canExtendTypeForClass(string $className, MutableObjectType $type, RecursiveTypeMapperInterface $recursiveTypeMapper): bool
                {
                    return false;
                }

                public function extendTypeForClass(string $className, MutableObjectType $type, RecursiveTypeMapperInterface $recursiveTypeMapper): void
                {
                    throw CannotMapTypeException::createForExtendType($className, $type);
                }

                public function canExtendTypeForName(string $typeName, MutableObjectType $type, RecursiveTypeMapperInterface $recursiveTypeMapper): bool
                {
                    return false;
                }

                public function extendTypeForName(string $typeName, MutableObjectType $type, RecursiveTypeMapperInterface $recursiveTypeMapper): void
                {
                    throw CannotMapTypeException::createForExtendName($typeName, $type);
                }
            }, new NamingStrategy(), new ArrayCache(), $this->getTypeRegistry());
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
                /*'customOutput' => function() {
                    return new StringType();
                }*/
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
            $this->getTypeResolver(),
            new CachedDocBlockFactory(new ArrayCache())
        );
    }

    protected function getTypeGenerator(): TypeGenerator
    {
        if ($this->typeGenerator === null) {
            $this->typeGenerator = new TypeGenerator($this->getAnnotationReader(), $this->getControllerQueryProviderFactory(), new NamingStrategy(), $this->getTypeRegistry(), $this->getRegistry());
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

    protected function getTypeResolver(): TypeResolver
    {
        if ($this->typeResolver === null) {
            $this->typeResolver = new TypeResolver();
            $this->typeResolver->registerSchema(new \GraphQL\Type\Schema([]));
        }
        return $this->typeResolver;
    }

    protected function getControllerQueryProviderFactory(): FieldsBuilderFactory
    {
        if ($this->controllerQueryProviderFactory === null) {
            $this->controllerQueryProviderFactory = new FieldsBuilderFactory($this->getAnnotationReader(),
                $this->getHydrator(),
                new VoidAuthenticationService(),
                new VoidAuthorizationService(),
                $this->getTypeResolver(),
                new CachedDocBlockFactory(new ArrayCache()));
        }
        return $this->controllerQueryProviderFactory;
    }

    protected function getTypeRegistry(): TypeRegistry
    {
        if ($this->typeRegistry === null) {
            $this->typeRegistry = new TypeRegistry();
        }
        return $this->typeRegistry;
    }
}
