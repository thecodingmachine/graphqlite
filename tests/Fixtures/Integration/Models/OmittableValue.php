<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\Integration\Models;

use TheCodingMachine\GraphQLite\Annotations\Factory;
use TheCodingMachine\GraphQLite\Undefined;

/**
 * Built by a factory whose parameters are typed `T|Undefined` (refuses null) and `T|null|Undefined`.
 */
class OmittableValue
{
    public function __construct(
        public readonly int|Undefined $value,
        public readonly int|null|Undefined $nullableValue,
    )
    {
    }

    #[Factory]
    public static function create(int|Undefined $value, int|null|Undefined $nullableValue): self
    {
        return new self($value, $nullableValue);
    }
}
