<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Annotations;

use Attribute;
use BadMethodCallException;

use function is_string;
use function ltrim;

/**
 * Use this attribute to force using a specific input type for an input argument.
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
class UseInputType implements ParameterAnnotationInterface
{
    private string|null $for = null;
    private string $inputType;

    /**
     * @param array<string, mixed>|string $inputType
     *
     * @throws BadMethodCallException
     */
    public function __construct(array|string $inputType = [], string|null $for = null)
    {
        $values = $inputType;
        if (is_string($values)) {
            $values = ['inputType' => $values];
        }
        if (is_string($for) && $for !== '') {
            $values['for'] = $for;
        }
        if (! isset($values['inputType'])) {
            throw new BadMethodCallException('The #[UseInputType] attribute must be passed an input type. For instance: #[UseInputType("MyInputType")]');
        }
        $this->inputType = $values['inputType'];
        if (! isset($values['for'])) {
            return;
        }

        $this->for = ltrim($values['for'], '$');
    }

    public function getTarget(): string
    {
        if ($this->for === null) {
            throw new BadMethodCallException('The #[UseInputType] attribute must be passed a target and an input type. For instance: #[UseInputType("MyInputType")]');
        }
        return $this->for;
    }

    public function getInputType(): string
    {
        return $this->inputType;
    }
}
