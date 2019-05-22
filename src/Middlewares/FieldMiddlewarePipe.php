<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Middlewares;

use GraphQL\Type\Definition\FieldDefinition;
use SplQueue;
use TheCodingMachine\GraphQLite\QueryFieldDescriptor;

final class FieldMiddlewarePipe implements FieldMiddlewareInterface
{
    /** @var SplQueue */
    private $pipeline;

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
    public function process(QueryFieldDescriptor $queryFieldDescriptor, FieldHandlerInterface $fieldHandler): ?FieldDefinition
    {
        return (new Next($this->pipeline, $fieldHandler))->handle($queryFieldDescriptor);
    }

    /**
     * Attach middleware to the pipeline.
     */
    public function pipe(FieldMiddlewareInterface $middleware): void
    {
        $this->pipeline->enqueue($middleware);
    }
}
