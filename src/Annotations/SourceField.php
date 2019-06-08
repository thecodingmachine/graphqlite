<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Annotations;

use function array_key_exists;
use BadMethodCallException;

/**
 * SourceFields are fields that are directly source from the base object into GraphQL.
 *
 * @Annotation
 * @Target({"CLASS"})
 * @Attributes({
 *   @Attribute("name", type = "string"),
 *   @Attribute("logged", type = "bool"),
 *   @Attribute("right", type = "TheCodingMachine\GraphQLite\Annotations\Right"),
 *   @Attribute("outputType", type = "string"),
 *   @Attribute("isId", type = "bool"),
 *   @Attribute("failWith", type = "mixed"),
 *   @Attribute("annotations", type = "TheCodingMachine\GraphQLite\Annotations\MiddlewareAnnotationInterface[]"),
 * })
 *
 * FIXME: remove idId since outputType="ID" is equivalent
 */
class SourceField implements SourceFieldInterface
{
    /** @var string */
    private $name;

    /** @var string|null */
    private $outputType;

    /** @var MiddlewareAnnotations */
    private $annotations;

    /**
     * @param mixed[] $attributes
     */
    public function __construct(array $attributes = [])
    {
        if (! isset($attributes['name'])) {
            throw new BadMethodCallException('The @SourceField annotation must be passed a name. For instance: "@SourceField(name=\'phone\')"');
        }
        $this->name       = $attributes['name'];
        $this->outputType = $attributes['outputType'] ?? null;
        $this->annotations = new MiddlewareAnnotations($attributes['annotations'] ?? []);
        if (! array_key_exists('failWith', $attributes)) {
            return;
        }
    }

    /**
     * Returns the name of the GraphQL query/mutation/field.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Returns the GraphQL return type of the request (as a string).
     * The string is the GraphQL output type name.
     */
    public function getOutputType(): ?string
    {
        return $this->outputType;
    }

    public function getAnnotations(): MiddlewareAnnotations
    {
        return $this->annotations;
    }
}
