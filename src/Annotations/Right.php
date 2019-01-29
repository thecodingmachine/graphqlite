<?php
declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Annotations;

/**
 * @Annotation
 * @Target({"ANNOTATION", "METHOD"})
 * @Attributes({
 *   @Attribute("name", type = "string"),
 * })
 */
class Right
{
    /**
     * @var string
     */
    private $name;

    /**
     * @param array<string, mixed> $values
     *
     * @throws \BadMethodCallException
     */
    public function __construct(array $values)
    {
        if (!isset($values['value']) && !isset($values['name'])) {
            throw new \BadMethodCallException('The @Right annotation must be passed a right name. For instance: "@Right(\'my_right\')"');
        }
        $this->name = $values['value'] ?? $values['name'];
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}
