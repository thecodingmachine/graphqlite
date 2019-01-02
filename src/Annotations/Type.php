<?php


namespace TheCodingMachine\GraphQL\Controllers\Annotations;

use BadMethodCallException;
use function class_exists;
use TheCodingMachine\GraphQL\Controllers\Annotations\Exceptions\ClassNotFoundException;
use TheCodingMachine\GraphQL\Controllers\MissingAnnotationException;

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
     * @var string
     */
    private $className;

    /**
     * @param mixed[] $attributes
     */
    public function __construct(array $attributes = [])
    {
        if (!isset($attributes['class'])) {
            throw new BadMethodCallException('In annotation @Type, missing compulsory parameter "class".');
        }
        $this->className = $attributes['class'];
        if (!class_exists($this->className)) {
            throw ClassNotFoundException::couldNotFindClass($this->className);
        }
    }

    /**
     * Returns the name of the GraphQL query/mutation/field.
     * If not specified, the name of the method should be used instead.
     *
     * @return string
     */
    public function getClass(): string
    {
        return ltrim($this->className, '\\');
    }
}
