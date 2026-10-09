<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\Integration\Models;

use BadMethodCallException;
use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Input;
use TheCodingMachine\GraphQLite\Undefined;

use function lcfirst;
use function str_starts_with;
use function substr;

/**
 * Without a constructor, hydrated through a magic `__call` setter onto non-public properties, each typed
 * `T|Undefined` (refuses null) and `T|null|Undefined`, with and without a PHP default.
 */
#[Input]
class OmittableMagicSetterInput
{
    #[Field]
    private int|Undefined $property = Undefined::VALUE;

    #[Field]
    protected int|null|Undefined $nullableProperty = Undefined::VALUE;

    #[Field]
    protected int|Undefined $propertyWithoutDefault;

    #[Field]
    private int|null|Undefined $nullablePropertyWithoutDefault;

    /** @param array<int, mixed> $arguments */
    public function __call(string $method, array $arguments): void
    {
        if (! str_starts_with($method, 'set')) {
            throw new BadMethodCallException('Call to undefined method ' . self::class . '::' . $method . '()');
        }

        $this->{lcfirst(substr($method, 3))} = $arguments[0];
    }

    public function getProperty(): int|Undefined
    {
        return $this->property;
    }

    public function getNullableProperty(): int|null|Undefined
    {
        return $this->nullableProperty;
    }

    public function getPropertyWithoutDefault(): int|Undefined
    {
        return $this->propertyWithoutDefault;
    }

    public function getNullablePropertyWithoutDefault(): int|null|Undefined
    {
        return $this->nullablePropertyWithoutDefault;
    }
}
