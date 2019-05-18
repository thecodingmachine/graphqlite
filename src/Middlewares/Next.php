<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Middlewares;

use GraphQL\Type\Definition\FieldDefinition;
use SplQueue;
use TheCodingMachine\GraphQLite\QueryFieldDescriptor;

/**
 * Iterate a queue of middlewares and execute them.
 */
final class Next implements FieldHandlerInterface
{
    /** @var FieldHandlerInterface */
    private $fallbackHandler;

    /** @var SplQueue */
    private $queue;

    /**
     * Clones the queue provided to allow re-use.
     *
     * @param FieldHandlerInterface $fallbackHandler Fallback handler to
     *     invoke when the queue is exhausted.
     */
    public function __construct(SplQueue $queue, FieldHandlerInterface $fallbackHandler)
    {
        $this->queue           = clone $queue;
        $this->fallbackHandler = $fallbackHandler;
    }

    public function handle(QueryFieldDescriptor $fieldDescriptor): ?FieldDefinition
    {
        if ($this->queue->isEmpty()) {
            return $this->fallbackHandler->handle($fieldDescriptor);
        }

        $middleware = $this->queue->dequeue();

        return $middleware->process($fieldDescriptor, $this);
    }
}
