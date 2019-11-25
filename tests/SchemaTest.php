<?php

namespace TheCodingMachine\GraphQLite;

use PHPUnit\Framework\TestCase;

class SchemaTest extends AbstractQueryProviderTest
{

    public function testEmptyQuery(): void
    {
        $queryProvider = new class implements QueryProviderInterface {
            public function getQueries(): array
            {
                return [];
            }

            public function getMutations(): array
            {
                return [];
            }
        };

        $schema = new Schema($queryProvider, $this->getTypeMapper(), $this->getTypeResolver(), $this->getRootTypeMapper());

        $fields = $schema->getQueryType()->getFields();
        $this->assertArrayHasKey('dummyQuery', $fields);
        $resolve = $fields['dummyQuery']->resolveFn;
        $this->assertSame('This is a placeholder query. Please create a query using the @Query annotation.', $resolve());

        $fields = $schema->getMutationType()->getFields();
        $this->assertArrayHasKey('dummyMutation', $fields);
        $resolve = $fields['dummyMutation']->resolveFn;
        $this->assertSame('This is a placeholder mutation. Please create a mutation using the @Mutation annotation.', $resolve());
    }
}
