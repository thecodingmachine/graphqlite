<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Middlewares;

use SplQueue;
use TheCodingMachine\GraphQLite\ObjectTypeDescriptor;
use TheCodingMachine\GraphQLite\Types\MutableObjectType;

final class ObjectTypeMiddlewarePipe implements ObjectTypeMiddlewareInterface
{
    private SplQueue $pipeline;

    public function __construct()
    {
        $this->pipeline = new SplQueue();
    }

    public function process(ObjectTypeDescriptor $descriptor, ObjectTypeHandlerInterface $next): MutableObjectType
    {
        return (new ObjectTypeNext($this->pipeline, $next))->handle($descriptor);
    }

    public function pipe(ObjectTypeMiddlewareInterface $middleware): void
    {
        $this->pipeline->enqueue($middleware);
    }
}
