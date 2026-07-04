<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Middlewares;

use SplQueue;
use TheCodingMachine\GraphQLite\InputObjectTypeDescriptor;
use TheCodingMachine\GraphQLite\Types\MutableInputObjectType;

final class InputObjectTypeMiddlewarePipe implements InputObjectTypeMiddlewareInterface
{
    private SplQueue $pipeline;

    public function __construct()
    {
        $this->pipeline = new SplQueue();
    }

    public function process(InputObjectTypeDescriptor $descriptor, InputObjectTypeHandlerInterface $next): MutableInputObjectType
    {
        return (new InputObjectTypeNext($this->pipeline, $next))->handle($descriptor);
    }

    public function pipe(InputObjectTypeMiddlewareInterface $middleware): void
    {
        $this->pipeline->enqueue($middleware);
    }
}
