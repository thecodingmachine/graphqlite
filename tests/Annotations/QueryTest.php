<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Annotations;

use PHPUnit\Framework\TestCase;

/**
 * Exercises {@see AbstractGraphQLElement}-level behaviour via the {@see Query} subclass, which inherits
 * the description plumbing shared by Query/Mutation/Subscription.
 */
class QueryTest extends TestCase
{
    public function testDescriptionDefaultsToNull(): void
    {
        $query = new Query();
        $this->assertNull($query->getDescription());
    }

    public function testDescriptionFromConstructor(): void
    {
        $query = new Query(description: 'Explicit query description');
        $this->assertSame('Explicit query description', $query->getDescription());
    }

    public function testDescriptionFromAttributesArray(): void
    {
        $query = new Query(['description' => 'From attributes array']);
        $this->assertSame('From attributes array', $query->getDescription());
    }

    public function testDescriptionPreservesEmptyString(): void
    {
        // '' is the deliberate "explicit empty" signal that suppresses docblock fallback; it
        // must round-trip intact through the attribute so downstream resolvers can distinguish
        // it from "no description provided" (null).
        $query = new Query(description: '');
        $this->assertSame('', $query->getDescription());
    }

    public function testDescriptionAlongsideNameAndOutputType(): void
    {
        $query = new Query(name: 'myQuery', outputType: 'String!', description: 'Desc');
        $this->assertSame('myQuery', $query->getName());
        $this->assertSame('String!', $query->getOutputType());
        $this->assertSame('Desc', $query->getDescription());
    }

    public function testMutationInheritsDescriptionSupport(): void
    {
        $mutation = new Mutation(description: 'Mutation desc');
        $this->assertSame('Mutation desc', $mutation->getDescription());
    }

    public function testSubscriptionInheritsDescriptionSupport(): void
    {
        $subscription = new Subscription(description: 'Subscription desc');
        $this->assertSame('Subscription desc', $subscription->getDescription());
    }
}
