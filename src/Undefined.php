<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite;

/**
 * Represents a special marker type used to distinguish between an explicitly
 * provided `null` value and an absent (missing) field in the input payload.
 */
enum Undefined
{
    case VALUE;
}
