<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Annotations;

use Attribute;
use BadMethodCallException;

use function is_string;
use function ltrim;

/**
 * Use this attribute to autowire a service from the container into a given parameter of a field/query/mutation.
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
class Autowire implements ParameterAnnotationInterface
{
    private string|null $for = null;
    private string|null $identifier = null;

    /** @param array<string, mixed>|string $params */
    public function __construct(
        array|string $params = [],
        string|null $for = null,
        string|null $identifier = null,
    )
    {
        $values = $params;
        if (is_string($values)) {
            $this->identifier = $values;
        } else {
            $this->identifier = $identifier ?? $values['identifier'] ?? $values['value'] ?? null;
            if (isset($values['for']) || $for !== null) {
                $this->for = ltrim($for ?? $values['for'], '$');
            }
        }
    }

    public function getTarget(): string
    {
        if ($this->for === null) {
            throw new BadMethodCallException('The #[Autowire] attribute must be passed a target. For instance: "#[Autowire(for: "$myService")]"');
        }
        return $this->for;
    }

    public function getIdentifier(): string|null
    {
        return $this->identifier;
    }
}
