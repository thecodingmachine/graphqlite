<?php

namespace TheCodingMachine\GraphQL\Controllers;

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
use TheCodingMachine\GraphQL\Controllers\Fixtures\TestController;
use TheCodingMachine\GraphQL\Controllers\Fixtures\TestControllerNoReturnType;
use TheCodingMachine\GraphQL\Controllers\Fixtures\TestControllerWithArrayParam;
use TheCodingMachine\GraphQL\Controllers\Fixtures\TestControllerWithArrayReturnType;
use TheCodingMachine\GraphQL\Controllers\Fixtures\TestControllerWithInvalidInputType;
use TheCodingMachine\GraphQL\Controllers\Fixtures\TestControllerWithInvalidReturnType;
use TheCodingMachine\GraphQL\Controllers\Fixtures\TestControllerWithIterableParam;
use TheCodingMachine\GraphQL\Controllers\Fixtures\TestControllerWithIterableReturnType;
use TheCodingMachine\GraphQL\Controllers\Fixtures\TestObject;
use TheCodingMachine\GraphQL\Controllers\Fixtures\TestType;
use TheCodingMachine\GraphQL\Controllers\Fixtures\TestTypeId;
use TheCodingMachine\GraphQL\Controllers\Fixtures\TestTypeMissingAnnotation;
use TheCodingMachine\GraphQL\Controllers\Fixtures\TestTypeMissingField;
use TheCodingMachine\GraphQL\Controllers\Fixtures\TestTypeMissingReturnType;
use TheCodingMachine\GraphQL\Controllers\Fixtures\TestTypeWithSourceFieldInterface;
use TheCodingMachine\GraphQL\Controllers\Containers\EmptyContainer;
use TheCodingMachine\GraphQL\Controllers\Containers\BasicAutoWiringContainer;
use TheCodingMachine\GraphQL\Controllers\Mappers\CannotMapTypeException;
use TheCodingMachine\GraphQL\Controllers\Security\AuthenticationServiceInterface;
use TheCodingMachine\GraphQL\Controllers\Security\AuthorizationServiceInterface;
use TheCodingMachine\GraphQL\Controllers\Security\VoidAuthenticationService;
use TheCodingMachine\GraphQL\Controllers\Security\VoidAuthorizationService;
use TheCodingMachine\GraphQL\Controllers\Annotations\Query;
use TheCodingMachine\GraphQL\Controllers\Types\DateTimeType;

class ControllerQueryProviderTest extends AbstractQueryProviderTest
{
    public function testQueryProvider()
    {
        $controller = new TestController();

        $queryProvider = $this->buildControllerQueryProvider($controller);

        $queries = $queryProvider->getQueries();

        $this->assertCount(6, $queries);
        $usersQuery = $queries[0];
        $this->assertSame('test', $usersQuery->name);

        $this->assertCount(8, $usersQuery->args);
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
        $this->assertSame('TestObject', $usersQuery->args[1]->getType()->getWrappedType()->getWrappedType()->getWrappedType()->name);

        $context = ['int' => 42, 'string' => 'foo', 'list' => [
            ['test' => 42],
            ['test' => 12],
        ],
            'boolean' => true,
            'float' => 4.2,
            'dateTimeImmutable' => '2017-01-01 01:01:01',
            'dateTime' => '2017-01-01 01:01:01'
        ];

        $resolve = $usersQuery->resolveFn;
        $result = $resolve('foo', $context);

        $this->assertInstanceOf(TestObject::class, $result);
        $this->assertSame('foo424212true4.22017010101010120170101010101default', $result->getTest());

        unset($context['string']); // Testing null default value
        $result = $resolve('foo', $context);

        $this->assertSame('424212true4.22017010101010120170101010101default', $result->getTest());
    }

    public function testMutations()
    {
        $controller = new TestController();

        $queryProvider = $this->buildControllerQueryProvider($controller);

        $mutations = $queryProvider->getMutations();

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

        $queryProvider = $this->buildControllerQueryProvider($controller);

        $this->expectException(MissingTypeHintException::class);
        $queryProvider->getQueries();
    }

    public function testQueryProviderWithFixedReturnType()
    {
        $controller = new TestController();

        $queryProvider = $this->buildControllerQueryProvider($controller);

        $queries = $queryProvider->getQueries();

        $this->assertCount(6, $queries);
        $fixedQuery = $queries[1];

        $this->assertInstanceOf(StringType::class, $fixedQuery->getType());
    }

    public function testNameFromAnnotation()
    {
        $controller = new TestController();

        $queryProvider = $this->buildControllerQueryProvider($controller);

        $queries = $queryProvider->getQueries();

        $query = $queries[2];

        $this->assertSame('nameFromAnnotation', $query->name);
    }

    public function testSourceField()
    {
        $controller = new TestType($this->getRegistry());

        $queryProvider = $this->buildControllerQueryProvider($controller);

        $fields = $queryProvider->getFields();

        $this->assertCount(2, $fields);

        $this->assertSame('customField', $fields['customField']->name);
        $this->assertSame('test', $fields['test']->name);
    }

    public function testLoggedInSourceField()
    {
        $queryProvider = new ControllerQueryProvider(
            new TestType(),
            $this->getAnnotationReader(),
            $this->getTypeMapper(),
            $this->getHydrator(),
            new class implements AuthenticationServiceInterface {
                public function isLogged(): bool
                {
                    return true;
                }
            },
            new VoidAuthorizationService(),
            new EmptyContainer()
        );

        $fields = $queryProvider->getFields();
        $this->assertCount(3, $fields);

        $this->assertSame('testBool', $fields['testBool']->name);

    }

    public function testRightInSourceField()
    {
        $queryProvider = new ControllerQueryProvider(
            new TestType(),
            $this->getAnnotationReader(),
            $this->getTypeMapper(),
            $this->getHydrator(),
            new VoidAuthenticationService(),
            new class implements AuthorizationServiceInterface {
                public function isAllowed(string $right): bool
                {
                    return true;
                }
            },new EmptyContainer()
        );

        $fields = $queryProvider->getFields();
        $this->assertCount(3, $fields);

        $this->assertSame('testRight', $fields['testRight']->name);

    }

    public function testMissingTypeAnnotation()
    {
        $queryProvider = $this->buildControllerQueryProvider(new TestTypeMissingAnnotation());

        $this->expectException(MissingAnnotationException::class);
        $queryProvider->getFields();
    }

    public function testSourceFieldDoesNotExists()
    {
        $queryProvider = $this->buildControllerQueryProvider(new TestTypeMissingField());

        $this->expectException(FieldNotFoundException::class);
        $this->expectExceptionMessage("There is an issue with a @SourceField annotation in class \"TheCodingMachine\GraphQL\Controllers\Fixtures\TestTypeMissingField\": Could not find a getter or a isser for field \"notExists\". Looked for: \"TheCodingMachine\GraphQL\Controllers\Fixtures\TestObject::notExists()\", \"TheCodingMachine\GraphQL\Controllers\Fixtures\TestObject::getNotExists()\", \"TheCodingMachine\GraphQL\Controllers\Fixtures\TestObject::isNotExists()");
        $queryProvider->getFields();
    }

    public function testSourceFieldHasMissingReturnType()
    {
        $queryProvider = $this->buildControllerQueryProvider(new TestTypeMissingReturnType());

        $this->expectException(TypeMappingException::class);
        $this->expectExceptionMessage("Return type in TheCodingMachine\\GraphQL\\Controllers\\Fixtures\\TestObjectMissingReturnType::getTest is missing a type-hint (or type-hinted to \"mixed\"). Please provide a better type-hint.");
        $queryProvider->getFields();
    }

    public function testSourceFieldIsId()
    {
        $queryProvider = $this->buildControllerQueryProvider(new TestTypeId());
        $fields = $queryProvider->getFields();
        $this->assertCount(1, $fields);

        $this->assertSame('test', $fields['test']->name);
        $this->assertInstanceOf(NonNull::class, $fields['test']->getType());
        $this->assertInstanceOf(IDType::class, $fields['test']->getType()->getWrappedType());
    }

    public function testFromSourceFieldsInterface()
    {
        $queryProvider = new ControllerQueryProvider(
            new TestTypeWithSourceFieldInterface(),
            $this->getAnnotationReader(),
            $this->getTypeMapper(),
            $this->getHydrator(),
            new VoidAuthenticationService(),
            new VoidAuthorizationService(),
            new EmptyContainer()
        );
        $fields = $queryProvider->getFields();
        $this->assertCount(1, $fields);

        $this->assertSame('test', $fields['test']->name);

    }

    public function testQueryProviderWithIterableClass()
    {
        $controller = new TestController();

        $queryProvider = $this->buildControllerQueryProvider($controller);

        $queries = $queryProvider->getQueries();

        $this->assertCount(6, $queries);
        $iterableQuery = $queries[3];

        $this->assertInstanceOf(NonNull::class, $iterableQuery->getType());
        $this->assertInstanceOf(ListOfType::class, $iterableQuery->getType()->getWrappedType());
        $this->assertInstanceOf(NonNull::class, $iterableQuery->getType()->getWrappedType()->getWrappedType());
        $this->assertInstanceOf(ObjectType::class, $iterableQuery->getType()->getWrappedType()->getWrappedType()->getWrappedType());
        $this->assertSame('TestObject', $iterableQuery->getType()->getWrappedType()->getWrappedType()->getWrappedType()->name);
    }

    public function testQueryProviderWithIterable()
    {
        $controller = new TestController();

        $queryProvider = $this->buildControllerQueryProvider($controller);

        $queries = $queryProvider->getQueries();

        $this->assertCount(6, $queries);
        $iterableQuery = $queries[4];

        $this->assertInstanceOf(NonNull::class, $iterableQuery->getType());
        $this->assertInstanceOf(ListOfType::class, $iterableQuery->getType()->getWrappedType());
        $this->assertInstanceOf(NonNull::class, $iterableQuery->getType()->getWrappedType()->getWrappedType());
        $this->assertInstanceOf(ObjectType::class, $iterableQuery->getType()->getWrappedType()->getWrappedType()->getWrappedType());
        $this->assertSame('TestObject', $iterableQuery->getType()->getWrappedType()->getWrappedType()->getWrappedType()->name);
    }

    public function testNoReturnTypeError()
    {
        $queryProvider = $this->buildControllerQueryProvider(new TestControllerNoReturnType());
        $this->expectException(TypeMappingException::class);
        $queryProvider->getQueries();
    }

    public function testQueryProviderWithUnion()
    {
        $controller = new TestController();

        $queryProvider = $this->buildControllerQueryProvider($controller);

        $queries = $queryProvider->getQueries();

        $this->assertCount(6, $queries);
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

        $queryProvider = $this->buildControllerQueryProvider($controller);

        $this->expectException(CannotMapTypeException::class);
        $this->expectExceptionMessage('For parameter $foo, in TheCodingMachine\GraphQL\Controllers\Fixtures\TestControllerWithInvalidInputType::test, cannot map class "Exception" to a known GraphQL input type. Check your TypeMapper configuration.');
        $queryProvider->getQueries();
    }

    public function testQueryProviderWithInvalidReturnType()
    {
        $controller = new TestControllerWithInvalidReturnType();

        $queryProvider = $this->buildControllerQueryProvider($controller);

        $this->expectException(CannotMapTypeException::class);
        $this->expectExceptionMessage('For return type of TheCodingMachine\GraphQL\Controllers\Fixtures\TestControllerWithInvalidReturnType::test, cannot map class "Exception" to a known GraphQL type. Check your TypeMapper configuration.');
        $queryProvider->getQueries();
    }

    public function testQueryProviderWithIterableReturnType()
    {
        $controller = new TestControllerWithIterableReturnType();

        $queryProvider = $this->buildControllerQueryProvider($controller);

        $this->expectException(TypeMappingException::class);
        $this->expectExceptionMessage('Return type in TheCodingMachine\GraphQL\Controllers\Fixtures\TestControllerWithIterableReturnType::test is type-hinted to "\ArrayObject", which is iterable. Please provide an additional @param in the PHPDoc block to further specify the type. For instance: @return \ArrayObject|User[]');
        $queryProvider->getQueries();
    }

    public function testQueryProviderWithArrayReturnType()
    {
        $controller = new TestControllerWithArrayReturnType();

        $queryProvider = $this->buildControllerQueryProvider($controller);

        $this->expectException(TypeMappingException::class);
        $this->expectExceptionMessage('Return type in TheCodingMachine\GraphQL\Controllers\Fixtures\TestControllerWithArrayReturnType::test is type-hinted to array. Please provide an additional @return in the PHPDoc block to further specify the type of the array. For instance: @return string[]');
        $queryProvider->getQueries();
    }

    public function testQueryProviderWithArrayParams()
    {
        $controller = new TestControllerWithArrayParam();

        $queryProvider = $this->buildControllerQueryProvider($controller);

        $this->expectException(TypeMappingException::class);
        $this->expectExceptionMessage('Parameter $params in TheCodingMachine\GraphQL\Controllers\Fixtures\TestControllerWithArrayParam::test is type-hinted to array. Please provide an additional @param in the PHPDoc block to further specify the type of the array. For instance: @param string[] $params.');
        $queryProvider->getQueries();
    }

    public function testQueryProviderWithIterableParams()
    {
        $controller = new TestControllerWithIterableParam();

        $queryProvider = $this->buildControllerQueryProvider($controller);

        $this->expectException(TypeMappingException::class);
        $this->expectExceptionMessage('Parameter $params in TheCodingMachine\GraphQL\Controllers\Fixtures\TestControllerWithIterableParam::test is type-hinted to "\ArrayObject", which is iterable. Please provide an additional @param in the PHPDoc block to further specify the type. For instance: @param \ArrayObject|User[] $params.');
        $queryProvider->getQueries();
    }
}
