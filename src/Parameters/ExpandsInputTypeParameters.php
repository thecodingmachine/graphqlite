<?php

namespace TheCodingMachine\GraphQLite\Parameters;

interface ExpandsInputTypeParameters
{
    /**
     * @return array<string, InputTypeParameterInterface>
     */
    public function toInputTypeParameters(): array;
}