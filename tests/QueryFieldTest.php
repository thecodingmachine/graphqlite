<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite;

use GraphQL\Error\Error;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use PHPUnit\Framework\TestCase;
use TheCodingMachine\GraphQLite\Fixtures\TestObject;
use TheCodingMachine\GraphQLite\Middlewares\SourceResolver;
use TheCodingMachine\GraphQLite\Parameters\ParameterInterface;

class QueryFieldTest extends TestCase
{
    public function testExceptionsHandling(): void
    {
        $sourceResolver = new SourceResolver('getTest');
        $queryField = new QueryField('foo', Type::string(), [
            new class () implements ParameterInterface {
                public function resolve(?object $source, array $args, mixed $context, ResolveInfo $info): mixed
                {
                    throw new Error('boum');
                }
            },
        ], $sourceResolver, $sourceResolver, null, null, null, []);

        $resolve = $queryField->resolveFn;

        $this->expectException(Error::class);
        $resolve(new TestObject('foo'), ['arg' => 12], null, $this->createMock(ResolveInfo::class));
    }
}
