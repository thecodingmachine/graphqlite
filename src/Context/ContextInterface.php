<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Context;

use TheCodingMachine\GraphQLite\Parameters\ParameterInterface;
use TheCodingMachine\GraphQLite\PrefetchBuffer;

/**
 * A context class that should be passed to the Webonyx executor.
 *
 * @unstable See https://graphqlite.thecodingmachine.io/docs/semver.html
 */
interface ContextInterface
{
    /**
     * Returns the prefetch buffer associated to the field $field.
     * (the buffer is created on the fly if it does not exist yet).
     */
    public function getPrefetchBuffer(ParameterInterface $field): PrefetchBuffer;
}
