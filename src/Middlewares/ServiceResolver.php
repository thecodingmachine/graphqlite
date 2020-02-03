<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Middlewares;

use function get_class;
use function is_object;

/**
 * A class that represents a callable on an object.
 * The object can be modified after class invocation.
 *
 * @internal
 */
class ServiceResolver implements ResolverInterface
{
    /** @var callable&array{0:object, 1:string} */
    private $callable;

    /**
     * @param callable&array{0:object, 1:string} $callable
     */
    public function __construct(callable $callable)
    {
        $this->callable = $callable;
    }

    public function getObject(): object
    {
        return $this->callable[0];
    }

    /**
     * @param mixed $args
     * @return mixed
     */
    public function __invoke(...$args)
    {
        $callable = $this->callable;

        return $callable(...$args);
    }

    public function toString(): string
    {
        $class = get_class($this->getObject());

        return $class.'::'.$this->callable[1].'()';
    }
}
