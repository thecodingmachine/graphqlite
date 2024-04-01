<?php

namespace TheCodingMachine\GraphQLite;

class SchemaTest extends AbstractQueryProvider
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

            public function getSubscriptions(): array
            {
                return [];
            }
        };

        $schema = new Schema($queryProvider, $this->getTypeMapper(), $this->getTypeResolver(), $this->getRootTypeMapper());

        $fields = $schema->getQueryType()->getFields();
        $this->assertArrayHasKey('dummyQuery', $fields);
        $resolve = $fields['dummyQuery']->resolveFn;
        $this->assertSame('This is a placeholder query. Please create a query using the "Query" attribute.', $resolve());

        $fields = $schema->getMutationType()->getFields();
        $this->assertArrayHasKey('dummyMutation', $fields);
        $resolve = $fields['dummyMutation']->resolveFn;
        $this->assertSame('This is a placeholder mutation. Please create a mutation using the "Mutation" attribute.', $resolve());

        $fields = $schema->getSubscriptionType()->getFields();
        $this->assertArrayHasKey('dummySubscription', $fields);
        $resolve = $fields['dummySubscription']->resolveFn;
        $this->assertSame('This is a placeholder subscription. Please create a subscription using the "Subscription" attribute.', $resolve());
    }
}
