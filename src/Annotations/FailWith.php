<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Annotations;

use Attribute;
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
#[Attribute(Attribute::TARGET_METHOD)]
class FailWith implements MiddlewareAnnotationInterface
{
    /**
     * The default value to use if the right is not enforced.
     *
     * @var mixed
     */
    private $value;

    /**
     * @param array<string, mixed>|mixed $values
     * @param mixed $value
     *
     * @throws BadMethodCallException
     */
    public function __construct($values = [], $value = '__fail__with__magic__key__')
    {
        if ($value !== '__fail__with__magic__key__') {
            $this->value = $value;
        } elseif (array_key_exists('value', $values)) {
            $this->value = $values['value'];
        } else {
            throw new BadMethodCallException('The @FailWith annotation must be passed a defaultValue. For instance: "@FailWith(null)"');
        }
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
