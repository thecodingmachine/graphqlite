<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite;

use GraphQL\Type\Definition\BooleanType;
use GraphQL\Type\Definition\EnumType;
use GraphQL\Type\Definition\FloatType;
use GraphQL\Type\Definition\IDType;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\IntType;
use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\StringType;
use GraphQL\Type\Definition\UnionType;
use ReflectionMethod;
use stdClass;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;
use TheCodingMachine\GraphQLite\Annotations\Exceptions\InvalidParameterException;
use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\GraphQLite\Fixtures\PropertyPromotionInputType;
use TheCodingMachine\GraphQLite\Fixtures\PropertyPromotionInputTypeWithoutGenericDoc;
use TheCodingMachine\GraphQLite\Fixtures\TestController;
use TheCodingMachine\GraphQLite\Fixtures\TestControllerNoReturnType;
use TheCodingMachine\GraphQLite\Fixtures\TestControllerWithArrayParam;
use TheCodingMachine\GraphQLite\Fixtures\TestControllerWithArrayReturnType;
use TheCodingMachine\GraphQLite\Fixtures\TestControllerWithBadSecurity;
use TheCodingMachine\GraphQLite\Fixtures\TestControllerWithFailWith;
use TheCodingMachine\GraphQLite\Fixtures\TestControllerWithInvalidInputType;
use TheCodingMachine\GraphQLite\Fixtures\TestControllerWithInvalidReturnType;
use TheCodingMachine\GraphQLite\Fixtures\TestControllerWithIterableReturnType;
use TheCodingMachine\GraphQLite\Fixtures\TestControllerWithNullableArray;
use TheCodingMachine\GraphQLite\Fixtures\TestControllerWithParamDateTime;
use TheCodingMachine\GraphQLite\Fixtures\TestControllerWithReturnDateTime;
use TheCodingMachine\GraphQLite\Fixtures\TestControllerWithUnionInputParam;
use TheCodingMachine\GraphQLite\Fixtures\TestDeprecatedField;
use TheCodingMachine\GraphQLite\Fixtures\TestDoubleReturnTag;
use TheCodingMachine\GraphQLite\Fixtures\TestEnum;
use TheCodingMachine\GraphQLite\Fixtures\TestFieldBadInputType;
use TheCodingMachine\GraphQLite\Fixtures\TestFieldBadOutputType;
use TheCodingMachine\GraphQLite\Fixtures\TestObject;
use TheCodingMachine\GraphQLite\Fixtures\TestObjectWithDeprecatedField;
use TheCodingMachine\GraphQLite\Fixtures\TestSelfType;
use TheCodingMachine\GraphQLite\Fixtures\TestSourceFieldBadOutputType;
use TheCodingMachine\GraphQLite\Fixtures\TestSourceFieldBadOutputType2;
use TheCodingMachine\GraphQLite\Fixtures\TestSourceName;
use TheCodingMachine\GraphQLite\Fixtures\TestSourceNameType;
use TheCodingMachine\GraphQLite\Fixtures\TestType;
use TheCodingMachine\GraphQLite\Fixtures\TestTypeId;
use TheCodingMachine\GraphQLite\Fixtures\TestTypeMissingAnnotation;
use TheCodingMachine\GraphQLite\Fixtures\TestTypeMissingField;
use TheCodingMachine\GraphQLite\Fixtures\TestTypeMissingReturnType;
use TheCodingMachine\GraphQLite\Fixtures\TestTypeWithDescriptions;
use TheCodingMachine\GraphQLite\Fixtures\TestTypeWithFailWith;
use TheCodingMachine\GraphQLite\Fixtures\TestTypeWithInvalidPrefetchMethod;
use TheCodingMachine\GraphQLite\Fixtures\TestTypeWithMagicProperty;
use TheCodingMachine\GraphQLite\Fixtures\TestTypeWithMagicPropertyType;
use TheCodingMachine\GraphQLite\Fixtures\TestTypeWithPrefetchMethods;
use TheCodingMachine\GraphQLite\Fixtures\TestTypeWithSourceFieldInterface;
use TheCodingMachine\GraphQLite\Fixtures\TestTypeWithSourceFieldInvalidParameterAnnotation;
use TheCodingMachine\GraphQLite\Mappers\CannotMapTypeException;
use TheCodingMachine\GraphQLite\Mappers\CannotMapTypeExceptionInterface;
use TheCodingMachine\GraphQLite\Middlewares\AuthorizationFieldMiddleware;
use TheCodingMachine\GraphQLite\Middlewares\BadExpressionInSecurityException;
use TheCodingMachine\GraphQLite\Middlewares\InputFieldMiddlewarePipe;
use TheCodingMachine\GraphQLite\Middlewares\MissingMagicGetException;
use TheCodingMachine\GraphQLite\Parameters\MissingArgumentException;
use TheCodingMachine\GraphQLite\Reflection\DocBlock\CachedDocBlockFactory;
use TheCodingMachine\GraphQLite\Security\AuthenticationServiceInterface;
use TheCodingMachine\GraphQLite\Security\AuthorizationServiceInterface;
use TheCodingMachine\GraphQLite\Security\VoidAuthenticationService;
use TheCodingMachine\GraphQLite\Security\VoidAuthorizationService;
use TheCodingMachine\GraphQLite\Types\DateTimeType;
use TheCodingMachine\GraphQLite\Types\VoidType;

use function reset;

class FieldsBuilderTest extends AbstractQueryProvider
{
    public function testQueryProvider(): void
    {
        $controller = new TestController();

        $queryProvider = $this->buildFieldsBuilder();

        $queries = $queryProvider->getQueries($controller);

        $this->assertCount(9, $queries);
        $usersQuery = $queries['test'];
        $this->assertSame('test', $usersQuery->name);

        $this->assertCount(10, $usersQuery->args);
        $this->assertSame('int', $usersQuery->args[0]->name);
        $this->assertInstanceOf(NonNull::class, $usersQuery->args[0]->getType());
        $this->assertInstanceOf(IntType::class, $usersQuery->args[0]->getType()->getWrappedType());
        $this->assertInstanceOf(StringType::class, $usersQuery->args[7]->getType());
        $this->assertInstanceOf(NonNull::class, $usersQuery->args[1]->getType());
        $this->assertInstanceOf(ListOfType::class, $usersQuery->args[1]->getType()->getWrappedType());
        $this->assertInstanceOf(NonNull::class, $usersQuery->args[1]->getType()->getWrappedType()->getWrappedType());
        $this->assertInstanceOf(InputObjectType::class, $usersQuery->args[1]->getType()->getWrappedType()->getWrappedType()->getWrappedType());
        $this->assertInstanceOf(BooleanType::class, $usersQuery->args[2]->getType());
        $this->assertInstanceOf(FloatType::class, $usersQuery->args[3]->getType());
        $this->assertInstanceOf(DateTimeType::class, $usersQuery->args[4]->getType());
        $this->assertInstanceOf(DateTimeType::class, $usersQuery->args[5]->getType());
        $this->assertInstanceOf(StringType::class, $usersQuery->args[6]->getType());
        $this->assertInstanceOf(IDType::class, $usersQuery->args[8]->getType());
        $this->assertInstanceOf(EnumType::class, $usersQuery->args[9]->getType());
        $this->assertSame('TestObjectInput', $usersQuery->args[1]->getType()->getWrappedType()->getWrappedType()->getWrappedType()->name);

        $context = [
            'int' => 42,
            'string' => 'foo',
            'list' => [
                ['test' => '42'],
                ['test' => '12'],
            ],
            'boolean' => true,
            'float' => 4.2,
            'dateTimeImmutable' => '2017-01-01 01:01:01',
            'dateTime' => '2017-01-01 01:01:01',
            'id' => 42,
            'enum' => TestEnum::ON(),
        ];

        $resolve = $usersQuery->resolveFn;
        $resolveInfo = $this->createMock(ResolveInfo::class);
        $result = $resolve(new stdClass(), $context, null, $resolveInfo);

        $this->assertInstanceOf(TestObject::class, $result);
        $this->assertSame('foo424212true4.22017010101010120170101010101default42on', $result->getTest());

        unset($context['string']); // Testing null default value
        $result = $resolve(new stdClass(), $context, null, $resolveInfo);

        $this->assertSame('424212true4.22017010101010120170101010101default42on', $result->getTest());
    }

    public function testMutations(): void
    {
        $controller = new TestController();

        $queryProvider = $this->buildFieldsBuilder();

        $mutations = $queryProvider->getMutations($controller);

        $this->assertCount(2, $mutations);

        $testReturnMutation = $mutations['testReturn'];
        $this->assertSame('testReturn', $testReturnMutation->name);

        $resolve = $testReturnMutation->resolveFn;
        $result = $resolve(
            new stdClass(),
            ['testObject' => ['test' => '42']],
            null,
            $this->createMock(ResolveInfo::class),
        );

        $this->assertInstanceOf(TestObject::class, $result);
        $this->assertEquals('42', $result->getTest());

        $testVoidMutation = $mutations['testVoid'];
        $this->assertInstanceOf(VoidType::class, $testVoidMutation->getType());
    }

    public function testSubscriptions(): void
    {
        $controller = new TestController();

        $queryProvider = $this->buildFieldsBuilder();

        $subscriptions = $queryProvider->getSubscriptions($controller);

        $this->assertCount(2, $subscriptions);

        $testSubscribeSubscription = $subscriptions['testSubscribe'];
        $this->assertSame('testSubscribe', $testSubscribeSubscription->name);

        $testSubscribeWithInputSubscription = $subscriptions['testSubscribeWithInput'];
        $this->assertInstanceOf(IDType::class, $testSubscribeWithInputSubscription->getType());
    }

    public function testErrors(): void
    {
        $controller = new class {
            #[Query]
            public function test($noTypeHint): string
            {
                return 'foo';
            }
        };

        $queryProvider = $this->buildFieldsBuilder();

        $this->expectException(CannotMapTypeException::class);
        $queryProvider->getQueries($controller);
    }

    public function testTypeInDocBlock(): void
    {
        $controller = new class {
            #[Query]
            public function test(int $typeHintInDocBlock): string
            {
                return 'foo';
            }
        };

        $queryProvider = $this->buildFieldsBuilder();
        $queries = $queryProvider->getQueries($controller);

        $this->assertCount(1, $queries);
        $query = $queries['test'];

        $this->assertInstanceOf(NonNull::class, $query->args[0]->getType());
        $this->assertInstanceOf(IntType::class, $query->args[0]->getType()->getWrappedType());
        $this->assertInstanceOf(NonNull::class, $query->getType());
        $this->assertInstanceOf(StringType::class, $query->getType()->getWrappedType());
    }

    /**
     * Tests that the fields builder will fail when a parameter is missing it's generic docblock
     * definition, when required - an array, for instance, or could be a collection (List types)
     */
    public function testTypeMissingForPropertyPromotionWithoutGenericDoc(): void
    {
        $fieldsBuilder = $this->buildFieldsBuilder();

        $this->expectException(CannotMapTypeException::class);

        $fieldsBuilder->getInputFields(
            PropertyPromotionInputTypeWithoutGenericDoc::class,
            'PropertyPromotionInputTypeWithoutGenericDocInput',
        );
    }

    /**
     * Tests that the fields builder will properly build an input type using property promotion
     * with the generic docblock defined on the constructor and not the property directly.
     */
    public function testTypeInDocBlockWithPropertyPromotion(): void
    {
        $fieldsBuilder = $this->buildFieldsBuilder();

        // Techncially at this point, we already know it's working, since an exception would have been
        // thrown otherwise, requiring the generic type to be specified.
        // @see self::testTypeMissingForPropertyPromotionWithoutGenericDoc
        $inputFields = $fieldsBuilder->getInputFields(
            PropertyPromotionInputType::class,
            'PropertyPromotionInputTypeInput',
        );

        $this->assertCount(1, $inputFields);
        $this->assertEquals('amounts', reset($inputFields)->name);
    }

    public function testQueryProviderWithFixedReturnType(): void
    {
        $controller = new TestController();

        $queryProvider = $this->buildFieldsBuilder();

        $queries = $queryProvider->getQueries($controller);

        $this->assertCount(9, $queries);
        $fixedQuery = $queries['testFixReturnType'];

        $this->assertInstanceOf(IDType::class, $fixedQuery->getType());
    }

    public function testQueryProviderWithComplexFixedReturnType(): void
    {
        $controller = new TestController();

        $queryProvider = $this->buildFieldsBuilder();

        $queries = $queryProvider->getQueries($controller);

        $this->assertCount(9, $queries);
        $fixedQuery = $queries['testFixComplexReturnType'];

        $this->assertInstanceOf(NonNull::class, $fixedQuery->getType());
        $this->assertInstanceOf(ListOfType::class, $fixedQuery->getType()->getWrappedType());
        $this->assertInstanceOf(NonNull::class, $fixedQuery->getType()->getWrappedType()->getWrappedType());
        $this->assertInstanceOf(IDType::class, $fixedQuery->getType()->getWrappedType()->getWrappedType()->getWrappedType());
    }

    public function testNameFromAnnotation(): void
    {
        $controller = new TestController();

        $queryProvider = $this->buildFieldsBuilder();

        $queries = $queryProvider->getQueries($controller);

        $query = $queries['nameFromAnnotation'];

        $this->assertSame('nameFromAnnotation', $query->name);
    }

    public function testSourceField(): void
    {
        $controller = new TestType();

        $queryProvider = $this->buildFieldsBuilder();

        $fields = $queryProvider->getFields($controller);

        $this->assertCount(3, $fields);

        $this->assertSame('customField', $fields['customField']->name);
        $this->assertSame('test', $fields['test']->name);
        // Test the "self" name resolution
        $this->assertSame('sibling', $fields['sibling']->name);
        $this->assertInstanceOf(NonNull::class, $fields['sibling']->getType());
        $this->assertInstanceOf(ObjectType::class, $fields['sibling']->getType()->getWrappedType());
        $this->assertSame('TestObject', $fields['sibling']->getType()->getWrappedType()->name);
        $this->assertSame('This is a test summary', $fields['test']->description);
        $this->assertSame('Test SourceField description', $fields['sibling']->description);
    }

    public function testSourceFieldOnSelfType(): void
    {
        $queryProvider = $this->buildFieldsBuilder();

        $fields = $queryProvider->getSelfFields(TestSelfType::class);

        $this->assertCount(1, $fields);

        $this->assertSame('test', $fields['test']->name);
        $resolve = $fields['test']->resolveFn;
        $obj = new TestSelfType();
        $this->assertEquals('foo', $resolve($obj, [], null, $this->createMock(ResolveInfo::class)));
    }

    public function testLoggedInSourceField(): void
    {
        $authenticationService = new class implements AuthenticationServiceInterface {
            public function isLogged(): bool
            {
                return true;
            }

            public function getUser(): object|null
            {
                return new stdClass();
            }
        };
        $queryProvider = new FieldsBuilder(
            $this->getAnnotationReader(),
            $this->getTypeMapper(),
            $this->getArgumentResolver(),
            $this->getTypeResolver(),
            $this->getDocBlockFactory(),
            new NamingStrategy(),
            $this->getRootTypeMapper(),
            $this->getParameterMiddlewarePipe(),
            new AuthorizationFieldMiddleware(
                $authenticationService,
                new VoidAuthorizationService(),
            ),
            new InputFieldMiddlewarePipe(),
        );

        $fields = $queryProvider->getFields(new TestType());
        $this->assertCount(4, $fields);

        $this->assertSame('testBool', $fields['testBool']->name);
    }

    public function testRightInSourceField(): void
    {
        $authorizationService = new class implements AuthorizationServiceInterface {
            public function isAllowed(string $right, $subject = null): bool
            {
                return true;
            }
        };

        $queryProvider = new FieldsBuilder(
            $this->getAnnotationReader(),
            $this->getTypeMapper(),
            $this->getArgumentResolver(),
            $this->getTypeResolver(),
            $this->getDocBlockFactory(),
            new NamingStrategy(),
            $this->getRootTypeMapper(),
            $this->getParameterMiddlewarePipe(),
            new AuthorizationFieldMiddleware(
                new VoidAuthenticationService(),
                $authorizationService,
            ),
            new InputFieldMiddlewarePipe(),
        );

        $fields = $queryProvider->getFields(new TestType());
        $this->assertCount(4, $fields);

        $this->assertSame('testRight', $fields['testRight']->name);
    }

    public function testMissingTypeAnnotation(): void
    {
        $queryProvider = $this->buildFieldsBuilder();

        $this->expectException(MissingAnnotationException::class);
        $queryProvider->getFields(new TestTypeMissingAnnotation());
    }

    public function testSourceFieldDoesNotExists(): void
    {
        $queryProvider = $this->buildFieldsBuilder();

        $this->expectException(FieldNotFoundException::class);
        $this->expectExceptionMessage('There is an issue with a @SourceField annotation in class "TheCodingMachine\GraphQLite\Fixtures\TestTypeMissingField": Could not find a getter or a isser for field "notExists". Looked for: "TheCodingMachine\GraphQLite\Fixtures\TestObject::notExists()", "TheCodingMachine\GraphQLite\Fixtures\TestObject::getNotExists()", "TheCodingMachine\GraphQLite\Fixtures\TestObject::isNotExists()');
        $queryProvider->getFields(new TestTypeMissingField());
    }

    public function testSourceFieldHasMissingReturnType(): void
    {
        $queryProvider = $this->buildFieldsBuilder();

        $this->expectException(CannotMapTypeException::class);
        $this->expectExceptionMessage('For return type of TheCodingMachine\GraphQLite\Fixtures\TestObjectMissingReturnType::getTest, a type-hint is missing (or PHPDoc specifies a "mixed" type-hint). Please provide a better type-hint.');
        $queryProvider->getFields(new TestTypeMissingReturnType());
    }

    public function testSourceFieldIsId(): void
    {
        $queryProvider = $this->buildFieldsBuilder();
        $fields = $queryProvider->getFields(new TestTypeId());
        $this->assertCount(1, $fields);

        $this->assertSame('test', $fields['test']->name);
        $this->assertInstanceOf(NonNull::class, $fields['test']->getType());
        $this->assertInstanceOf(IDType::class, $fields['test']->getType()->getWrappedType());
    }

    public function testFromSourceFieldsInterface(): void
    {
        $queryProvider = new FieldsBuilder(
            $this->getAnnotationReader(),
            $this->getTypeMapper(),
            $this->getArgumentResolver(),
            $this->getTypeResolver(),
            $this->getDocBlockFactory(),
            new NamingStrategy(),
            $this->getRootTypeMapper(),
            $this->getParameterMiddlewarePipe(),
            new AuthorizationFieldMiddleware(
                new VoidAuthenticationService(),
                new VoidAuthorizationService(),
            ),
            new InputFieldMiddlewarePipe(),
        );
        $fields = $queryProvider->getFields(new TestTypeWithSourceFieldInterface());
        $this->assertCount(1, $fields);

        $this->assertSame('test', $fields['test']->name);
    }

    public function testQueryProviderWithIterableClass(): void
    {
        $controller = new TestController();

        $queryProvider = $this->buildFieldsBuilder();

        $queries = $queryProvider->getQueries($controller);

        $this->assertCount(9, $queries);
        $iterableQuery = $queries['arrayObject'];

        $this->assertSame('arrayObject', $iterableQuery->name);
        $this->assertInstanceOf(NonNull::class, $iterableQuery->getType());
        $this->assertInstanceOf(ListOfType::class, $iterableQuery->getType()->getWrappedType());
        $this->assertInstanceOf(NonNull::class, $iterableQuery->getType()->getWrappedType()->getWrappedType());
        $this->assertInstanceOf(ObjectType::class, $iterableQuery->getType()->getWrappedType()->getWrappedType()->getWrappedType());
        $this->assertSame('TestObject', $iterableQuery->getType()->getWrappedType()->getWrappedType()->getWrappedType()->name);
    }

    public function testQueryProviderWithIterableGenericClass(): void
    {
        $controller = new TestController();

        $queryProvider = $this->buildFieldsBuilder();

        $queries = $queryProvider->getQueries($controller);

        $this->assertCount(9, $queries);
        $iterableQuery = $queries['arrayObjectGeneric'];

        $this->assertSame('arrayObjectGeneric', $iterableQuery->name);
        $this->assertInstanceOf(NonNull::class, $iterableQuery->getType());
        $this->assertInstanceOf(ListOfType::class, $iterableQuery->getType()->getWrappedType());
        $this->assertInstanceOf(NonNull::class, $iterableQuery->getType()->getWrappedType()->getWrappedType());
        $this->assertInstanceOf(ObjectType::class, $iterableQuery->getType()->getWrappedType()->getWrappedType()->getWrappedType());
        $this->assertSame('TestObject', $iterableQuery->getType()->getWrappedType()->getWrappedType()->getWrappedType()->name);
    }

    public function testQueryProviderWithIterable(): void
    {
        $queryProvider = $this->buildFieldsBuilder();

        $queries = $queryProvider->getQueries(new TestController());

        $this->assertCount(9, $queries);
        $iterableQuery = $queries['iterable'];

        $this->assertSame('iterable', $iterableQuery->name);
        $this->assertInstanceOf(NonNull::class, $iterableQuery->getType());
        $this->assertInstanceOf(ListOfType::class, $iterableQuery->getType()->getWrappedType());
        $this->assertInstanceOf(NonNull::class, $iterableQuery->getType()->getWrappedType()->getWrappedType());
        $this->assertInstanceOf(ObjectType::class, $iterableQuery->getType()->getWrappedType()->getWrappedType()->getWrappedType());
        $this->assertSame('TestObject', $iterableQuery->getType()->getWrappedType()->getWrappedType()->getWrappedType()->name);
    }

    public function testQueryProviderWithIterableGeneric(): void
    {
        $queryProvider = $this->buildFieldsBuilder();

        $queries = $queryProvider->getQueries(new TestController());

        $this->assertCount(9, $queries);
        $iterableQuery = $queries['iterableGeneric'];

        $this->assertSame('iterableGeneric', $iterableQuery->name);
        $this->assertInstanceOf(NonNull::class, $iterableQuery->getType());
        $this->assertInstanceOf(ListOfType::class, $iterableQuery->getType()->getWrappedType());
        $this->assertInstanceOf(NonNull::class, $iterableQuery->getType()->getWrappedType()->getWrappedType());
        $this->assertInstanceOf(ObjectType::class, $iterableQuery->getType()->getWrappedType()->getWrappedType()->getWrappedType());
        $this->assertSame('TestObject', $iterableQuery->getType()->getWrappedType()->getWrappedType()->getWrappedType()->name);
    }

    public function testNoReturnTypeError(): void
    {
        $queryProvider = $this->buildFieldsBuilder();
        $this->expectException(CannotMapTypeException::class);
        $this->expectExceptionMessage('For return type of TheCodingMachine\GraphQLite\Fixtures\TestControllerNoReturnType::test, a type-hint is missing (or PHPDoc specifies a "mixed" type-hint). Please provide a better type-hint.');
        $queryProvider->getQueries(new TestControllerNoReturnType());
    }

    public function testQueryProviderWithUnion(): void
    {
        $controller = new TestController();

        $queryProvider = $this->buildFieldsBuilder();

        $queries = $queryProvider->getQueries($controller);

        $this->assertCount(9, $queries);
        $unionQuery = $queries['union'];

        $this->assertInstanceOf(NonNull::class, $unionQuery->getType());
        $this->assertInstanceOf(UnionType::class, $unionQuery->getType()->getWrappedType());
        $this->assertInstanceOf(ObjectType::class, $unionQuery->getType()->getWrappedType()->getTypes()[0]);
        $this->assertSame('TestObject', $unionQuery->getType()->getWrappedType()->getTypes()[0]->name);
        $this->assertInstanceOf(ObjectType::class, $unionQuery->getType()->getWrappedType()->getTypes()[1]);
        $this->assertSame('TestObject2', $unionQuery->getType()->getWrappedType()->getTypes()[1]->name);
    }

    public function testQueryProviderWithInvalidInputType(): void
    {
        $controller = new TestControllerWithInvalidInputType();

        $queryProvider = $this->buildFieldsBuilder();

        $this->expectException(CannotMapTypeException::class);
        $this->expectExceptionMessage('For parameter $foo, in TheCodingMachine\GraphQLite\Fixtures\TestControllerWithInvalidInputType::test, cannot map class "Throwable" to a known GraphQL input type. Are you missing a @Factory annotation? If you have a @Factory annotation, is it in a namespace analyzed by GraphQLite?');
        $queryProvider->getQueries($controller);
    }

    public function testQueryProviderWithInvalidReturnType(): void
    {
        $controller = new TestControllerWithInvalidReturnType();

        $queryProvider = $this->buildFieldsBuilder();

        $this->expectException(CannotMapTypeException::class);
        $this->expectExceptionMessage('For return type of TheCodingMachine\GraphQLite\Fixtures\TestControllerWithInvalidReturnType::test, cannot map class "Exception" to a known GraphQL type. Check your TypeMapper configuration.');
        $queryProvider->getQueries($controller);
    }

    public function testQueryProviderWithIterableReturnType(): void
    {
        $controller = new TestControllerWithIterableReturnType();

        $queryProvider = $this->buildFieldsBuilder();

        $this->expectException(CannotMapTypeException::class);
        $this->expectExceptionMessage('For return type of TheCodingMachine\GraphQLite\Fixtures\TestControllerWithIterableReturnType::test, "\ArrayObject" is iterable. Please provide a more specific type. For instance: \ArrayObject|User[].');
        $queryProvider->getQueries($controller);
    }

    public function testQueryProviderWithArrayReturnType(): void
    {
        $controller = new TestControllerWithArrayReturnType();

        $queryProvider = $this->buildFieldsBuilder();

        $this->expectException(CannotMapTypeException::class);
        $this->expectExceptionMessage('For return type of TheCodingMachine\GraphQLite\Fixtures\TestControllerWithArrayReturnType::test, please provide an additional @return in the PHPDoc block to further specify the return type of array. For instance: @return string[]');
        $queryProvider->getQueries($controller);
    }

    public function testQueryProviderWithIterableParams(): void
    {
        $controller = new TestControllerWithArrayParam();

        $queryProvider = $this->buildFieldsBuilder();

        $this->expectException(CannotMapTypeException::class);
        $this->expectExceptionMessage('For parameter $params, in TheCodingMachine\GraphQLite\Fixtures\TestControllerWithArrayParam::test, please provide an additional @param in the PHPDoc block to further specify the type of the iterable. For instance: @param string[] $params.');
        $queryProvider->getQueries($controller);
    }

    // Test disabled because we cannot assume that by providing a more specific type, we will be able to handle the iterable.
    /*public function testQueryProviderWithIterableParams(): void
    {
        $controller = new TestControllerWithIterableParam();

        $queryProvider = $this->buildFieldsBuilder();

        $this->expectException(CannotMapTypeException::class);
        $this->expectExceptionMessage('For parameter $params, in TheCodingMachine\GraphQLite\Fixtures\TestControllerWithIterableParam::test, "\ArrayObject" is iterable. Please provide a more specific type. For instance: \ArrayObject|User[].');
        $queryProvider->getQueries($controller);
    }*/

    public function testFailWith(): void
    {
        $controller = new TestControllerWithFailWith();

        $queryProvider = $this->buildFieldsBuilder();

        $queries = $queryProvider->getQueries($controller);

        $this->assertCount(1, $queries);
        $query = $queries['testFailWith'];
        $this->assertSame('testFailWith', $query->name);

        $resolve = $query->resolveFn;
        $result = $resolve(new stdClass(), [], null, $this->createMock(ResolveInfo::class));

        $this->assertNull($result);

        $this->assertInstanceOf(ObjectType::class, $query->getType());
    }

    public function testSourceFieldWithFailWith(): void
    {
        $controller = new TestTypeWithFailWith();

        $queryProvider = $this->buildFieldsBuilder();

        $fields = $queryProvider->getFields($controller);

        $this->assertCount(1, $fields);

        $this->assertSame('test', $fields['test']->name);
        $this->assertInstanceOf(StringType::class, $fields['test']->getType());

        $resolve = $fields['test']->resolveFn;
        $result = $resolve(new stdClass(), [], null, $this->createMock(ResolveInfo::class));

        $this->assertNull($result);

        $this->assertInstanceOf(StringType::class, $fields['test']->getType());
    }

    public function testSourceFieldBadOutputTypeException(): void
    {
        $queryProvider = $this->buildFieldsBuilder();
        $this->expectException(CannotMapTypeExceptionInterface::class);
        $this->expectExceptionMessage('For @SourceField "test" declared in "TheCodingMachine\GraphQLite\Fixtures\TestSourceFieldBadOutputType", cannot find GraphQL type "[NotExists]". Check your TypeMapper configuration.');
        $queryProvider->getFields(new TestSourceFieldBadOutputType());
    }

    public function testSourceFieldBadOutputType2Exception(): void
    {
        $queryProvider = $this->buildFieldsBuilder();
        $this->expectException(CannotMapTypeExceptionInterface::class);
        $this->expectExceptionMessage('For @SourceField "test" declared in "TheCodingMachine\GraphQLite\Fixtures\TestSourceFieldBadOutputType2", Syntax Error: Expected ], found <EOF>');
        $queryProvider->getFields(new TestSourceFieldBadOutputType2());
    }

    public function testBadOutputTypeException(): void
    {
        $queryProvider = $this->buildFieldsBuilder();
        $this->expectException(CannotMapTypeExceptionInterface::class);
        $this->expectExceptionMessage('For return type of TheCodingMachine\GraphQLite\Fixtures\TestFieldBadOutputType::test, cannot find GraphQL type "[NotExists]". Check your TypeMapper configuration.');
        $queryProvider->getFields(new TestFieldBadOutputType());
    }

    public function testBadInputTypeException(): void
    {
        $queryProvider = $this->buildFieldsBuilder();
        $this->expectException(CannotMapTypeExceptionInterface::class);
        $this->expectExceptionMessage('For parameter $input, in TheCodingMachine\GraphQLite\Fixtures\TestFieldBadInputType::testInput, cannot find GraphQL type "[NotExists]". Check your TypeMapper configuration.');
        $queryProvider->getFields(new TestFieldBadInputType());
    }

    public function testDoubleReturnException(): void
    {
        $queryProvider = $this->buildFieldsBuilder();
        $this->expectException(InvalidDocBlockRuntimeException::class);
        $this->expectExceptionMessage('Method TheCodingMachine\\GraphQLite\\Fixtures\\TestDoubleReturnTag::test has several @return annotations.');
        $queryProvider->getFields(new TestDoubleReturnTag());
    }

    public function testMissingArgument(): void
    {
        $controller = new TestController();

        $queryProvider = $this->buildFieldsBuilder();

        $queries = $queryProvider->getQueries($controller);

        $this->assertCount(9, $queries);
        $usersQuery = $queries['test'];
        $context = [];

        $resolve = $usersQuery->resolveFn;
        $resolveInfo = $this->createMock(ResolveInfo::class);

        $this->expectException(MissingArgumentException::class);
        $this->expectExceptionMessage("Expected argument 'int' was not provided in GraphQL query/mutation/field 'test' used in method 'TheCodingMachine\GraphQLite\Fixtures\TestController::test()'");
        $resolve(new stdClass(), $context, null, $resolveInfo);
    }

    public function testEmptyParametersForDecorator(): void
    {
        $queryProvider = $this->buildFieldsBuilder();
        // Let's test that a decorator with no parameter is working.
        $this->assertSame([], $queryProvider->getParametersForDecorator(new ReflectionMethod(SchemaFactory::class, 'devMode')));
    }

    public function testInvalidPrefetchMethod(): void
    {
        $controller = new TestTypeWithInvalidPrefetchMethod();

        $queryProvider = $this->buildFieldsBuilder();

        $this->expectException(InvalidPrefetchMethodRuntimeException::class);
        $this->expectExceptionMessage('#[Prefetch] attribute on parameter $data in TheCodingMachine\\GraphQLite\\Fixtures\\TestTypeWithInvalidPrefetchMethod::test specifies a callable that is invalid: Method TheCodingMachine\\GraphQLite\\Fixtures\\TestTypeWithInvalidPrefetchMethod::notExists wasn\'t found or isn\'t accessible.');
        $queryProvider->getFields($controller);
    }

    public function testPrefetchMethod(): void
    {
        $controller = new TestTypeWithPrefetchMethods();

        $queryProvider = $this->buildFieldsBuilder();

        $fields = $queryProvider->getFields($controller);
        $testField = $fields['test'];

        $this->assertSame('test', $testField->name);

        $this->assertCount(4, $testField->args);
        $this->assertSame('arg1', $testField->args[0]->name);
        $this->assertSame('arg2', $testField->args[1]->name);
        $this->assertSame('arg3', $testField->args[2]->name);
        $this->assertSame('arg4', $testField->args[3]->name);
    }

    public function testOutputTypeArgumentDescription(): void
    {
        $controller = new TestTypeWithDescriptions();

        $queryProvider = $this->buildFieldsBuilder();

        $fields = $queryProvider->getFields($controller);
        $testField = $fields['customField'];

        $this->assertCount(1, $testField->args);
        $this->assertSame('arg1', $testField->args[0]->name);
        $this->assertSame('Test argument description', $testField->args[0]->description);
    }

    public function testSecurityBadQuery(): void
    {
        $controller = new TestControllerWithBadSecurity();

        $queryProvider = $this->buildFieldsBuilder();

        $queries = $queryProvider->getQueries($controller);

        $this->assertCount(1, $queries);
        $query = $queries['testBadSecurity'];
        $this->assertSame('testBadSecurity', $query->name);

        $resolve = $query->resolveFn;

        $this->expectException(BadExpressionInSecurityException::class);
        $this->expectExceptionMessage('An error occurred while evaluating expression in @Security annotation of method "TheCodingMachine\GraphQLite\Fixtures\TestControllerWithBadSecurity::testBadSecurity()": Unexpected token "name" of value "is" around position 6 for expression `this is not valid expression language`.');
        $result = $resolve(new stdClass(), [], null, $this->createMock(ResolveInfo::class));
    }

    public function testQueryProviderWithNullableArray(): void
    {
        $controller = new TestControllerWithNullableArray();

        $queryProvider = $this->buildFieldsBuilder();

        $queries = $queryProvider->getQueries($controller);

        $this->assertCount(1, $queries);
        $usersQuery = $queries['test'];
        $this->assertSame('test', $usersQuery->name);

        $this->assertInstanceOf(NonNull::class, $usersQuery->args[0]->getType());
        $this->assertInstanceOf(ListOfType::class, $usersQuery->args[0]->getType()->getWrappedType());
        $this->assertInstanceOf(IntType::class, $usersQuery->args[0]->getType()->getWrappedType()->getWrappedType());
        $this->assertInstanceOf(NonNull::class, $usersQuery->getType());
        $this->assertInstanceOf(ListOfType::class, $usersQuery->getType()->getWrappedType());
        $this->assertInstanceOf(IntType::class, $usersQuery->getType()->getWrappedType()->getWrappedType());
    }

    public function testQueryProviderWithParamDateTime(): void
    {
        $controller = new TestControllerWithParamDateTime();

        $queryProvider = $this->buildFieldsBuilder();

        $this->expectException(CannotMapTypeException::class);
        $this->expectExceptionMessage('For parameter $dateTime, in TheCodingMachine\GraphQLite\Fixtures\TestControllerWithParamDateTime::test, type-hinting against DateTime is not allowed. Please use the DateTimeImmutable type instead.');
        $queries = $queryProvider->getQueries($controller);
    }

    public function testQueryProviderWithReturnDateTime(): void
    {
        $controller = new TestControllerWithReturnDateTime();

        $queryProvider = $this->buildFieldsBuilder();

        $this->expectException(CannotMapTypeException::class);
        $this->expectExceptionMessage('For return type of TheCodingMachine\GraphQLite\Fixtures\TestControllerWithReturnDateTime::test, type-hinting against DateTime is not allowed. Please use the DateTimeImmutable type instead.');
        $queries = $queryProvider->getQueries($controller);
    }

    public function testQueryProviderWithUnionInputParam(): void
    {
        $controller = new TestControllerWithUnionInputParam();

        $queryProvider = $this->buildFieldsBuilder();

        $this->expectException(CannotMapTypeException::class);
        $this->expectExceptionMessage('For parameter $testObject, in TheCodingMachine\GraphQLite\Fixtures\TestControllerWithUnionInputParam::test, parameter is type-hinted to "\TheCodingMachine\GraphQLite\Fixtures\TestObject|\TheCodingMachine\GraphQLite\Fixtures\TestObject2". Type-hinting a parameter to a union type is forbidden in GraphQL. Only return types can be union types.');
        $queries = $queryProvider->getQueries($controller);
    }

    public function testParameterAnnotationOnNonExistingParameterInSourceField(): void
    {
        $controller = new TestTypeWithSourceFieldInvalidParameterAnnotation();

        $queryProvider = $this->buildFieldsBuilder();

        $this->expectException(InvalidParameterException::class);
        $this->expectExceptionMessage('Could not find parameter "foo" declared in annotation "TheCodingMachine\\GraphQLite\\Annotations\\HideParameter". This annotation is itself declared in a SourceField attribute targeting resolver "TheCodingMachine\\GraphQLite\\Fixtures\\TestObject::getSibling()".');
        $fields = $queryProvider->getFields($controller);
    }

    public function testMagicField(): void
    {
        $controller = new TestTypeWithMagicProperty();

        $queryProvider = $this->buildFieldsBuilder();

        $fields = $queryProvider->getFields($controller);

        $this->assertCount(1, $fields);
        $query = $fields['foo'];
        $this->assertSame('foo', $query->name);
        $this->assertSame('Test MagicField description', $query->description);

        $resolve = $query->resolveFn;
        $result = $resolve(new TestTypeWithMagicProperty(), [], null, $this->createMock(ResolveInfo::class));

        $this->assertSame('foo', $result);

        $this->expectException(MissingMagicGetException::class);
        $this->expectExceptionMessage('You cannot use a @MagicField annotation on an object that does not implement the __get() magic method. The class stdClass must implement a __get() method.');
        $result = $resolve(new stdClass(), [], null, $this->createMock(ResolveInfo::class));
    }

    public function testProxyClassWithMagicPropertyOfPhpType(): void
    {
        $controller = new TestTypeWithMagicPropertyType();

        $queryProvider = $this->buildFieldsBuilder();

        $fields = $queryProvider->getFields($controller);

        $query = $fields['foo'];
        $this->assertSame('foo', $query->name);

        $resolve = $query->resolveFn;
        $result = $resolve(new TestTypeWithMagicProperty(), [], null, $this->createMock(ResolveInfo::class));

        $this->assertSame('foo', $result);
    }

    public function testSourceNameInSourceAndMagicFields(): void
    {
        $controller = new TestSourceNameType();
        $queryProvider = $this->buildFieldsBuilder();
        $fields = $queryProvider->getFields($controller);
        $source = new TestSourceName('foo value', 'bar value');

        $this->assertCount(2, $fields);

        $query = $fields['foo2'];
        $this->assertSame('foo2', $query->name);
        $resolve = $query->resolveFn;
        $result = $resolve($source, [], null, $this->createMock(ResolveInfo::class));
        $this->assertSame('foo value', $result);

        $query = $fields['bar2'];
        $this->assertSame('bar2', $query->name);
        $resolve = $query->resolveFn;
        $result = $resolve($source, [], null, $this->createMock(ResolveInfo::class));
        $this->assertSame('bar value', $result);
    }

    public function testDeprecationInDocblock(): void
    {
        $fieldsBuilder = $this->buildFieldsBuilder();
        $inputFields = $fieldsBuilder->getFields(
            new TestDeprecatedField(),
            'Test',
        );

        $this->assertCount(2, $inputFields);

        $this->assertEquals('this is deprecated', $inputFields['deprecatedField']->deprecationReason);
        $this->assertTrue( $inputFields['deprecatedField']->isDeprecated());
        $this->assertNull( $inputFields['name']->deprecationReason);
        $this->assertFalse( $inputFields['name']->isDeprecated());
    }
}
