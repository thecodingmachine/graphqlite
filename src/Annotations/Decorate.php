<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Annotations;

use Attribute;
use BadMethodCallException;

use function is_string;

/**
 * Methods with this attribute are decorating an input type when the input type is resolved.
 * This is meant to be used only when the input type is provided by a third-party library and you want to modify it.
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Decorate
{
    private string $inputTypeName;

    /**
     * @param array<string, mixed>|string $inputTypeName
     *
     * @throws BadMethodCallException
     */
    public function __construct(array|string $inputTypeName = [])
    {
        $values = $inputTypeName;
        if (is_string($values)) {
            $this->inputTypeName = $values;
        } elseif (! isset($values['value']) && ! isset($values['inputTypeName'])) {
            throw new BadMethodCallException('The #[Decorate] attribute must be passed an input type. For instance: "#[Decorate("MyInputType")]"');
        } else {
            $this->inputTypeName = $values['value'] ?? $values['inputTypeName'];
        }
    }

    public function getInputTypeName(): string
    {
        return $this->inputTypeName;
    }
}
