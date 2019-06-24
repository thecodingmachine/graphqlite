<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Annotations;

/**
 * Use this annotation to autowire a service from the container into a given parameter of a field/query/mutation.
 *
 * @Annotation
 * @Target({"ANNOTATION"})
 * @Attributes({
 *   @Attribute("identifier", type = "string")
 * })
 */
class Autowire implements ParameterAnnotationInterface
{
    /** @var string|null */
    private $identifier;

    /**
     * @param array<string, mixed> $values
     */
    public function __construct(array $values)
    {
        $this->identifier = $values['identifier'] ?? $values['value'] ?? null;
    }

    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }
}
