<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Annotations;

use Doctrine\Common\Annotations\Annotation\Attribute;

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
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->prefetchMethod = $attributes['prefetchMethod'] ?? null;
        $this->description = $attributes['description'] ?? null;
        $this->inputType = $attributes['inputType'] ?? null;

        if (empty($attributes['for'])) {
            return;
        }

        $this->for = (array) $attributes['for'];
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
