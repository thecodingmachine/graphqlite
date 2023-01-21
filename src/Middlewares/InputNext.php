<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Middlewares;

use SplQueue;
use TheCodingMachine\GraphQLite\InputField;
use TheCodingMachine\GraphQLite\InputFieldDescriptor;

use function assert;

/**
 * Iterate a queue of middlewares and execute them.
 */
final class InputNext implements InputFieldHandlerInterface
{
    private SplQueue $queue;

    /**
     * Clones the queue provided to allow re-use.
     *
     * @param InputFieldHandlerInterface $fallbackHandler Fallback handler to
     *     invoke when the queue is exhausted.
     */
    public function __construct(SplQueue $queue, private readonly InputFieldHandlerInterface $fallbackHandler)
    {
        $this->queue = clone $queue;
    }

    public function handle(InputFieldDescriptor $inputFieldDescriptor): InputField|null
    {
        if ($this->queue->isEmpty()) {
            return $this->fallbackHandler->handle($inputFieldDescriptor);
        }

        $middleware = $this->queue->dequeue();
        assert($middleware instanceof InputFieldMiddlewareInterface);
        return $middleware->process($inputFieldDescriptor, $this);
    }
}
