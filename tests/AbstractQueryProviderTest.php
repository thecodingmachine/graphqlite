<?php


namespace TheCodingMachine\GraphQLite;

use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\AnnotationReader as DoctrineAnnotationReader;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\OutputType;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\StringType;
use GraphQL\Type\Definition\Type;
use Mouf\Picotainer\Picotainer;
use phpDocumentor\Reflection\TypeResolver as PhpDocumentorTypeResolver;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use SplFileInfo;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\Psr16Adapter;
use Symfony\Component\Cache\Psr16Cache;
use Symfony\Component\Cache\Simple\ArrayCache;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use TheCodingMachine\GraphQLite\Fixtures\Mocks\MockResolvableInputObjectType;
use TheCodingMachine\GraphQLite\Fixtures\TestObject;
use TheCodingMachine\GraphQLite\Fixtures\TestObject2;
use TheCodingMachine\GraphQLite\Fixtures\Types\TestFactory;
use TheCodingMachine\GraphQLite\Loggers\ExceptionLogger;
use TheCodingMachine\GraphQLite\Mappers\CannotMapTypeException;
use TheCodingMachine\GraphQLite\Mappers\CannotMapTypeExceptionInterface;
use TheCodingMachine\GraphQLite\Mappers\Parameters\ResolveInfoParameterHandler;
use TheCodingMachine\GraphQLite\Mappers\RecursiveTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\RecursiveTypeMapperInterface;
use TheCodingMachine\GraphQLite\Mappers\Root\BaseTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\Root\CompositeRootTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\Root\CompoundTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\Root\FinalRootTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\Root\IteratorTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\Root\MyCLabsEnumTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\Root\NullableTypeMapperAdapter;
use TheCodingMachine\GraphQLite\Mappers\Root\RootTypeMapperFactoryContext;
use TheCodingMachine\GraphQLite\Mappers\Root\RootTypeMapperInterface;
use TheCodingMachine\GraphQLite\Mappers\TypeMapperInterface;
use TheCodingMachine\GraphQLite\Containers\EmptyContainer;
use TheCodingMachine\GraphQLite\Containers\BasicAutoWiringContainer;
use TheCodingMachine\GraphQLite\Middlewares\AuthorizationFieldMiddleware;
use TheCodingMachine\GraphQLite\Middlewares\FieldMiddlewarePipe;
use TheCodingMachine\GraphQLite\Mappers\Parameters\ParameterMiddlewarePipe;
use TheCodingMachine\GraphQLite\Middlewares\SecurityFieldMiddleware;
use TheCodingMachine\GraphQLite\Reflection\CachedDocBlockFactory;
use TheCodingMachine\GraphQLite\Security\SecurityExpressionLanguageProvider;
use TheCodingMachine\GraphQLite\Security\VoidAuthenticationService;
use TheCodingMachine\GraphQLite\Security\VoidAuthorizationService;
use TheCodingMachine\GraphQLite\Types\ArgumentResolver;
use TheCodingMachine\GraphQLite\Types\MutableInterface;
use TheCodingMachine\GraphQLite\Types\MutableObjectType;
use TheCodingMachine\GraphQLite\Types\ResolvableMutableInputInterface;
use TheCodingMachine\GraphQLite\Types\ResolvableMutableInputObjectType;
use TheCodingMachine\GraphQLite\Types\TypeResolver;
use function array_reverse;

abstract class AbstractQueryProviderTest extends TestCase
{
    private $testObjectType;
    private $testObjectType2;
    private $inputTestObjectType;
    private $typeMapper;
    private $argumentResolver;
    private $registry;
    private $typeGenerator;
    private $inputTypeGenerator;
    private $inputTypeUtils;
    private $fieldsBuilder;
    private $annotationReader;
    private $typeResolver;
    private $typeRegistry;
    private $lockFactory;
    private $parameterMiddlewarePipe;
    private $rootTypeMapper;

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

    protected function getInputTestObjectType(): MockResolvableInputObjectType
    {
        if ($this->inputTestObjectType === null) {
            $this->inputTestObjectType = new MockResolvableInputObjectType([
                'name'    => 'TestObjectInput',
                'fields'  => [
                    'test'   => Type::string(),
                ],
            ], function($source, $args) {
                return new TestObject($args['test']);
            });
        }
        return $this->inputTestObjectType;
    }

    protected function getTypeMapper()
    {
        if ($this->typeMapper === null) {
            $arrayAdapter = new ArrayAdapter();
            $arrayAdapter->setLogger(new ExceptionLogger());

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

                public function mapClassToType(string $className, ?OutputType $subType): MutableInterface
                {
                    if ($className === TestObject::class) {
                        return $this->testObjectType;
                    } elseif ($className === TestObject2::class) {
                        return $this->testObjectType2;
                    } else {
                        throw CannotMapTypeException::createForType($className);
                    }
                }

                public function mapClassToInputType(string $className): ResolvableMutableInputInterface
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

                public function mapNameToType(string $typeName): Type
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

                public function canExtendTypeForClass(string $className, MutableInterface $type): bool
                {
                    return false;
                }

                public function extendTypeForClass(string $className, MutableInterface $type): void
                {
                    throw CannotMapTypeException::createForExtendType($className, $type);
                }

                public function canExtendTypeForName(string $typeName, MutableInterface $type): bool
                {
                    return false;
                }

                public function extendTypeForName(string $typeName, MutableInterface $type): void
                {
                    throw CannotMapTypeException::createForExtendName($typeName, $type);
                }

                public function canDecorateInputTypeForName(string $typeName, ResolvableMutableInputInterface $type): bool
                {
                    return false;
                }

                public function decorateInputTypeForName(string $typeName, ResolvableMutableInputInterface $type): void
                {
                    throw CannotMapTypeException::createForDecorateName($typeName, $type);
                }

            }, new NamingStrategy(), new Psr16Cache($arrayAdapter), $this->getTypeRegistry());
        }
        return $this->typeMapper;
    }

    protected function getArgumentResolver(): ArgumentResolver
    {
        if ($this->argumentResolver === null) {
            $this->argumentResolver = new ArgumentResolver();
        }
        return $this->argumentResolver;
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

    protected function getParameterMiddlewarePipe(): ParameterMiddlewarePipe
    {
        if ($this->parameterMiddlewarePipe === null) {
            $this->parameterMiddlewarePipe = new ParameterMiddlewarePipe();
            $this->parameterMiddlewarePipe->pipe(new ResolveInfoParameterHandler());
        }
        return $this->parameterMiddlewarePipe;
    }

    protected function buildFieldsBuilder(): FieldsBuilder
    {
        $arrayAdapter = new ArrayAdapter();
        $arrayAdapter->setLogger(new ExceptionLogger());
        $psr16Cache = new Psr16Cache($arrayAdapter);

        $fieldMiddlewarePipe = new FieldMiddlewarePipe();
        $fieldMiddlewarePipe->pipe(new AuthorizationFieldMiddleware(
            new VoidAuthenticationService(),
            new VoidAuthorizationService()
        ));
        $expressionLanguage = new ExpressionLanguage(new Psr16Adapter($psr16Cache), [new SecurityExpressionLanguageProvider()]);
        $fieldMiddlewarePipe->pipe(new SecurityFieldMiddleware($expressionLanguage, new VoidAuthenticationService(), new VoidAuthorizationService()));

        $parameterMiddlewarePipe = new ParameterMiddlewarePipe();
        $parameterMiddlewarePipe->pipe(new ResolveInfoParameterHandler());

        return new FieldsBuilder(
            $this->getAnnotationReader(),
            $this->getTypeMapper(),
            $this->getArgumentResolver(),
            $this->getTypeResolver(),
            new CachedDocBlockFactory($psr16Cache),
            new NamingStrategy(),
            $this->buildRootTypeMapper(),
            $this->getParameterMiddlewarePipe(),
            $fieldMiddlewarePipe
        );
    }

    protected function getRootTypeMapper(): RootTypeMapperInterface
    {
        if ($this->rootTypeMapper === null) {
            $this->rootTypeMapper = $this->buildRootTypeMapper();
        }
        return $this->rootTypeMapper;
    }

    protected function buildRootTypeMapper(): RootTypeMapperInterface
    {
        $topRootTypeMapper = new NullableTypeMapperAdapter();

        $errorRootTypeMapper = new FinalRootTypeMapper($this->getTypeMapper());
        $rootTypeMapper = new BaseTypeMapper($errorRootTypeMapper, $this->getTypeMapper(), $topRootTypeMapper);
        $rootTypeMapper = new MyCLabsEnumTypeMapper($rootTypeMapper);
        $rootTypeMapper = new CompoundTypeMapper($rootTypeMapper, $topRootTypeMapper, $this->getTypeRegistry(), $this->getTypeMapper());
        $rootTypeMapper = new IteratorTypeMapper($rootTypeMapper, $topRootTypeMapper);

        $topRootTypeMapper->setNext($rootTypeMapper);
        return $topRootTypeMapper;
    }

    protected function getFieldsBuilder(): FieldsBuilder
    {
        if ($this->fieldsBuilder === null) {
            $this->fieldsBuilder = $this->buildFieldsBuilder();
        }
        return $this->fieldsBuilder;
    }

    protected function getTypeGenerator(): TypeGenerator
    {
        if ($this->typeGenerator !== null) {
            return $this->typeGenerator;
        }
        $this->typeGenerator = new TypeGenerator($this->getAnnotationReader(), new NamingStrategy(), $this->getTypeRegistry(), $this->getRegistry(), $this->getTypeMapper(), $this->getFieldsBuilder());
        return $this->typeGenerator;
    }

    protected function getInputTypeGenerator(): InputTypeGenerator
    {
        if ($this->inputTypeGenerator !== null) {
            return $this->inputTypeGenerator;
        }
        $this->inputTypeGenerator = new InputTypeGenerator($this->getInputTypeUtils(), $this->getFieldsBuilder());
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

    protected function getTypeRegistry(): TypeRegistry
    {
        if ($this->typeRegistry === null) {
            $this->typeRegistry = new TypeRegistry();
        }
        return $this->typeRegistry;
    }

    protected function resolveType(string $type): \phpDocumentor\Reflection\Type
    {
        $phpDocumentorTypeResolver = new PhpDocumentorTypeResolver();
        return $phpDocumentorTypeResolver->resolve($type);
    }
}
