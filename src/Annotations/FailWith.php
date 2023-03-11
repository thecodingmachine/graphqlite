<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Annotations;

use Attribute;
use BadMethodCallException;

use function array_key_exists;
use function is_array;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 * @Attributes({
 *   @Attribute("value", type = "mixed"),
 *   @Attribute("mode", type = "string")
 * })
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD)]
class FailWith implements MiddlewareAnnotationInterface
{
    /** The default value to use if the right is not enforced. */
    private $value;

    /** @throws BadMethodCallException */
    public function __construct(mixed $values = [], mixed $value = '__fail__with__magic__key__')
    {
        if ($value !== '__fail__with__magic__key__') {
            $this->value = $value;
        } elseif (is_array($values) && array_key_exists('value', $values)) {
            $this->value = $values['value'];
        } else {
            throw new BadMethodCallException('The @FailWith annotation must be passed a defaultValue. For instance: "@FailWith(null)"');
        }
    }

    /**
     * Returns the default value to use if the right is not enforced.
     */
    public function getValue(): mixed
    {
        return $this->value;
    }
}
