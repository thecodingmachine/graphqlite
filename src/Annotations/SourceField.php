<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Annotations;

use Attribute;
use BadMethodCallException;

use function array_map;
use function is_array;

/**
 * SourceFields are fields that are directly source from the base object into GraphQL.
 *
 * @Annotation
 * @Target({"CLASS"})
 * @Attributes({
 *   @Attribute("name", type = "string"),
 *   @Attribute("outputType", type = "string"),
 *   @Attribute("phpType", type = "string"),
 *   @Attribute("annotations", type = "mixed"),
 * })
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class SourceField implements SourceFieldInterface
{
    /** @var string */
    private $name;

    /** @var string|null */
    private $outputType;

    /** @var string|null */
    private $phpType;

    /** @var string|null */
    private $description;

    /** @var string */
    private $sourceName;

    /** @var MiddlewareAnnotations */
    private $middlewareAnnotations;

    /** @var array<string, ParameterAnnotations> */
    private $parameterAnnotations;

    /**
     * @param mixed[] $attributes
     */
    public function __construct(array $attributes = [], ?string $name = null, ?string $outputType = null, ?string $phpType = null, ?string $description = null, ?string $sourceName = null)
    {
        $name = $name ?? $attributes['name'] ?? null;
        if ($name === null) {
            throw new BadMethodCallException('The @SourceField annotation must be passed a name. For instance: "@SourceField(name=\'phone\')"');
        }
        $this->name = $name;

        $this->outputType = $outputType ?? $attributes['outputType'] ?? null;
        $this->phpType = $phpType ?? $attributes['phpType'] ?? null;
        $this->description = $description ?? $attributes['description'] ?? null;
        $this->sourceName = $sourceName ?? $attributes['sourceName'] ?? null;

        if ($this->outputType && $this->phpType) {
            throw new BadMethodCallException('In a @SourceField annotation, you cannot use the outputType and the phpType at the same time. For instance: "@SourceField(name=\'phone\', outputType=\'String!\')" or "@SourceField(name=\'phone\', phpType=\'string\')"');
        }
        $middlewareAnnotations = [];
        $parameterAnnotations = [];
        $annotations = $attributes['annotations'] ?? [];
        if (! is_array($annotations)) {
            $annotations = [$annotations];
        }
        foreach ($annotations ?? [] as $annotation) {
            if ($annotation instanceof MiddlewareAnnotationInterface) {
                $middlewareAnnotations[] = $annotation;
            } elseif ($annotation instanceof ParameterAnnotationInterface) {
                $parameterAnnotations[$annotation->getTarget()][] = $annotation;
            } else {
                throw new BadMethodCallException('The @SourceField annotation\'s "annotations" attribute must be passed an array of annotations implementing either MiddlewareAnnotationInterface or ParameterAnnotationInterface."');
            }
        }
        $this->middlewareAnnotations = new MiddlewareAnnotations($middlewareAnnotations);
        $this->parameterAnnotations = array_map(static function (array $parameterAnnotationsForAttribute): ParameterAnnotations {
            return new ParameterAnnotations($parameterAnnotationsForAttribute);
        }, $parameterAnnotations);
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

    public function getPhpType(): ?string
    {
        return $this->phpType;
    }

    /**
     * Returns the description of the GraphQL query/mutation/field.
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Returns the property name in the source class
     */
    public function getSourceName(): ?string
    {
        return $this->sourceName;
    }

    public function getMiddlewareAnnotations(): MiddlewareAnnotations
    {
        return $this->middlewareAnnotations;
    }

    /**
     * @return array<string, ParameterAnnotations> Key: the name of the attribute
     */
    public function getParameterAnnotations(): array
    {
        return $this->parameterAnnotations;
    }

    public function shouldFetchFromMagicProperty(): bool
    {
        return false;
    }
}
