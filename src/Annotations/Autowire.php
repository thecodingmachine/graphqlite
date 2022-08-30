<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Annotations;

use Attribute;
use BadMethodCallException;

use function is_string;
use function ltrim;

/**
 * Use this annotation to autowire a service from the container into a given parameter of a field/query/mutation.
 *
 * @Annotation
 * @Target({"METHOD", "ANNOTATION"})
 * @Attributes({
 *   @Attribute("for", type = "string"),
 *   @Attribute("identifier", type = "string")
 * })
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
class Autowire implements ParameterAnnotationInterface
{
    /** @var string|null */
    private $for;
    /** @var string|null */
    private $identifier;

    /**
     * @param array<string, mixed>|string $identifier
     */
    public function __construct(array|string $identifier = [])
    {
        $values = $identifier;
        if (is_string($values)) {
            $this->identifier = $values;
        } else {
            $this->identifier = $values['identifier'] ?? $values['value'] ?? null;
            if (isset($values['for'])) {
                $this->for = ltrim($values['for'], '$');
            }
        }
    }

    public function getTarget(): string
    {
        if ($this->for === null) {
            throw new BadMethodCallException('The @Autowire annotation must be passed a target. For instance: "@Autowire(for="$myService")"');
        }
        return $this->for;
    }

    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }
}
