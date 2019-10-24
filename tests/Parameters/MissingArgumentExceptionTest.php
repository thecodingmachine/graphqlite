<?php

namespace TheCodingMachine\GraphQLite\Parameters;

use PHPUnit\Framework\TestCase;
use TheCodingMachine\GraphQLite\Fixtures\Integration\Models\Filter;

class MissingArgumentExceptionTest extends TestCase
{

    public function testWrapWithFactoryContext(): void
    {
        $e = MissingArgumentException::create('foo');
        $e2 = MissingArgumentException::wrapWithFactoryContext($e, 'Input', [Filter::class, 'create']);

        $this->assertEquals('Expected argument \'foo\' was not provided in GraphQL input type \'Input\' used in factory \'TheCodingMachine\GraphQLite\Fixtures\Integration\Models\Filter::create()\'', $e2->getMessage());

        $e3 = MissingArgumentException::wrapWithFactoryContext($e, 'Input', function() {});

        $this->assertEquals('Expected argument \'foo\' was not provided in GraphQL input type \'Input\' used in factory \'\'', $e3->getMessage());

        $this->assertTrue($e->isClientSafe());
        $this->assertSame([], $e->getExtensions());
        $this->assertSame('graphql', $e->getCategory());
    }
}
