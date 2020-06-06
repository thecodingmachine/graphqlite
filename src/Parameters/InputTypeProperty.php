<?php

namespace TheCodingMachine\GraphQLite\Parameters;

use GraphQL\Type\Definition\InputType;
use TheCodingMachine\GraphQLite\Types\ArgumentResolver;

class InputTypeProperty extends InputTypeParameter
{

    /**
     * @var string
     */
    private $propertyName;

    /**
     * @param string           $propertyName
     * @param string           $fieldName
     * @param InputType        $type
     * @param bool             $hasDefaultValue
     * @param mixed            $defaultValue
     * @param ArgumentResolver $argumentResolver
     */
    public function __construct(string $propertyName, string $fieldName, InputType $type, bool $hasDefaultValue, $defaultValue, ArgumentResolver $argumentResolver) {
        parent::__construct($fieldName, $type, $hasDefaultValue, $defaultValue, $argumentResolver);
        $this->propertyName = $propertyName;
    }

    /**
     * @return string
     */
    public function getPropertyName(): string {
        return $this->propertyName;
    }
}
