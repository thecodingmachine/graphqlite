<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Parameters;

use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\Type;
use TheCodingMachine\GraphQLite\Types\ArgumentResolver;

class InputTypeProperty extends InputTypeParameter
{
    public function __construct(
        private readonly string $propertyName,
        string $fieldName,
        InputType&Type $type,
        string $description,
        bool $hasDefaultValue,
        mixed $defaultValue,
        ArgumentResolver $argumentResolver
    )
    {
        parent::__construct(
            $fieldName,
            $type,
            $description,
            $hasDefaultValue,
            $defaultValue,
            $argumentResolver
        );
    }

    public function getPropertyName(): string
    {
        return $this->propertyName;
    }
}
