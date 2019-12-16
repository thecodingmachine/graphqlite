<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Containers;

use Psr\Container\ContainerInterface;

/**
 * An always empty container (to use as a stub for the BasicAutoWiringContainer).
 */
class EmptyContainer implements ContainerInterface
{
    /**
     * @param string $id
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint
     */
    public function get($id): void
    {
        throw NotFoundException::notFound($id);
    }

    /**
     * @param string $id
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint
     */
    public function has($id): bool
    {
        return false;
    }
}
