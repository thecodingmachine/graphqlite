<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Annotations;

use BadMethodCallException;
use function ltrim;

/**
 * Use this annotation to force using a specific input type for an input argument.
 *
 * @Annotation
 * @Target({"METHOD"})
 * @Attributes({
 *   @Attribute("for", type = "string"),
 *   @Attribute("inputType", type = "string")
 * })
 */
class Parameter
{
    /** @var string */
    private $for;
    /** @var string */
    private $inputType;

    /**
     * @param array<string, mixed> $values
     *
     * @throws BadMethodCallException
     */
    public function __construct(array $values)
    {
        if (! isset($values['for'], $values['inputType'])) {
            throw new BadMethodCallException('The @Parameter annotation must be passed a target and an input type. For instance: "@Parameter(for="$input", inputType="MyInputType")"');
        }
        $this->for       = ltrim($values['for'], '$');
        $this->inputType = $values['inputType'];
    }

    public function getFor(): string
    {
        return $this->for;
    }

    public function getInputType(): string
    {
        return $this->inputType;
    }
}
