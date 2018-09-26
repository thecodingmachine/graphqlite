<?php

namespace TheCodingMachine\GraphQL\Controllers;

use Doctrine\Common\Annotations\AnnotationReader;
use GraphQL\Type\Definition\BooleanType;
use GraphQL\Type\Definition\FloatType;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\IntType;
use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\StringType;
use GraphQL\Type\Definition\ObjectType;
use TheCodingMachine\GraphQL\Controllers\Fixtures\TestController;
use TheCodingMachine\GraphQL\Controllers\Fixtures\TestObject;
use TheCodingMachine\GraphQL\Controllers\Fixtures\TestType;
use TheCodingMachine\GraphQL\Controllers\Fixtures\TestTypeMissingAnnotation;
use TheCodingMachine\GraphQL\Controllers\Fixtures\TestTypeMissingField;
use TheCodingMachine\GraphQL\Controllers\Registry\EmptyContainer;
use TheCodingMachine\GraphQL\Controllers\Registry\Registry;
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

        $queryProvider = new ControllerQueryProvider($controller, $this->getRegistry());

        $queries = $queryProvider->getQueries();

        $this->assertCount(3, $queries);
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

        $queryProvider = new ControllerQueryProvider($controller, $this->getRegistry());

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

        $queryProvider = new ControllerQueryProvider($controller, $this->getRegistry());

        $this->expectException(MissingTypeHintException::class);
        $queryProvider->getQueries();
    }

    public function testQueryProviderWithFixedReturnType()
    {
        $controller = new TestController();

        $queryProvider = new ControllerQueryProvider($controller, $this->getRegistry());

        $queries = $queryProvider->getQueries();

        $this->assertCount(3, $queries);
        $fixedQuery = $queries[1];

        $this->assertInstanceOf(ObjectType::class, $fixedQuery->getType());
        $this->assertSame('Test', $fixedQuery->getType()->name);
    }

    public function testNameFromAnnotation()
    {
        $controller = new TestController();

        $queryProvider = new ControllerQueryProvider($controller, $this->getRegistry());

        $queries = $queryProvider->getQueries();

        $query = $queries[2];

        $this->assertSame('nameFromAnnotation', $query->name);
    }

    public function testSourceField()
    {
        $controller = new TestType($this->getRegistry());

        $queryProvider = new ControllerQueryProvider($controller, $this->getRegistry());

        $fields = $queryProvider->getFields();

        $this->assertCount(2, $fields);

        $this->assertSame('customField', $fields[0]->name);
        $this->assertSame('test', $fields[1]->name);
    }

    public function testLoggedInSourceField()
    {
        $registry = new Registry(new EmptyContainer(),
            new VoidAuthorizationService(),
            new class implements AuthenticationServiceInterface {
                public function isLogged(): bool
                {
                    return true;
                }
            },
            new AnnotationReader(),
            $this->getTypeMapper(),
            $this->getHydrator());

        $queryProvider = new ControllerQueryProvider(new TestType($this->getRegistry()), $registry);
        $fields = $queryProvider->getFields();
        $this->assertCount(3, $fields);

        $this->assertSame('testBool', $fields[2]->name);

    }

    public function testRightInSourceField()
    {
        $registry = new Registry(new EmptyContainer(),
            new class implements AuthorizationServiceInterface {
                public function isAllowed(string $right): bool
                {
                    return true;
                }
            },
            new VoidAuthenticationService(),
            new AnnotationReader(),
            $this->getTypeMapper(),
            $this->getHydrator());

        $queryProvider = new ControllerQueryProvider(new TestType($this->getRegistry()), $registry);
        $fields = $queryProvider->getFields();
        $this->assertCount(3, $fields);

        $this->assertSame('testRight', $fields[2]->name);

    }

    public function testMissingTypeAnnotation()
    {
        $queryProvider = new ControllerQueryProvider(new TestTypeMissingAnnotation(), $this->getRegistry());

        $this->expectException(MissingAnnotationException::class);
        $queryProvider->getFields();
    }

    public function testSourceFieldDoesNotExists()
    {
        $queryProvider = new ControllerQueryProvider(new TestTypeMissingField(), $this->getRegistry());

        $this->expectException(FieldNotFoundException::class);
        $this->expectExceptionMessage("There is an issue with a @SourceField annotation in class \"TheCodingMachine\GraphQL\Controllers\Fixtures\TestTypeMissingField\": Could not find a getter or a isser for field \"notExists\". Looked for: \"TheCodingMachine\GraphQL\Controllers\Fixtures\TestObject::getNotExists()\", \"TheCodingMachine\GraphQL\Controllers\Fixtures\TestObject::isNotExists()");
        $queryProvider->getFields();
    }
}
