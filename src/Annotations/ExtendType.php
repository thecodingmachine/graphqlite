<?php


namespace TheCodingMachine\GraphQLite\Annotations;

use BadMethodCallException;
use function class_exists;
use TheCodingMachine\GraphQLite\Annotations\Exceptions\ClassNotFoundException;
use TheCodingMachine\GraphQLite\MissingAnnotationException;

/**
 * The ExtendType annotation must be put in a GraphQL type class docblock and is used to add additional fields to the underlying PHP class.
 *
 * @Annotation
 * @Target({"CLASS"})
 * @Attributes({
 *   @Attribute("class", type = "string"),
 * })
 */
class ExtendType
{
    /**
     * @var string
     */
    private $class;

    /**
     * @param mixed[] $attributes
     */
    public function __construct(array $attributes = [])
    {
        if (!isset($attributes['class'])) {
            throw new BadMethodCallException('In annotation @ExtendType, missing compulsory parameter "class".');
        }
        $this->class = $attributes['class'];
        if (!class_exists($this->class)) {
            throw ClassNotFoundException::couldNotFindClass($this->class);
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
        return ltrim($this->class, '\\');
    }
}
