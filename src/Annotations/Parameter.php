<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Annotations;

use BadMethodCallException;
use function array_filter;
use function array_pop;
use function count;
use function ltrim;

/**
 * Use this annotation to force using a specific input type for an input argument.
 *
 * @Annotation
 * @Target({"METHOD"})
 * @Attributes({
 *   @Attribute("for", type = "string"),
 *   @Attribute("inputType", type = "string"),
 *   @Attribute("annotations", type = "array<TheCodingMachine\GraphQLite\Annotations\ParameterAnnotationInterface>")
 * })
 */
class Parameter
{
    /** @var string */
    private $for;
    /** @var string|null */
    private $inputType;
    /** @var array<ParameterAnnotationInterface> */
    private $annotations;

    /**
     * @param array<string, mixed> $values
     *
     * @throws BadMethodCallException
     */
    public function __construct(array $values)
    {
        if (! isset($values['for'])) {
            throw new BadMethodCallException('The @Parameter annotation must be passed a target. For instance: "@Parameter(for="$input", inputType="MyInputType")"');
        }
        $this->for       = ltrim($values['for'], '$');
        $this->inputType = $values['inputType'] ?? null;
        $this->annotations = $values['annotations'] ?? [];
    }

    public function getFor(): string
    {
        return $this->for;
    }

    public function getInputType(): ?string
    {
        return $this->inputType;
    }

    /**
     * @return array<ParameterAnnotationInterface>
     */
    public function getAllAnnotations(): array
    {
        return $this->annotations;
    }

    /**
     * Return annotations of the $className type
     *
     * @return array<int, ParameterAnnotationInterface>
     */
    public function getAnnotationsByType(string $className): array
    {
        return array_filter($this->annotations, static function (ParameterAnnotationInterface $annotation) use ($className) {
            return $annotation instanceof $className;
        });
    }

    /**
     * Returns at most 1 annotation of the $className type.
     */
    public function getAnnotationByType(string $className): ?ParameterAnnotationInterface
    {
        $annotations = $this->getAnnotationsByType($className);
        $count = count($annotations);
        if ($count > 1) {
            throw TooManyAnnotationsException::forClass($className);
        }

        if ($count === 0) {
            return null;
        }

        return array_pop($annotations);
    }
}
