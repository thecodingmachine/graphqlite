<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Annotations;

use Attribute;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD"})
 * @Attributes({
 *   @Attribute("name", type = "string"),
 *   @Attribute("outputType", type = "string"),
 *   @Attribute("prefetchMethod", type = "string"),
 *   @Attribute("for", type = "string[]"),
 *   @Attribute("description", type = "string"),
 *   @Attribute("inputType", type = "string"),
 * })
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Field extends AbstractRequest
{
    /** @var string|null */
    private $prefetchMethod;

    /**
     * Input/Output type names for which this fields should be applied to.
     *
     * @var string[]|null
     */
    private $for = null;

    /** @var string|null */
    private $description;

    /** @var string|null */
    private $inputType;

    /**
     * @param mixed[] $attributes
     */
    public function __construct(array $attributes = [], ?string $name = null, ?string $outputType = null, ?string $prefetchMethod = null, $for = null, ?string $description = null, ?string $inputType = null)
    {
        parent::__construct($attributes, $name, $outputType);
        $this->prefetchMethod = $prefetchMethod ?? $attributes['prefetchMethod'] ?? null;
        $this->description = $description ?? $attributes['description'] ?? null;
        $this->inputType = $inputType ?? $attributes['inputType'] ?? null;

        $forValue = $for ?? $attributes['for'] ?? null;
        if ($forValue) {
            $this->for = (array) $for;
        }
    }

    /**
     * Returns the prefetch method name (the method that will be called to fetch many records at once)
     */
    public function getPrefetchMethod(): ?string
    {
        return $this->prefetchMethod;
    }

    /**
     * @return string[]|null
     */
    public function getFor(): ?array
    {
        return $this->for;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getInputType(): ?string
    {
        return $this->inputType;
    }
}
