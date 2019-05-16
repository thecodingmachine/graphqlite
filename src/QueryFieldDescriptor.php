<?php


namespace TheCodingMachine\GraphQLite;

use GraphQL\Type\Definition\OutputType;
use GraphQL\Type\Definition\Type;
use TheCodingMachine\GraphQLite\Parameters\ParameterInterface;

/**
 * A class that describes a field to be created.
 * To contains getters and setters to alter the field behaviour.
 * It is meant to be passed from middleware to middleware.
 */
class QueryFieldDescriptor
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var OutputType&Type
     */
    private $type;
    /**
     * @var array<string, ParameterInterface>
     */
    private $parameters = [];
    /**
     * @var array<string, ParameterInterface>
     */
    private $prefetchParameters = [];
    /**
     * @var string|null
     */
    private $prefetchMethodName;
    /**
     * @var callable|null
     */
    private $callable;
    /**
     * @var string|null
     */
    private $targetMethodOnSource;
    /**
     * Whether we should inject the source as the first parameter or not.
     * @var bool
     */
    private $injectSource;
    /**
     * @var string|null
     */
    private $comment;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return OutputType|Type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param OutputType|Type $type
     */
    public function setType($type): void
    {
        $this->type = $type;
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

    /**
     * @return array<string, ParameterInterface>
     */
    public function getPrefetchParameters(): array
    {
        return $this->prefetchParameters;
    }

    /**
     * @param array<string, ParameterInterface> $prefetchParameters
     */
    public function setPrefetchParameters(array $prefetchParameters): void
    {
        $this->prefetchParameters = $prefetchParameters;
    }

    /**
     * @return string|null
     */
    public function getPrefetchMethodName(): ?string
    {
        return $this->prefetchMethodName;
    }

    /**
     * @param string|null $prefetchMethodName
     */
    public function setPrefetchMethodName(?string $prefetchMethodName): void
    {
        $this->prefetchMethodName = $prefetchMethodName;
    }

    /**
     * @return callable|null
     */
    public function getCallable(): ?callable
    {
        return $this->callable;
    }

    /**
     * @param callable $callable
     */
    public function setCallable(callable $callable): void
    {
        $this->callable = $callable;
        $this->targetMethodOnSource = null;
    }

    /**
     * @return string|null
     */
    public function getTargetMethodOnSource(): ?string
    {
        return $this->targetMethodOnSource;
    }

    /**
     * @param string|null $targetMethodOnSource
     */
    public function setTargetMethodOnSource(?string $targetMethodOnSource): void
    {
        $this->callable = null;
        $this->targetMethodOnSource = $targetMethodOnSource;
    }

    /**
     * @return bool
     */
    public function isInjectSource(): bool
    {
        return $this->injectSource;
    }

    /**
     * @param bool $injectSource
     */
    public function setInjectSource(bool $injectSource): void
    {
        $this->injectSource = $injectSource;
    }

    /**
     * @return string|null
     */
    public function getComment(): ?string
    {
        return $this->comment;
    }

    /**
     * @param string|null $comment
     */
    public function setComment(?string $comment): void
    {
        $this->comment = $comment;
    }
}
