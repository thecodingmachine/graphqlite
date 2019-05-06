<?php


namespace TheCodingMachine\GraphQLite\Parameters;


use function array_key_exists;
use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\ResolveInfo;
use TheCodingMachine\GraphQLite\GraphQLException;
use TheCodingMachine\GraphQLite\Types\ArgumentResolver;

/**
 * Typically the first parameter of "external" fields that will be filled with the Source object.
 */
class InputTypeParameter implements ParameterInterface
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var InputType
     */
    private $type;
    /**
     * @var bool
     */
    private $doesHaveDefaultValue;
    private $defaultValue;
    /**
     * @var ArgumentResolver
     */
    private $argumentResolver;

    /**
     * InputTypeParameter constructor.
     * @param string $name
     * @param InputType $type
     * @param bool $hasDefaultValue
     * @param mixed $defaultValue
     * @param ArgumentResolver $argumentResolver
     */
    public function __construct(string $name, InputType $type, bool $hasDefaultValue, $defaultValue, ArgumentResolver $argumentResolver)
    {
        $this->name = $name;
        $this->type = $type;
        $this->doesHaveDefaultValue = $hasDefaultValue;
        $this->defaultValue = $defaultValue;
        $this->argumentResolver = $argumentResolver;
    }

    /**
     * @param object $source
     * @param array<string, mixed> $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @return mixed
     */
    public function resolve($source, $args, $context, ResolveInfo $info)
    {
        if (isset($args[$this->name])) {
            return $this->argumentResolver->resolve($args[$this->name], $this->type);
        }

        if ($this->doesHaveDefaultValue) {
            return $this->defaultValue;
        }

        throw new GraphQLException("Expected argument '{$this->name}' was not provided.");
    }

    /**
     * @return InputType
     */
    public function getType(): InputType
    {
        return $this->type;
    }

    /**
     * @return bool
     */
    public function hasDefaultValue(): bool
    {
        return $this->doesHaveDefaultValue;
    }

    /**
     * @return mixed
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }
}
