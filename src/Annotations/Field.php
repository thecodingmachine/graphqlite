<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Annotations;

/**
 * @Annotation
 * @Target({"METHOD"})
 * @Attributes({
 *   @Attribute("name", type = "string"),
 *   @Attribute("outputType", type = "string"),
 *   @Attribute("prefetchMethod", type = "string"),
 * })
 */
class Field extends AbstractRequest
{
    /** @var string|null */
    private $prefetchMethod;

    /**
     * @param mixed[] $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->prefetchMethod = $attributes['prefetchMethod'] ?? null;
    }

    /**
     * Returns the prefetch method name (the method that will be called to fetch many records at once)
     */
    public function getPrefetchMethod(): ?string
    {
        return $this->prefetchMethod;
    }
}
