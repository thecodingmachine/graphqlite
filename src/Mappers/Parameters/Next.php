<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Mappers\Parameters;

use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\Type;
use ReflectionParameter;
use SplQueue;
use TheCodingMachine\GraphQLite\Annotations\ParameterAnnotations;
use TheCodingMachine\GraphQLite\Parameters\ParameterInterface;
use function assert;

/**
 * Iterate a queue of middlewares and execute them.
 */
final class Next implements ParameterHandlerInterface
{
    /** @var ParameterHandlerInterface */
    private $fallbackHandler;

    /** @var SplQueue */
    private $queue;

    /**
     * Clones the queue provided to allow re-use.
     *
     * @param ParameterHandlerInterface $fallbackHandler Fallback handler to
     *     invoke when the queue is exhausted.
     */
    public function __construct(SplQueue $queue, ParameterHandlerInterface $fallbackHandler)
    {
        $this->queue           = clone $queue;
        $this->fallbackHandler = $fallbackHandler;
    }

    public function mapParameter(ReflectionParameter $parameter, DocBlock $docBlock, ?Type $paramTagType, ParameterAnnotations $parameterAnnotations): ParameterInterface
    {
        if ($this->queue->isEmpty()) {
            return $this->fallbackHandler->mapParameter($parameter, $docBlock, $paramTagType, $parameterAnnotations);
        }

        $middleware = $this->queue->dequeue();
        assert($middleware instanceof ParameterMiddlewareInterface);

        return $middleware->mapParameter($parameter, $docBlock, $paramTagType, $parameterAnnotations, $this);
    }
}
