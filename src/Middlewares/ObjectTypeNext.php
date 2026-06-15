<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Middlewares;

use SplQueue;
use TheCodingMachine\GraphQLite\ObjectTypeDescriptor;
use TheCodingMachine\GraphQLite\Types\MutableObjectType;

use function assert;

/**
 * Iterates the queue of object-type middlewares and dispatches each one.
 */
final class ObjectTypeNext implements ObjectTypeHandlerInterface
{
    private SplQueue $queue;

    public function __construct(SplQueue $queue, private readonly ObjectTypeHandlerInterface $fallbackHandler)
    {
        $this->queue = clone $queue;
    }

    public function handle(ObjectTypeDescriptor $descriptor): MutableObjectType
    {
        if ($this->queue->isEmpty()) {
            return $this->fallbackHandler->handle($descriptor);
        }

        $middleware = $this->queue->dequeue();
        assert($middleware instanceof ObjectTypeMiddlewareInterface);
        return $middleware->process($descriptor, $this);
    }
}
