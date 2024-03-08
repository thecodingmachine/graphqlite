<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Parameters;

interface ExpandsInputTypeParameters
{
    /** @return array<string, InputTypeParameterInterface> */
    public function toInputTypeParameters(): array;
}
