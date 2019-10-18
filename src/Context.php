<?php


namespace TheCodingMachine\GraphQLite;

use SplObjectStorage;

/**
 * A context class that should be passed to the Webonyx executor.
 */
class Context implements ContextInterface
{
    /**
     * @var SplObjectStorage<QueryField, PrefetchBuffer>
     */
    private $prefetchBuffers;

    public function __construct()
    {
        $this->prefetchBuffers = new SplObjectStorage();
    }

    /**
     * Returns the prefetch buffer associated to the field $field.
     * (the buffer is created on the fly if it does not exist yet).
     */
    public function getPrefetchBuffer(QueryField $field): PrefetchBuffer
    {
        if ($this->prefetchBuffers->offsetExists($this)) {
            $prefetchBuffer = $this->prefetchBuffers->offsetGet($this);
        } else {
            $prefetchBuffer = new PrefetchBuffer();
            $this->prefetchBuffers->offsetSet($this, $prefetchBuffer);
        }

        return $prefetchBuffer;
    }
}
