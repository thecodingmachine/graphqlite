<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Annotations;

use Attribute;
use BadMethodCallException;

use function ltrim;

/**
 * Use this annotation to tell GraphQLite to ignore a parameter and not expose it as an input parameter.
 * This is only possible if the parameter has a default value.
 *
 * @Annotation
 * @Target({"METHOD", "ANNOTATION"})
 * @Attributes({
 *   @Attribute("for", type = "string")
 * })
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
class HideParameter implements ParameterAnnotationInterface
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
            throw new BadMethodCallException('The @HideParameter annotation must be passed a target. For instance: "@HideParameter(for="$myParameterToHide")"');
        }
        return $this->for;
    }
}
