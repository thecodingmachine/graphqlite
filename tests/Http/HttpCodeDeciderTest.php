<?php

namespace TheCodingMachine\GraphQLite\Http;

use Exception;
use GraphQL\Error\ClientAware;
use GraphQL\Error\Error;
use GraphQL\Executor\ExecutionResult;
use PHPUnit\Framework\TestCase;

class HttpCodeDeciderTest extends TestCase
{

    public function testDecideHttpStatusCode(): void
    {
        $codeDecider = new HttpCodeDecider();

        $executionResult = new ExecutionResult(['someData'], []);

        $this->assertSame(200, $codeDecider->decideHttpStatusCode($executionResult));

        $graphqlError = new Error('Foo');

        $exception = new Exception('foo', 0);
        $errorCode0 = new Error('Foo', null, null, null, null, $exception);

        $exception401 = new Exception('foo', 401);
        $errorCode401 = new Error('Foo', null, null, null, null, $exception401);

        $exception404 = new Exception('foo', 404);
        $errorCode404 = new Error('Foo', null, null, null, null, $exception404);

        $exception600 = new Exception('foo', 600);
        $errorCode600 = new Error('Foo', null, null, null, null, $exception600);

        $clientAwareException = new class extends Exception implements ClientAware {
            public function isClientSafe()
            {
                return true;
            }

            public function getCategory()
            {
                return 'foo';
            }
        };
        $clientAwareError = new Error('Foo', null, null, null, null, $clientAwareException);

        $executionResult = new ExecutionResult(null, [ $errorCode0 ]);
        $this->assertSame(500, $codeDecider->decideHttpStatusCode($executionResult));

        $executionResult = new ExecutionResult(['foo'], [ $errorCode401, $errorCode404, $errorCode600 ]);
        $this->assertSame(404, $codeDecider->decideHttpStatusCode($executionResult));

        $executionResult = new ExecutionResult(null, [ $graphqlError ]);
        $this->assertSame(400, $codeDecider->decideHttpStatusCode($executionResult));

        $executionResult = new ExecutionResult(null, [ $clientAwareError ]);
        $this->assertSame(400, $codeDecider->decideHttpStatusCode($executionResult));
    }
}
