<?php

namespace TheCodingMachine\GraphQLite\Tests\Server\PersistedQuery;

use GraphQL\Server\OperationParams;
use PHPUnit\Framework\TestCase;
use TheCodingMachine\GraphQLite\Server\PersistedQuery\NotSupportedPersistedQueryLoader;
use TheCodingMachine\GraphQLite\Server\PersistedQuery\PersistedQueryNotSupportedException;

class NotSupportedPersistedQueryLoaderTest extends TestCase
{
    public function testThrowsNotSupportedException(): void
    {
        $this->expectException(PersistedQueryNotSupportedException::class);
        $this->expectExceptionMessage('Persisted queries are not supported by this server.');

        $loader = new NotSupportedPersistedQueryLoader();

        $loader('asd', OperationParams::create([]));
    }
}