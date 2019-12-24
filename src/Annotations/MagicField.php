<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Annotations;

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
class MagicField implements SourceFieldInterface
{
    /** @var string */
    private $name;

    /** @var string|null */
    private $outputType;

    /** @var string|null */
    private $phpType;

    /** @var MiddlewareAnnotations */
    private $middlewareAnnotations;

    /** @var array<string, ParameterAnnotations> */
    private $parameterAnnotations;

    /**
     * @param mixed[] $attributes
     */
    public function __construct(array $attributes = [])
    {
        if (! isset($attributes['name']) || (! isset($attributes['outputType']) && ! isset($attributes['phpType']))) {
            throw new BadMethodCallException('The @MagicField annotation must be passed a name and an output type or a php type. For instance: "@MagicField(name=\'phone\', outputType=\'String!\')" or "@MagicField(name=\'phone\', phpType=\'string\')"');
        }
        if (isset($attributes['outputType']) && isset($attributes['phpType'])) {
            throw new BadMethodCallException('In a @MagicField annotation, you cannot use the outputType and the phpType at the same time. For instance: "@MagicField(name=\'phone\', outputType=\'String!\')" or "@MagicField(name=\'phone\', phpType=\'string\')"');
        }
        $this->name = $attributes['name'];
        $this->outputType = $attributes['outputType'] ?? null;
        $this->phpType = $attributes['phpType'] ?? null;
        $middlewareAnnotations = [];
        $parameterAnnotations = [];
        $annotations = $attributes['annotations'] ?? [];
        if (! is_array($annotations)) {
            $annotations = [ $annotations ];
        }
        foreach ($annotations ?? [] as $annotation) {
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
    public function getOutputType(): ?string
    {
        return $this->outputType;
    }

    /**
     * Returns the PHP return type of the property (as a string).
     * The string is the PHPDoc for the PHP type.
     */
    public function getPhpType(): ?string
    {
        return $this->phpType;
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
        return true;
    }
}
