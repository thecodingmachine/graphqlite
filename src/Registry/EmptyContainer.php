<?php


namespace TheCodingMachine\GraphQL\Controllers\Registry;

use Psr\Container\ContainerInterface;

/**
 * An always empty container (to use as a stub for the Registry).
 */
class EmptyContainer implements ContainerInterface
{
    public function get($id)
    {
        throw NotFoundException::notFound($id);
    }

    public function has($id)
    {
        return false;
    }
}
