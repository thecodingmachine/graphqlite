<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Context;

use SplObjectStorage;
use TheCodingMachine\GraphQLite\Parameters\ParameterInterface;
use TheCodingMachine\GraphQLite\PrefetchBuffer;

/**
 * A context class that should be passed to the Webonyx executor.
 */
class Context implements ContextInterface, ResetableContextInterface
{
    private SplObjectStorage $prefetchBuffers;

    public function __construct()
    {
        $this->prefetchBuffers = new SplObjectStorage();
    }

    /**
     * Returns the prefetch buffer associated to the field $field.
     * (the buffer is created on the fly if it does not exist yet).
     */
    public function getPrefetchBuffer(ParameterInterface $field): PrefetchBuffer
    {
        if ($this->prefetchBuffers->offsetExists($field)) {
            $prefetchBuffer = $this->prefetchBuffers->offsetGet($field);
        } else {
            $prefetchBuffer = new PrefetchBuffer();
            $this->prefetchBuffers->offsetSet($field, $prefetchBuffer);
        }

        return $prefetchBuffer;
    }

    public function reset(): void
    {
        $this->prefetchBuffers = new SplObjectStorage();
    }
}
