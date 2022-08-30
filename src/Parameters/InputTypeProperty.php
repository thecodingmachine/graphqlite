<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Parameters;

use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\Type;
use TheCodingMachine\GraphQLite\Types\ArgumentResolver;

class InputTypeProperty extends InputTypeParameter
{
    /** @var string */
    private $propertyName;

    /**
     * @param InputType&Type $type
     */
    public function __construct(string $propertyName, string $fieldName, InputType $type, bool $hasDefaultValue, mixed $defaultValue, ArgumentResolver $argumentResolver)
    {
        parent::__construct($fieldName, $type, $hasDefaultValue, $defaultValue, $argumentResolver);
        $this->propertyName = $propertyName;
    }

    public function getPropertyName(): string
    {
        return $this->propertyName;
    }
}
