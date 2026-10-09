<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\Integration\Models;

use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Input;
use TheCodingMachine\GraphQLite\Undefined;

/**
 * Without a constructor, hydrated through its public properties, each typed `T|Undefined` (refuses null) and
 * `T|null|Undefined`, with and without a PHP default.
 */
#[Input]
class OmittablePropertiesInput
{
    #[Field]
    public int|Undefined $property = Undefined::VALUE;

    #[Field]
    public int|null|Undefined $nullableProperty = Undefined::VALUE;

    #[Field]
    public int|Undefined $propertyWithoutDefault;

    #[Field]
    public int|null|Undefined $nullablePropertyWithoutDefault;
}
