<?php

namespace TheCodingMachine\GraphQLite\Middlewares;

use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\Type;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\Version;
use TheCodingMachine\GraphQLite\AbstractQueryProvider;
use TheCodingMachine\GraphQLite\Annotations\Cost;
use TheCodingMachine\GraphQLite\Annotations\Exceptions\IncompatibleAnnotationsException;
use TheCodingMachine\GraphQLite\Annotations\FailWith;
use TheCodingMachine\GraphQLite\Annotations\HideIfUnauthorized;
use TheCodingMachine\GraphQLite\Annotations\Logged;
use TheCodingMachine\GraphQLite\Annotations\MiddlewareAnnotationInterface;
use TheCodingMachine\GraphQLite\Annotations\MiddlewareAnnotations;
use TheCodingMachine\GraphQLite\Annotations\Right;
use TheCodingMachine\GraphQLite\QueryFieldDescriptor;
use TheCodingMachine\GraphQLite\Security\AuthenticationServiceInterface;
use TheCodingMachine\GraphQLite\Security\AuthorizationServiceInterface;
use TheCodingMachine\GraphQLite\Security\VoidAuthenticationService;
use TheCodingMachine\GraphQLite\Security\VoidAuthorizationService;

class CostFieldMiddlewareTest extends TestCase
{
    public function testIgnoresFieldsWithoutCustomCost(): void
    {
        $field = $this->stubField();

        $result = (new CostFieldMiddleware())->process($this->stubDescriptor([]), $this->stubFieldHandler($field));

        self::assertSame($field, $result);
    }

    public function testIgnoresNullField(): void
    {
        $result = (new CostFieldMiddleware())->process($this->stubDescriptor([]), $this->stubFieldHandler(null));

        self::assertNull($result);
    }

    #[DataProvider('setsComplexityFunctionProvider')]
    public function testSetsComplexityFunction(int $expectedComplexity, Cost $cost): void
    {
        $field = $this->stubField();

        $result = (new CostFieldMiddleware())->process($this->stubDescriptor([$cost]), $this->stubFieldHandler($field));

        self::assertNotNull($result->complexityFn);

        $resultComplexity = ($result->complexityFn)(8, [
            'take' => 10,
            'null' => null,
        ]);

        self::assertSame($expectedComplexity, $resultComplexity);
    }

    public static function setsComplexityFunctionProvider(): iterable
    {
        yield 'default 1 + children 8 #1' => [9, new Cost()];
        yield 'default 1 + children 8 #2' => [9, new Cost(defaultMultiplier: 100)];
        yield 'default 1 + children 8 #3' => [9, new Cost(multipliers: ['null'])];
        yield 'default 1 + children 8 #4' => [9, new Cost(multipliers: ['missing'])];

        yield 'set 3 + children 8 #1' => [11, new Cost(complexity: 3)];
        yield 'set 3 + children 8 #2' => [11, new Cost(complexity: 3, defaultMultiplier: 100)];
        yield 'set 3 + children 8 #3' => [11, new Cost(complexity: 3, multipliers: ['null'])];
        yield 'set 3 + children 8 #4' => [11, new Cost(complexity: 3, multipliers: ['missing'])];

        yield 'take 10 * (default 1 + children 8) #1' => [90, new Cost(multipliers: ['take'])];
        yield 'take 10 * (default 1 + children 8) #2' => [90, new Cost(multipliers: ['take'], defaultMultiplier: 100)];
        yield 'take 10 * (default 1 + children 8) #3' => [90, new Cost(multipliers: ['take', 'null'])];
        yield 'take 10 * (default 1 + children 8) #4' => [90, new Cost(multipliers: ['take', 'null'], defaultMultiplier: 100)];

        yield 'take 10 * (set 3 + children 8) #1' => [110, new Cost(complexity: 3, multipliers: ['take'])];
        yield 'take 10 * (set 3 + children 8) #2' => [110, new Cost(complexity: 3, multipliers: ['take'], defaultMultiplier: 100)];
        yield 'take 10 * (set 3 + children 8) #3' => [110, new Cost(complexity: 3, multipliers: ['take', 'null'])];
        yield 'take 10 * (set 3 + children 8) #4' => [110, new Cost(complexity: 3, multipliers: ['take', 'null'], defaultMultiplier: 100)];

        yield 'default multiplier 100 * (default 1 + children 8) #1' => [900, new Cost(multipliers: ['null'], defaultMultiplier: 100)];
        yield 'default multiplier 100 * (default 1 + children 8) #2' => [900, new Cost(multipliers: ['missing'], defaultMultiplier: 100)];
        yield 'default multiplier 100 * (default 1 + children 8) #3' => [900, new Cost(multipliers: ['null', 'missing'], defaultMultiplier: 100)];

    }

    #[DataProvider('addsCostInDescriptionProvider')]
    public function testAddsCostInDescription(string $expectedDescription, Cost $cost): void
    {
        if (Version::series() === '8.5') {
            $this->markTestSkipped('Broken on PHPUnit 8.');
        }

        $queryFieldDescriptor = $this->createMock(QueryFieldDescriptor::class);
        $queryFieldDescriptor->method('getMiddlewareAnnotations')
            ->willReturn(new MiddlewareAnnotations([$cost]));
        $queryFieldDescriptor->expects($this->once())
            ->method('withAddedCommentLines')
            ->with($expectedDescription)
            ->willReturnSelf();

        (new CostFieldMiddleware())->process($queryFieldDescriptor, $this->stubFieldHandler(null));
    }

    public static function addsCostInDescriptionProvider(): iterable
    {
        yield [
            "\nCost: complexity = 1, multipliers = [], defaultMultiplier = null",
            new Cost(),
        ];

        yield [
            "\nCost: complexity = 5, multipliers = [take], defaultMultiplier = 500",
            new Cost(complexity: 5, multipliers: ['take'], defaultMultiplier: 500)
        ];

        yield [
            "\nCost: complexity = 5, multipliers = [take, null], defaultMultiplier = null",
            new Cost(complexity: 5, multipliers: ['take', 'null'], defaultMultiplier: null)
        ];
    }

    /**
     * @param MiddlewareAnnotationInterface[] $annotations
     */
    private function stubDescriptor(array $annotations): QueryFieldDescriptor
    {
        $resolver = fn () => self::fail('Should not be called.');

        return new QueryFieldDescriptor(
            name: 'foo',
            type: Type::string(),
            resolver: $resolver,
            originalResolver: new ServiceResolver($resolver),
            middlewareAnnotations: new MiddlewareAnnotations($annotations),
        );
    }

    private function stubFieldHandler(FieldDefinition|null $field): FieldHandlerInterface
    {
        return new class ($field) implements FieldHandlerInterface {
            public function __construct(private readonly FieldDefinition|null $field)
            {
            }

            public function handle(QueryFieldDescriptor $fieldDescriptor): FieldDefinition|null
            {
                return $this->field;
            }
        };
    }

    private function stubField(): FieldDefinition
    {
        return new FieldDefinition([
            'name' => 'test',
            'resolve' => function () {},
        ]);
    }
}
