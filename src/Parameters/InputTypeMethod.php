<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Parameters;

use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\Type;
use TheCodingMachine\GraphQLite\Types\ArgumentResolver;

class InputTypeMethod extends InputTypeParameter
{
    /** @var string */
    private string $methodName;

    /** @var array<string, ParameterInterface> */
    private array $parameters = [];

    /**
     * @param InputType&Type $type
     * @param mixed $defaultValue
     */
    public function __construct(string $methodName, string $fieldName, InputType $type, bool $hasDefaultValue, $defaultValue, ?ArgumentResolver $argumentResolver = null)
    {
        if(!$argumentResolver) $argumentResolver = new ArgumentResolver();
        parent::__construct($fieldName, $type, $hasDefaultValue, $defaultValue, $argumentResolver);
        $this->methodName = $methodName;
    }

    public function getMethodName(): string
    {
        return $this->methodName;
    }

    public function setMethodName($methodName): void
    {
        $this->methodName = $methodName;
    }

    /**
     * @return array<string, ParameterInterface>
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @param array<string, ParameterInterface> $parameters
     */
    public function setParameters(array $parameters): void
    {
        $this->parameters = $parameters;
    }
}
