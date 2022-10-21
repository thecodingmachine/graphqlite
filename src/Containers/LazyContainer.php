<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Containers;

use Closure;
use Psr\Container\ContainerInterface;

/**
 * This class is a minimalist dependency injection container.
 * It has compatibility with container-interop's ContainerInterface and delegate-lookup feature.
 */
class LazyContainer implements ContainerInterface
{
    /**
     * The array of entries once they have been instantiated.
     *
     * @var array<string, mixed>
     */
    protected array $objects;
    private ContainerInterface $delegateLookupContainer;

    /**
     * Instantiate the container.
     *
     * @param array<string, Closure> $entries The array of closures defining each entry of the container. Entries must be passed as an array of anonymous functions.
     * @param ContainerInterface|null $delegateLookupContainer Optional delegate lookup container.
     */
    public function __construct(private array $entries, ContainerInterface|null $delegateLookupContainer = null)
    {
        $this->delegateLookupContainer = $delegateLookupContainer ?: $this;
    }

    public function get(string $id): mixed
    {
        if (isset($this->objects[$id])) {
            return $this->objects[$id];
        }
        if (! isset($this->entries[$id])) {
            throw  throw NotFoundException::notFoundInContainer($id);
        }

        return $this->objects[$id] = $this->entries[$id]($this->delegateLookupContainer);
    }

    public function has(string $id): bool
    {
        return isset($this->entries[$id]) || isset($this->objects[$id]);
    }
}
