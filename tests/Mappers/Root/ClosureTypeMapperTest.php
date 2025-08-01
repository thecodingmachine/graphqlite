<?php

namespace TheCodingMachine\GraphQLite\Mappers\Root;

use Closure;
use Generator;
use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\NamedType;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\OutputType;
use GraphQL\Type\Definition\StringType;
use GraphQL\Type\Definition\Type as GraphQLType;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\Fqsen;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Callable_;
use phpDocumentor\Reflection\Types\CallableParameter;
use phpDocumentor\Reflection\Types\Compound;
use phpDocumentor\Reflection\Types\Nullable;
use phpDocumentor\Reflection\Types\Object_;
use phpDocumentor\Reflection\Types\String_;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionMethod;
use TheCodingMachine\GraphQLite\AbstractQueryProvider;
use TheCodingMachine\GraphQLite\Fixtures\TestObject;
use TheCodingMachine\GraphQLite\Fixtures\TestObject2;
use TheCodingMachine\GraphQLite\Mappers\CannotMapTypeException;

#[CoversClass(ClosureTypeMapper::class)]
#[CoversClass(CannotMapTypeException::class)]
class ClosureTypeMapperTest extends AbstractQueryProvider
{
    public function testMapsCallableReturnTypeUsingTopRootMapper(): void
    {
        $reflection = new ReflectionMethod(__CLASS__, 'testSkipsNonCallables');
        $docBlock = new DocBlock();

        $returnType = new String_();

        $topRootMapper = $this->createMock(RootTypeMapperInterface::class);
        $topRootMapper->expects($this->once())
            ->method('toGraphQLOutputType')
            ->with($returnType, null, $reflection, $docBlock)
            ->willReturn(GraphQLType::string());

        $mapper = new ClosureTypeMapper(
            $this->createMock(RootTypeMapperInterface::class),
            $topRootMapper,
        );

        $type = new Compound([
            new Callable_(returnType: $returnType),
            new Object_(new Fqsen('\\' . Closure::class))
        ]);

        $result = $mapper->toGraphQLOutputType($type, null, $reflection, $docBlock);

        $this->assertSame(GraphQLType::string(), $result);
    }

    public function testThrowsWhenUsingCallable(): void
    {
        $this->expectExceptionObject(CannotMapTypeException::createForUnexpectedCallable());

        $mapper = new ClosureTypeMapper(
            $this->createMock(RootTypeMapperInterface::class),
            $this->createMock(RootTypeMapperInterface::class)
        );

        $mapper->toGraphQLOutputType(new Callable_(), null, new ReflectionMethod(__CLASS__, 'testSkipsNonCallables'), new DocBlock());
    }

    public function testThrowsWhenUsingClosureWithParameters(): void
    {
        $this->expectExceptionObject(CannotMapTypeException::createForUnexpectedClosureParameters());

        $mapper = new ClosureTypeMapper(
            $this->createMock(RootTypeMapperInterface::class),
            $this->createMock(RootTypeMapperInterface::class)
        );

        $type = new Compound([
            new Callable_(
                parameters: [
                    new CallableParameter(new String_())
                ],
                returnType: new String_()
            ),
            new Object_(new Fqsen('\\' . Closure::class))
        ]);

        $mapper->toGraphQLOutputType($type, null, new ReflectionMethod(__CLASS__, 'testSkipsNonCallables'), new DocBlock());
    }

    public function testThrowsWhenUsingClosureWithoutReturnType(): void
    {
        $this->expectExceptionObject(CannotMapTypeException::createForMissingClosureReturnType());

        $mapper = new ClosureTypeMapper(
            $this->createMock(RootTypeMapperInterface::class),
            $this->createMock(RootTypeMapperInterface::class)
        );

        $type = new Compound([
            new Callable_(),
            new Object_(new Fqsen('\\' . Closure::class))
        ]);

        $mapper->toGraphQLOutputType($type, null, new ReflectionMethod(__CLASS__, 'testSkipsNonCallables'), new DocBlock());
    }

    public function testThrowsWhenUsingClosureAsInputType(): void
    {
        $this->expectExceptionObject(CannotMapTypeException::createForClosureAsInput());

        $mapper = new ClosureTypeMapper(
            $this->createMock(RootTypeMapperInterface::class),
            $this->createMock(RootTypeMapperInterface::class)
        );

        $mapper->toGraphQLInputType(new Callable_(), null, 'arg1', new ReflectionMethod(__CLASS__, 'testSkipsNonCallables'), new DocBlock());
    }

    #[DataProvider('skipsNonCallablesProvider')]
    public function testSkipsNonCallables(callable $createType): void
    {
        $type = $createType();
        $reflection = new ReflectionMethod(__CLASS__, 'testSkipsNonCallables');
        $docBlock = new DocBlock();

        $next = $this->createMock(RootTypeMapperInterface::class);
        $next->expects($this->once())
            ->method('toGraphQLOutputType')
            ->with($type, null, $reflection, $docBlock)
            ->willReturn(GraphQLType::string());
        $next->expects($this->once())
            ->method('toGraphQLInputType')
            ->with($type, null, 'arg1', $reflection, $docBlock)
            ->willReturn(GraphQLType::int());
        $next->expects($this->once())
            ->method('mapNameToType')
            ->with('Name')
            ->willReturn(GraphQLType::float());

        $mapper = new ClosureTypeMapper($next, $this->createMock(RootTypeMapperInterface::class));

        $this->assertSame(GraphQLType::string(), $mapper->toGraphQLOutputType($type, null, $reflection, $docBlock));
        $this->assertSame(GraphQLType::int(), $mapper->toGraphQLInputType($type, null, 'arg1', $reflection, $docBlock));
        $this->assertSame(GraphQLType::float(), $mapper->mapNameToType('Name'));
    }

    public static function skipsNonCallablesProvider(): iterable
    {
        yield [fn () => new Object_()];
        yield [fn () => new Array_()];
        yield [fn () => new String_()];
    }
}
