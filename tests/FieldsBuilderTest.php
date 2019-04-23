<?php

namespace TheCodingMachine\GraphQLite;

use Doctrine\Common\Annotations\AnnotationReader;
use GraphQL\Type\Definition\BooleanType;
use GraphQL\Type\Definition\FloatType;
use GraphQL\Type\Definition\IDType;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\IntType;
use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\StringType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\UnionType;
use Symfony\Component\Cache\Simple\ArrayCache;
use TheCodingMachine\GraphQLite\Fixtures\TestController;
use TheCodingMachine\GraphQLite\Fixtures\TestControllerNoReturnType;
use TheCodingMachine\GraphQLite\Fixtures\TestControllerWithArrayParam;
use TheCodingMachine\GraphQLite\Fixtures\TestControllerWithArrayReturnType;
use TheCodingMachine\GraphQLite\Fixtures\TestControllerWithFailWith;
use TheCodingMachine\GraphQLite\Fixtures\TestControllerWithInputType;
use TheCodingMachine\GraphQLite\Fixtures\TestControllerWithInvalidInputType;
use TheCodingMachine\GraphQLite\Fixtures\TestControllerWithInvalidReturnType;
use TheCodingMachine\GraphQLite\Fixtures\TestControllerWithIterableParam;
use TheCodingMachine\GraphQLite\Fixtures\TestControllerWithIterableReturnType;
use TheCodingMachine\GraphQLite\Fixtures\TestFieldBadOutputType;
use TheCodingMachine\GraphQLite\Fixtures\TestObject;
use TheCodingMachine\GraphQLite\Fixtures\TestSelfType;
use TheCodingMachine\GraphQLite\Fixtures\TestSourceFieldBadOutputType;
use TheCodingMachine\GraphQLite\Fixtures\TestSourceFieldBadOutputType2;
use TheCodingMachine\GraphQLite\Fixtures\TestType;
use TheCodingMachine\GraphQLite\Fixtures\TestTypeId;
use TheCodingMachine\GraphQLite\Fixtures\TestTypeMissingAnnotation;
use TheCodingMachine\GraphQLite\Fixtures\TestTypeMissingField;
use TheCodingMachine\GraphQLite\Fixtures\TestTypeMissingReturnType;
use TheCodingMachine\GraphQLite\Fixtures\TestTypeWithFailWith;
use TheCodingMachine\GraphQLite\Fixtures\TestTypeWithSourceFieldInterface;
use TheCodingMachine\GraphQLite\Containers\EmptyContainer;
use TheCodingMachine\GraphQLite\Containers\BasicAutoWiringContainer;
use TheCodingMachine\GraphQLite\Mappers\CannotMapTypeException;
use TheCodingMachine\GraphQLite\Mappers\CannotMapTypeExceptionInterface;
use TheCodingMachine\GraphQLite\Reflection\CachedDocBlockFactory;
use TheCodingMachine\GraphQLite\Security\AuthenticationServiceInterface;
use TheCodingMachine\GraphQLite\Security\AuthorizationServiceInterface;
use TheCodingMachine\GraphQLite\Security\VoidAuthenticationService;
use TheCodingMachine\GraphQLite\Security\VoidAuthorizationService;
use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\GraphQLite\Types\DateTimeType;
use function var_dump;

class FieldsBuilderTest extends AbstractQueryProviderTest
{
    public function testQueryProvider()
    {
        $controller = new TestController();

        $queryProvider = $this->buildFieldsBuilder();

        $queries = $queryProvider->getQueries($controller);

        $this->assertCount(7, $queries);
        $usersQuery = $queries[0];
        $this->assertSame('test', $usersQuery->name);

        $this->assertCount(9, $usersQuery->args);
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
        $this->assertSame('TestObjectInput', $usersQuery->args[1]->getType()->getWrappedType()->getWrappedType()->getWrappedType()->name);

        $context = ['int' => 42, 'string' => 'foo', 'list' => [
            ['test' => 42],
            ['test' => 12],
        ],
            'boolean' => true,
            'float' => 4.2,
            'dateTimeImmutable' => '2017-01-01 01:01:01',
            'dateTime' => '2017-01-01 01:01:01',
            'id' => 42
        ];

        $resolve = $usersQuery->resolveFn;
        $result = $resolve('foo', $context);

        $this->assertInstanceOf(TestObject::class, $result);
        $this->assertSame('foo424212true4.22017010101010120170101010101default42', $result->getTest());

        unset($context['string']); // Testing null default value
        $result = $resolve('foo', $context);

        $this->assertSame('424212true4.22017010101010120170101010101default42', $result->getTest());
    }

    public function testMutations()
    {
        $controller = new TestController();

        $queryProvider = $this->buildFieldsBuilder();

        $mutations = $queryProvider->getMutations($controller);

        $this->assertCount(1, $mutations);
        $mutation = $mutations[0];
        $this->assertSame('mutation', $mutation->name);

        $resolve = $mutation->resolveFn;
        $result = $resolve('foo', ['testObject' => ['test' => 42]]);

        $this->assertInstanceOf(TestObject::class, $result);
        $this->assertEquals('42', $result->getTest());
    }

    public function testErrors()
    {
        $controller = new class
        {
            /**
             * @Query
             * @return string
             */
            public function test($noTypeHint): string
            {
                return 'foo';
            }
        };

        $queryProvider = $this->buildFieldsBuilder();

        $this->expectException(MissingTypeHintException::class);
        $queryProvider->getQueries($controller);
    }

    public function testQueryProviderWithFixedReturnType()
    {
        $controller = new TestController();

        $queryProvider = $this->buildFieldsBuilder();

        $queries = $queryProvider->getQueries($controller);

        $this->assertCount(7, $queries);
        $fixedQuery = $queries[1];

        $this->assertInstanceOf(IDType::class, $fixedQuery->getType());
    }

    public function testQueryProviderWithComplexFixedReturnType()
    {
        $controller = new TestController();

        $queryProvider = $this->buildFieldsBuilder();

        $queries = $queryProvider->getQueries($controller);

        $this->assertCount(7, $queries);
        $fixedQuery = $queries[6];

        $this->assertInstanceOf(NonNull::class, $fixedQuery->getType());
        $this->assertInstanceOf(ListOfType::class, $fixedQuery->getType()->getWrappedType());
        $this->assertInstanceOf(NonNull::class, $fixedQuery->getType()->getWrappedType()->getWrappedType());
        $this->assertInstanceOf(IDType::class, $fixedQuery->getType()->getWrappedType()->getWrappedType()->getWrappedType());
    }

    public function testNameFromAnnotation()
    {
        $controller = new TestController();

        $queryProvider = $this->buildFieldsBuilder();

        $queries = $queryProvider->getQueries($controller);

        $query = $queries[2];

        $this->assertSame('nameFromAnnotation', $query->name);
    }

    public function testSourceField()
    {
        $controller = new TestType();

        $queryProvider = $this->buildFieldsBuilder();

        $fields = $queryProvider->getFields($controller, true);

        $this->assertCount(3, $fields);

        $this->assertSame('customField', $fields['customField']->name);
        $this->assertSame('test', $fields['test']->name);
        // Test the "self" name resolution
        $this->assertSame('sibling', $fields['sibling']->name);
        $this->assertInstanceOf(NonNull::class, $fields['sibling']->getType());
        $this->assertInstanceOf(ObjectType::class, $fields['sibling']->getType()->getWrappedType());
        $this->assertSame('TestObject', $fields['sibling']->getType()->getWrappedType()->name);
    }

    public function testSourceFieldOnSelfType()
    {
        $queryProvider = $this->buildFieldsBuilder();

        $fields = $queryProvider->getSelfFields(TestSelfType::class, true);

        $this->assertCount(1, $fields);

        $this->assertSame('test', $fields['test']->name);
        $resolve = $fields['test']->resolveFn;
        $obj = new TestSelfType();
        $this->assertEquals('foo', $resolve($obj, []));
    }

    public function testLoggedInSourceField()
    {
        $queryProvider = new FieldsBuilder(
            $this->getAnnotationReader(),
            $this->getTypeMapper(),
            $this->getArgumentResolver(),
            new class implements AuthenticationServiceInterface {
                public function isLogged(): bool
                {
                    return true;
                }
            },
            new VoidAuthorizationService(),
            $this->getTypeResolver(),
            new CachedDocBlockFactory(new ArrayCache()),
            new NamingStrategy()
        );

        $fields = $queryProvider->getFields(new TestType(), true);
        $this->assertCount(4, $fields);

        $this->assertSame('testBool', $fields['testBool']->name);

    }

    public function testRightInSourceField()
    {
        $queryProvider = new FieldsBuilder(
            $this->getAnnotationReader(),
            $this->getTypeMapper(),
            $this->getArgumentResolver(),
            new VoidAuthenticationService(),
            new class implements AuthorizationServiceInterface {
                public function isAllowed(string $right): bool
                {
                    return true;
                }
            },
            $this->getTypeResolver(),
            new CachedDocBlockFactory(new ArrayCache()),
            new NamingStrategy()
        );

        $fields = $queryProvider->getFields(new TestType(), true);
        $this->assertCount(4, $fields);

        $this->assertSame('testRight', $fields['testRight']->name);

    }

    public function testMissingTypeAnnotation()
    {
        $queryProvider = $this->buildFieldsBuilder();

        $this->expectException(MissingAnnotationException::class);
        $queryProvider->getFields(new TestTypeMissingAnnotation(), true);
    }

    public function testSourceFieldDoesNotExists()
    {
        $queryProvider = $this->buildFieldsBuilder();

        $this->expectException(FieldNotFoundException::class);
        $this->expectExceptionMessage("There is an issue with a @SourceField annotation in class \"TheCodingMachine\GraphQLite\Fixtures\TestTypeMissingField\": Could not find a getter or a isser for field \"notExists\". Looked for: \"TheCodingMachine\GraphQLite\Fixtures\TestObject::notExists()\", \"TheCodingMachine\GraphQLite\Fixtures\TestObject::getNotExists()\", \"TheCodingMachine\GraphQLite\Fixtures\TestObject::isNotExists()");
        $queryProvider->getFields(new TestTypeMissingField(), true);
    }

    public function testSourceFieldHasMissingReturnType()
    {
        $queryProvider = $this->buildFieldsBuilder();

        $this->expectException(TypeMappingException::class);
        $this->expectExceptionMessage("Return type in TheCodingMachine\\GraphQLite\\Fixtures\\TestObjectMissingReturnType::getTest is missing a type-hint (or type-hinted to \"mixed\"). Please provide a better type-hint.");
        $queryProvider->getFields(new TestTypeMissingReturnType(), true);
    }

    public function testSourceFieldIsId()
    {
        $queryProvider = $this->buildFieldsBuilder();
        $fields = $queryProvider->getFields(new TestTypeId(), true);
        $this->assertCount(1, $fields);

        $this->assertSame('test', $fields['test']->name);
        $this->assertInstanceOf(NonNull::class, $fields['test']->getType());
        $this->assertInstanceOf(IDType::class, $fields['test']->getType()->getWrappedType());
    }

    public function testFromSourceFieldsInterface()
    {
        $queryProvider = new FieldsBuilder(
            $this->getAnnotationReader(),
            $this->getTypeMapper(),
            $this->getArgumentResolver(),
            new VoidAuthenticationService(),
            new VoidAuthorizationService(),
            $this->getTypeResolver(),
            new CachedDocBlockFactory(new ArrayCache()),
            new NamingStrategy()
        );
        $fields = $queryProvider->getFields(new TestTypeWithSourceFieldInterface(), true);
        $this->assertCount(1, $fields);

        $this->assertSame('test', $fields['test']->name);

    }

    public function testQueryProviderWithIterableClass()
    {
        $controller = new TestController();

        $queryProvider = $this->buildFieldsBuilder();

        $queries = $queryProvider->getQueries($controller);

        $this->assertCount(7, $queries);
        $iterableQuery = $queries[3];

        $this->assertInstanceOf(NonNull::class, $iterableQuery->getType());
        $this->assertInstanceOf(ListOfType::class, $iterableQuery->getType()->getWrappedType());
        $this->assertInstanceOf(NonNull::class, $iterableQuery->getType()->getWrappedType()->getWrappedType());
        $this->assertInstanceOf(ObjectType::class, $iterableQuery->getType()->getWrappedType()->getWrappedType()->getWrappedType());
        $this->assertSame('TestObject', $iterableQuery->getType()->getWrappedType()->getWrappedType()->getWrappedType()->name);
    }

    public function testQueryProviderWithIterable()
    {
        $queryProvider = $this->buildFieldsBuilder();

        $queries = $queryProvider->getQueries(new TestController());

        $this->assertCount(7, $queries);
        $iterableQuery = $queries[4];

        $this->assertInstanceOf(NonNull::class, $iterableQuery->getType());
        $this->assertInstanceOf(ListOfType::class, $iterableQuery->getType()->getWrappedType());
        $this->assertInstanceOf(NonNull::class, $iterableQuery->getType()->getWrappedType()->getWrappedType());
        $this->assertInstanceOf(ObjectType::class, $iterableQuery->getType()->getWrappedType()->getWrappedType()->getWrappedType());
        $this->assertSame('TestObject', $iterableQuery->getType()->getWrappedType()->getWrappedType()->getWrappedType()->name);
    }

    public function testNoReturnTypeError()
    {
        $queryProvider = $this->buildFieldsBuilder();
        $this->expectException(TypeMappingException::class);
        $queryProvider->getQueries(new TestControllerNoReturnType());
    }

    public function testQueryProviderWithUnion()
    {
        $controller = new TestController();

        $queryProvider = $this->buildFieldsBuilder();

        $queries = $queryProvider->getQueries($controller);

        $this->assertCount(7, $queries);
        $unionQuery = $queries[5];

        $this->assertInstanceOf(NonNull::class, $unionQuery->getType());
        $this->assertInstanceOf(UnionType::class, $unionQuery->getType()->getWrappedType());
        $this->assertInstanceOf(ObjectType::class, $unionQuery->getType()->getWrappedType()->getTypes()[0]);
        $this->assertSame('TestObject', $unionQuery->getType()->getWrappedType()->getTypes()[0]->name);
        $this->assertInstanceOf(ObjectType::class, $unionQuery->getType()->getWrappedType()->getTypes()[1]);
        $this->assertSame('TestObject2', $unionQuery->getType()->getWrappedType()->getTypes()[1]->name);
    }

    public function testQueryProviderWithInvalidInputType()
    {
        $controller = new TestControllerWithInvalidInputType();

        $queryProvider = $this->buildFieldsBuilder();

        $this->expectException(CannotMapTypeException::class);
        $this->expectExceptionMessage('For parameter $foo, in TheCodingMachine\GraphQLite\Fixtures\TestControllerWithInvalidInputType::test, cannot map class "Exception" to a known GraphQL input type. Check your TypeMapper configuration.');
        $queryProvider->getQueries($controller);
    }

    public function testQueryProviderWithInvalidReturnType()
    {
        $controller = new TestControllerWithInvalidReturnType();

        $queryProvider = $this->buildFieldsBuilder();

        $this->expectException(CannotMapTypeException::class);
        $this->expectExceptionMessage('For return type of TheCodingMachine\GraphQLite\Fixtures\TestControllerWithInvalidReturnType::test, cannot map class "Exception" to a known GraphQL type. Check your TypeMapper configuration.');
        $queryProvider->getQueries($controller);
    }

    public function testQueryProviderWithIterableReturnType()
    {
        $controller = new TestControllerWithIterableReturnType();

        $queryProvider = $this->buildFieldsBuilder();

        $this->expectException(TypeMappingException::class);
        $this->expectExceptionMessage('Return type in TheCodingMachine\GraphQLite\Fixtures\TestControllerWithIterableReturnType::test is type-hinted to "\ArrayObject", which is iterable. Please provide an additional @param in the PHPDoc block to further specify the type. For instance: @return \ArrayObject|User[]');
        $queryProvider->getQueries($controller);
    }

    public function testQueryProviderWithArrayReturnType()
    {
        $controller = new TestControllerWithArrayReturnType();

        $queryProvider = $this->buildFieldsBuilder();

        $this->expectException(TypeMappingException::class);
        $this->expectExceptionMessage('Return type in TheCodingMachine\GraphQLite\Fixtures\TestControllerWithArrayReturnType::test is type-hinted to array. Please provide an additional @return in the PHPDoc block to further specify the type of the array. For instance: @return string[]');
        $queryProvider->getQueries($controller);
    }

    public function testQueryProviderWithArrayParams()
    {
        $controller = new TestControllerWithArrayParam();

        $queryProvider = $this->buildFieldsBuilder();

        $this->expectException(TypeMappingException::class);
        $this->expectExceptionMessage('Parameter $params in TheCodingMachine\GraphQLite\Fixtures\TestControllerWithArrayParam::test is type-hinted to array. Please provide an additional @param in the PHPDoc block to further specify the type of the array. For instance: @param string[] $params.');
        $queryProvider->getQueries($controller);
    }

    public function testQueryProviderWithIterableParams()
    {
        $controller = new TestControllerWithIterableParam();

        $queryProvider = $this->buildFieldsBuilder();

        $this->expectException(TypeMappingException::class);
        $this->expectExceptionMessage('Parameter $params in TheCodingMachine\GraphQLite\Fixtures\TestControllerWithIterableParam::test is type-hinted to "\ArrayObject", which is iterable. Please provide an additional @param in the PHPDoc block to further specify the type. For instance: @param \ArrayObject|User[] $params.');
        $queryProvider->getQueries($controller);
    }

    public function testFailWith()
    {
        $controller = new TestControllerWithFailWith();

        $queryProvider = $this->buildFieldsBuilder();

        $queries = $queryProvider->getQueries($controller);

        $this->assertCount(1, $queries);
        $query = $queries[0];
        $this->assertSame('testFailWith', $query->name);

        $resolve = $query->resolveFn;
        $result = $resolve('foo', []);

        $this->assertNull($result);

        $this->assertInstanceOf(ObjectType::class, $query->getType());
    }

    public function testSourceFieldWithFailWith()
    {
        $controller = new TestTypeWithFailWith();

        $queryProvider = $this->buildFieldsBuilder();

        $fields = $queryProvider->getFields($controller, true);

        $this->assertCount(1, $fields);

        $this->assertSame('test', $fields['test']->name);
        $this->assertInstanceOf(StringType::class, $fields['test']->getType());


        $resolve = $fields['test']->resolveFn;
        $result = $resolve('foo', []);

        $this->assertNull($result);

        $this->assertInstanceOf(StringType::class, $fields['test']->getType());
    }

    public function testSourceFieldBadOutputTypeException()
    {
        $queryProvider = $this->buildFieldsBuilder();
        $this->expectException(CannotMapTypeExceptionInterface::class);
        $this->expectExceptionMessage('For @SourceField "test" declared in "TheCodingMachine\GraphQLite\Fixtures\TestSourceFieldBadOutputType", cannot find GraphQL type "[NotExists]". Check your TypeMapper configuration.');
        $queryProvider->getFields(new TestSourceFieldBadOutputType(), true);
    }

    public function testSourceFieldBadOutputType2Exception()
    {
        $queryProvider = $this->buildFieldsBuilder();
        $this->expectException(CannotMapTypeExceptionInterface::class);
        $this->expectExceptionMessage('For @SourceField "test" declared in "TheCodingMachine\GraphQLite\Fixtures\TestSourceFieldBadOutputType2", Syntax Error: Expected ], found <EOF>');
        $queryProvider->getFields(new TestSourceFieldBadOutputType2(), true);
    }

    public function testBadOutputTypeException()
    {
        $queryProvider = $this->buildFieldsBuilder();
        $this->expectException(CannotMapTypeExceptionInterface::class);
        $this->expectExceptionMessage('For return type of TheCodingMachine\GraphQLite\Fixtures\TestFieldBadOutputType::test, cannot find GraphQL type "[NotExists]". Check your TypeMapper configuration.');
        $queryProvider->getFields(new TestFieldBadOutputType(), true);
    }
}
