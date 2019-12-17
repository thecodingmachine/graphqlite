<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Annotations;

use BadMethodCallException;
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
class Autowire implements ParameterAnnotationInterface
{
    /** @var string */
    private $for;
    /** @var string|null */
    private $identifier;

    /**
     * @param array<string, mixed> $values
     */
    public function __construct(array $values)
    {
        if (! isset($values['for'])) {
            throw new BadMethodCallException('The @Autowire annotation must be passed a target. For instance: "@Autowire(for="$myService")"');
        }
        $this->for = ltrim($values['for'], '$');
        $this->identifier = $values['identifier'] ?? $values['value'] ?? null;
    }

    public function getTarget(): string
    {
        return $this->for;
    }

    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }
}
