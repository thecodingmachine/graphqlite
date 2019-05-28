<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Annotations;

use BadMethodCallException;
use function array_key_exists;

/**
 * @Annotation
 * @Target({"METHOD", "ANNOTATION"})
 * @Attributes({
 *   @Attribute("value", type = "mixed"),
 *   @Attribute("mode", type = "string")
 * })
 */
class FailWith implements MiddlewareAnnotationInterface
{
    /**
     * The default value to use if the right is not enforced.
     *
     * @var mixed
     */
    private $value;

    /**
     * @param array<string, mixed> $values
     *
     * @throws BadMethodCallException
     */
    public function __construct(array $values)
    {
        if (! array_key_exists('value', $values)) {
            throw new BadMethodCallException('The @FailWith annotation must be passed a defaultValue. For instance: "@FailWith(null)"');
        }
        $this->value = $values['value'];
    }

    /**
     * Returns the default value to use if the right is not enforced.
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
}
