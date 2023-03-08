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
class MagicField implements SourceFieldInterface
{
    /** @var string */
    private $name;

    /** @var string|null */
    private $outputType;

    /** @var string|null */
    private $phpType;

    /** @var string|null */
    private $description;

    /** @var string|null */
    private $sourceName;

    /** @var MiddlewareAnnotations */
    private $middlewareAnnotations;

    /** @var array<string, ParameterAnnotations> */
    private $parameterAnnotations;

    /**
     * @param mixed[] $attributes
     * @param array<MiddlewareAnnotationInterface|ParameterAnnotationInterface> $annotations
     */
    public function __construct(array $attributes = [], string|null $name = null, string|null $outputType = null, string|null $phpType = null, string|null $description = null, string|null $sourceName = null, array $annotations = [])
    {
        $this->name = $attributes['name'] ?? $name;
        $this->outputType = $attributes['outputType'] ?? $outputType ?? null;
        $this->phpType = $attributes['phpType'] ?? $phpType ?? null;
        $this->description = $attributes['description'] ?? $description ?? null;
        $this->sourceName = $attributes['sourceName'] ?? $sourceName ?? null;

        if (! $this->name || (! $this->outputType && ! $this->phpType)) {
            throw new BadMethodCallException('The @MagicField annotation must be passed a name and an output type or a php type. For instance: "@MagicField(name=\'phone\', outputType=\'String!\')" or "@MagicField(name=\'phone\', phpType=\'string\')"');
        }
        if (isset($this->outputType) && $this->phpType) {
            throw new BadMethodCallException('In a @MagicField annotation, you cannot use the outputType and the phpType at the same time. For instance: "@MagicField(name=\'phone\', outputType=\'String!\')" or "@MagicField(name=\'phone\', phpType=\'string\')"');
        }
        $middlewareAnnotations = [];
        $parameterAnnotations = [];
        $annotations = $annotations ?: $attributes['annotations'] ?? [];
        if (! is_array($annotations)) {
            $annotations = [$annotations];
        }
        foreach ($annotations as $annotation) {
            if ($annotation instanceof MiddlewareAnnotationInterface) {
                $middlewareAnnotations[] = $annotation;
            } elseif ($annotation instanceof ParameterAnnotationInterface) {
                $parameterAnnotations[$annotation->getTarget()][] = $annotation;
            } else {
                throw new BadMethodCallException('The @MagicField annotation\'s "annotations" attribute must be passed an array of annotations implementing either MiddlewareAnnotationInterface or ParameterAnnotationInterface."');
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
     * Returns the GraphQL return type of the property (as a string).
     * The string is the GraphQL output type name.
     */
    public function getOutputType(): string|null
    {
        return $this->outputType;
    }

    /**
     * Returns the PHP return type of the property (as a string).
     * The string is the PHPDoc for the PHP type.
     */
    public function getPhpType(): string|null
    {
        return $this->phpType;
    }

    /**
     * Returns the description of the GraphQL query/mutation/field.
     */
    public function getDescription(): string|null
    {
        return $this->description;
    }

    /**
     * Returns the property name in the source class
     */
    public function getSourceName(): string|null
    {
        return $this->sourceName;
    }

    public function getMiddlewareAnnotations(): MiddlewareAnnotations
    {
        return $this->middlewareAnnotations;
    }

    /** @return array<string, ParameterAnnotations> Key: the name of the attribute */
    public function getParameterAnnotations(): array
    {
        return $this->parameterAnnotations;
    }

    public function shouldFetchFromMagicProperty(): bool
    {
        return true;
    }
}
