<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Middlewares;

use SplQueue;
use TheCodingMachine\GraphQLite\InputField;
use TheCodingMachine\GraphQLite\InputFieldDescriptor;

/**
 * Iterate a queue of middlewares and execute them.
 */
final class InputNext implements InputFieldHandlerInterface
{
    /** @var InputFieldHandlerInterface */
    private $fallbackHandler;

    /** @var SplQueue */
    private $queue;

    /**
     * Clones the queue provided to allow re-use.
     *
     * @param InputFieldHandlerInterface $fallbackHandler Fallback handler to
     *     invoke when the queue is exhausted.
     */
    public function __construct(SplQueue $queue, InputFieldHandlerInterface $fallbackHandler)
    {
        $this->queue           = clone $queue;
        $this->fallbackHandler = $fallbackHandler;
    }

    public function handle(InputFieldDescriptor $inputFieldDescriptor): ?InputField
    {
        if ($this->queue->isEmpty()) {
            return $this->fallbackHandler->handle($inputFieldDescriptor);
        }

        $middleware = $this->queue->dequeue();

        return $middleware->process($inputFieldDescriptor, $this);
    }
}
