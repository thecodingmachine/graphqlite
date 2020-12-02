<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Annotations;

use BadMethodCallException;
use function is_string;
use function ltrim;

/**
 * Use this annotation to force using a specific input type for an input argument.
 *
 * @Annotation
 * @Target({"METHOD", "ANNOTATION"})
 * @Attributes({
 *   @Attribute("for", type = "string"),
 *   @Attribute("inputType", type = "string"),
 * })
 */
class UseInputType implements ParameterAnnotationInterface
{
    /** @var string|null */
    private $for;
    /** @var string */
    private $inputType;

    /**
     * @param array<string, mixed>|string $inputType
     *
     * @throws BadMethodCallException
     */
    public function __construct($inputType = [])
    {
        $values = $inputType;
        if (is_string($values)) {
            $values = ['inputType' => $values];
        }
        if (! isset($values['inputType'])) {
            throw new BadMethodCallException('The @UseInputType annotation must be passed an input type. For instance: "@UseInputType(for="$input", inputType="MyInputType")" in PHP 7+ or #[UseInputType("MyInputType")] in PHP 8+');
        }
        $this->inputType = $values['inputType'];
        if (isset($values['for'])) {
            $this->for = ltrim($values['for'], '$');
        }
    }

    public function getTarget(): string
    {
        if ($this->for === null) {
            throw new BadMethodCallException('The @UseInputType annotation must be passed a target and an input type. For instance: "@UseInputType(for="$input", inputType="MyInputType")" in PHP 7+ or #[UseInputType("MyInputType")] in PHP 8+');
        }
        return $this->for;
    }

    public function getInputType(): string
    {
        return $this->inputType;
    }
}
