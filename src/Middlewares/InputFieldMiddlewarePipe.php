<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Middlewares;

use SplQueue;
use TheCodingMachine\GraphQLite\InputField;
use TheCodingMachine\GraphQLite\InputFieldDescriptor;

final class InputFieldMiddlewarePipe implements InputFieldMiddlewareInterface
{
    private SplQueue $pipeline;

    /**
     * Initializes the queue.
     */
    public function __construct()
    {
        $this->pipeline = new SplQueue();
    }

    /**
     * PSR-15 middleware invocation.
     *
     * Executes the internal pipeline, passing $handler as the "final
     * handler" in cases when the pipeline exhausts itself.
     */
    public function process(InputFieldDescriptor $inputFieldDescriptor, InputFieldHandlerInterface $inputFieldHandler): ?InputField
    {
        return (new InputNext($this->pipeline, $inputFieldHandler))->handle($inputFieldDescriptor);
    }

    /**
     * Attach middleware to the pipeline.
     */
    public function pipe(InputFieldMiddlewareInterface $middleware): void
    {
        $this->pipeline->enqueue($middleware);
    }
}
