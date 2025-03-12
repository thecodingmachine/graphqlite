<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Annotations;

use Attribute;

use function trigger_error;

use const E_USER_DEPRECATED;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Field extends AbstractRequest
{
    private string|null $prefetchMethod;

    /**
     * Input/Output type names for which this fields should be applied to.
     *
     * @var string[]|null
     */
    private array|null $for = null;

    private string|null $description;
    private string|null $inputType;

    /**
     * @param mixed[] $attributes
     * @param string|string[] $for
     */
    public function __construct(
        array $attributes = [],
        string|null $name = null,
        string|null $outputType = null,
        string|null $prefetchMethod = null,
        string|array|null $for = null,
        string|null $description = null,
        string|null $inputType = null,
    ) {
        parent::__construct($attributes, $name, $outputType);

        $this->prefetchMethod = $prefetchMethod ?? $attributes['prefetchMethod'] ?? null;
        $this->description = $description ?? $attributes['description'] ?? null;
        $this->inputType = $inputType ?? $attributes['inputType'] ?? null;

        $forValue = $for ?? $attributes['for'] ?? null;
        if (! $forValue) {
            return;
        }

        $this->for = (array) $forValue;

        if (! $this->prefetchMethod) {
            return;
        }

        trigger_error(
            "Using #[Field(prefetchMethod='" . $this->prefetchMethod . "')] on fields is deprecated in favor " .
            "of #[Prefetch('" . $this->prefetchMethod . "')] \$data attribute on the parameter itself.",
            E_USER_DEPRECATED,
        );
    }

    /**
     * Returns the prefetch method name (the method that will be called to fetch many records at once)
     */
    public function getPrefetchMethod(): string|null
    {
        return $this->prefetchMethod;
    }

    /** @return string[]|null */
    public function getFor(): array|null
    {
        return $this->for;
    }

    public function getDescription(): string|null
    {
        return $this->description;
    }

    public function getInputType(): string|null
    {
        return $this->inputType;
    }
}
