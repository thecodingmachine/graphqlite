<?php

namespace TheCodingMachine\GraphQLite;

use PHPUnit\Framework\TestCase;
use ReflectionClass;

class AggregateQueryProviderTest extends TestCase
{
    private function getMockQueryProvider(): QueryProviderInterface
    {
        return new class implements QueryProviderInterface {
            public function getQueries(): array
            {
                $queryFieldRef = new ReflectionClass(QueryField::class);
                return [ $queryFieldRef->newInstanceWithoutConstructor() ];
            }

            public function getMutations(): array
            {
                $queryFieldRef = new ReflectionClass(QueryField::class);
                return [ $queryFieldRef->newInstanceWithoutConstructor() ];
            }
        };
    }

    public function testGetMutations(): void
    {
        $aggregateQueryProvider = new AggregateQueryProvider([$this->getMockQueryProvider(), $this->getMockQueryProvider()]);
        $this->assertCount(2, $aggregateQueryProvider->getMutations());

        $aggregateQueryProvider = new AggregateQueryProvider([]);
        $this->assertCount(0, $aggregateQueryProvider->getMutations());
    }

    public function testGetQueries(): void
    {
        $aggregateQueryProvider = new AggregateQueryProvider([$this->getMockQueryProvider(), $this->getMockQueryProvider()]);
        $this->assertCount(2, $aggregateQueryProvider->getQueries());

        $aggregateQueryProvider = new AggregateQueryProvider([]);
        $this->assertCount(0, $aggregateQueryProvider->getQueries());
    }
}
