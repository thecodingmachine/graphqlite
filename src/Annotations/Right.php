<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Annotations;

use Attribute;
use BadMethodCallException;

use function is_string;

/**
 * @Annotation
 * @Target({"PROPERTY", "ANNOTATION", "METHOD"})
 * @Attributes({
 *   @Attribute("name", type = "string"),
 * })
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Right implements MiddlewareAnnotationInterface
{
    /** @var string */
    private $name;

    /**
     * @param array<string, mixed>|string $name
     *
     * @throws BadMethodCallException
     */
    public function __construct($name = [])
    {
        $data = $name;
        if (is_string($data)) {
            $data = ['name' => $data];
        }
        if (! isset($data['value']) && ! isset($data['name'])) {
            throw new BadMethodCallException('The @Right annotation must be passed a right name. For instance: "@Right(\'my_right\')"');
        }
        $this->name = $data['value'] ?? $data['name'];
    }

    public function getName(): string
    {
        return $this->name;
    }
}
