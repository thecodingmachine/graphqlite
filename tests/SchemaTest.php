<?php

namespace TheCodingMachine\GraphQL\Controllers;

use PHPUnit\Framework\TestCase;

class SchemaTest extends AbstractQueryProviderTest
{

    public function testEmptyQuery()
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

        $schema = new Schema($queryProvider, $this->getTypeMapper(), $this->getTypeResolver());

        $fields = $schema->getQueryType()->getFields();
        $this->assertArrayHasKey('dummyQuery', $fields);

        $fields = $schema->getMutationType()->getFields();
        $this->assertArrayHasKey('dummyMutation', $fields);
    }
}
