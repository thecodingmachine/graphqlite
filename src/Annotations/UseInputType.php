<?php

namespace TheCodingMachine\GraphQLite\Annotations;

/**
 * Use this annotation to force using a specific input type for an input argument.
 *
 * @Annotation
 * @Target({"METHOD"})
 * @Attributes({
 *   @Attribute("for", type = "string"),
 *   @Attribute("type", type = "string")
 * })
 */
class UseInputType
{
    /**
     * @var string
     */
    private $for;
    /**
     * @var string
     */
    private $type;

    /**
     * @param array<string, mixed> $values
     *
     * @throws BadMethodCallException
     */
    public function __construct(array $values)
    {
        if (!isset($values['for'], $values['type'])) {
            throw new BadMethodCallException('The @UseInputType annotation must be passed a target and an input type. For instance: "@UseInputType(for="$input", type="MyInputType")"');
        }
        $this->for = $values['for'];
        $this->type = $values['type'];
    }

    /**
     * @return string
     */
    public function getFor(): string
    {
        return $this->for;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }
}
