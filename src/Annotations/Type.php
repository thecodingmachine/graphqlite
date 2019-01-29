<?php


namespace TheCodingMachine\GraphQLite\Annotations;

use BadMethodCallException;
use function class_exists;
use TheCodingMachine\GraphQLite\Annotations\Exceptions\ClassNotFoundException;
use TheCodingMachine\GraphQLite\MissingAnnotationException;

/**
 * The Type annotation must be put in a GraphQL type class docblock and is used to map to the underlying PHP class
 * this is exposed via this type.
 *
 * @Annotation
 * @Target({"CLASS"})
 * @Attributes({
 *   @Attribute("class", type = "string"),
 * })
 */
class Type
{
    /**
     * @var string|null
     */
    private $class;

    /**
     * Is the class having the annotation a GraphQL type itself?
     *
     * @var bool
     */
    private $selfType = false;

    /**
     * @param mixed[] $attributes
     */
    public function __construct(array $attributes = [])
    {
        if (isset($attributes['class'])) {
            $this->setClass($attributes['class']);
            if (!class_exists($this->class)) {
                throw ClassNotFoundException::couldNotFindClass($this->class);
            }
        } else {
            $this->selfType = true;
        }
    }

    /**
     * Returns the fully qualified class name of the targeted class.
     *
     * @return string
     */
    public function getClass(): string
    {
        if ($this->class === null) {
            throw new \RuntimeException('Empty class for @Type annotation. You MUST create the Type annotation object using the GraphQLite AnnotationReader');
        }
        return $this->class;
    }

    public function setClass(string $class): void
    {
        $this->class = ltrim($class, '\\');
    }

    public function isSelfType(): bool
    {
        return $this->selfType;
    }
}
