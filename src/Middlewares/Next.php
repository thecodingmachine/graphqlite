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
    private SplQueue $queue;

    /**
     * Clones the queue provided to allow re-use.
     *
     * @param FieldHandlerInterface $fallbackHandler fallback handler to
     *                                               invoke when the queue is exhausted
     */
    public function __construct(SplQueue $queue, private readonly FieldHandlerInterface $fallbackHandler)
    {
        $this->queue = clone $queue;
    }

    public function handle(QueryFieldDescriptor $fieldDescriptor): FieldDefinition|null
    {
        if ($this->queue->isEmpty()) {
            return $this->fallbackHandler->handle($fieldDescriptor);
        }

        return $this->queue->dequeue()->process($fieldDescriptor, $this);
    }
}
