<?php

namespace TheCodingMachine\GraphQLite\Server\PersistedQuery;

use GraphQL\Server\OperationParams;
use PHPUnit\Framework\TestCase;

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