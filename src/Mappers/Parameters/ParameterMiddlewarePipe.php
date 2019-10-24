<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Mappers\Parameters;

use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\Type;
use ReflectionParameter;
use SplQueue;
use TheCodingMachine\GraphQLite\Annotations\ParameterAnnotations;
use TheCodingMachine\GraphQLite\Parameters\ParameterInterface;

final class ParameterMiddlewarePipe implements ParameterMiddlewareInterface
{
    /** @var SplQueue<ParameterMiddlewareInterface> */
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
    public function mapParameter(ReflectionParameter $parameter, DocBlock $docBlock, ?Type $paramTagType, ParameterAnnotations $parameterAnnotations, ParameterHandlerInterface $parameterMapper): ParameterInterface
    {
        return (new Next($this->pipeline, $parameterMapper))->mapParameter($parameter, $docBlock, $paramTagType, $parameterAnnotations);
    }

    /**
     * Attach middleware to the pipeline.
     */
    public function pipe(ParameterMiddlewareInterface $middleware): void
    {
        $this->pipeline->enqueue($middleware);
    }
}
