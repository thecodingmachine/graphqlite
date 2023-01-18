<?php

namespace TheCodingMachine\GraphQLite\Exceptions;

use GraphQL\Error\Error;
use PHPUnit\Framework\TestCase;

class ErrorHandlerTest extends TestCase
{

    public function testErrorFormatter()
    {
        $exception = new GraphQLException('foo', 0, null, 'MyCategory', ['field' => 'foo']);
        $error = new Error('foo', null, null, [], null, $exception);
        $formattedError = WebonyxErrorHandler::errorFormatter($error);
        $this->assertSame([
            'message' => 'foo',
            'extensions' => [
                'field' => 'foo'
            ]
        ], $formattedError);
    }

    public function testErrorHandler()
    {
        $exception = new GraphQLException('foo', 0, null, 'MyCategory', ['field' => 'foo']);
        $error = new Error('bar', null, null, [], null, $exception);
        $aggregateException = new GraphQLAggregateException();
        $aggregateException->add($exception);
        $aggregateException->add($exception);
        $aggregateError = new Error('bar', null, null, [], null, $aggregateException);
        $formattedError = WebonyxErrorHandler::errorHandler([$error, $aggregateError], [WebonyxErrorHandler::class, 'errorFormatter']);

        $this->assertSame([
            [
                'message' => 'bar',
                'extensions' => [
                    'field' => 'foo'
                ]
            ],
            [
                'message' => 'foo',
                'extensions' => [
                    'field' => 'foo',
                ]
            ],
            [
                'message' => 'foo',
                'extensions' => [
                    'field' => 'foo',
                ]
            ]], $formattedError);
    }
}
