<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Middlewares;

use SplQueue;
use TheCodingMachine\GraphQLite\InputObjectTypeDescriptor;
use TheCodingMachine\GraphQLite\Types\MutableInputObjectType;

use function assert;

/**
 * Iterates the queue of input-object-type middlewares and dispatches each one.
 */
final class InputObjectTypeNext implements InputObjectTypeHandlerInterface
{
    private SplQueue $queue;

    public function __construct(SplQueue $queue, private readonly InputObjectTypeHandlerInterface $fallbackHandler)
    {
        $this->queue = clone $queue;
    }

    public function handle(InputObjectTypeDescriptor $descriptor): MutableInputObjectType
    {
        if ($this->queue->isEmpty()) {
            return $this->fallbackHandler->handle($descriptor);
        }

        $middleware = $this->queue->dequeue();

        assert($middleware instanceof InputObjectTypeMiddlewareInterface);

        return $middleware->process($descriptor, $this);
    }
}
