<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Annotations;

use Attribute;
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
#[Attribute(Attribute::TARGET_PARAMETER)]
class InjectUser implements ParameterAnnotationInterface
{
    /** @var string */
    private $for;

    /** @param array<string, mixed> $values */
    public function __construct(array $values = [])
    {
        if (! isset($values['for'])) {
            return;
        }

        $this->for = ltrim($values['for'], '$');
    }

    public function getTarget(): string
    {
        if ($this->for === null) {
            throw new BadMethodCallException('The @InjectUser annotation must be passed a target. For instance: "@InjectUser(for="$user")"');
        }
        return $this->for;
    }
}
