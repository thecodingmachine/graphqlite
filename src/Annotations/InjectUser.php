<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Annotations;

use BadMethodCallException;
use function ltrim;

/**
 * Use this annotation to tell GraphQLite to inject the current logged user as an input parameter.
 * If the parameter is not nullable, the user MUST be logged to access the resource.
 *
 * @Annotation
 * @Target({"METHOD", "ANNOTATION"})
 * @Attributes({
 *   @Attribute("for", type = "string")
 * })
 */
class InjectUser implements ParameterAnnotationInterface
{
    /** @var string */
    private $for;

    /**
     * @param array<string, mixed> $values
     */
    public function __construct(array $values)
    {
        if (! isset($values['for'])) {
            throw new BadMethodCallException('The @InjectUser annotation must be passed a target. For instance: "@InjectUser(for="$user")"');
        }
        $this->for = ltrim($values['for'], '$');
    }

    public function getTarget(): string
    {
        return $this->for;
    }
}
