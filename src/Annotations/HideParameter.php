<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Annotations;

use Attribute;
use BadMethodCallException;

use function ltrim;

/**
 * Use this attribute to tell GraphQLite to ignore a parameter and not expose it as an input parameter.
 * This is only possible if the parameter has a default value.
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
class HideParameter implements ParameterAnnotationInterface
{
    private string|null $for = null;

    /** @param array<string, mixed> $values */
    public function __construct(array $values = [], string|null $for = null)
    {
        if (! isset($values['for']) && $for === null) {
            return;
        }

        $this->for = ltrim($for ?? $values['for'], '$');
    }

    public function getTarget(): string
    {
        if ($this->for === null) {
            throw new BadMethodCallException('The #[HideParameter] attribute must be passed a target. For instance: "#[HideParameter(for: "$myParameterToHide")]"');
        }
        return $this->for;
    }
}
