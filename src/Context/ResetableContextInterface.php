<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Context;

/**
 * Contexts implementing this interface can be reseted.
 */
interface ResetableContextInterface
{
    public function reset(): void;
}
