<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Annotations;

use Attribute;
use BadMethodCallException;

use function is_string;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Right implements MiddlewareAnnotationInterface
{
    private string $name;

    /**
     * @param array<string, mixed>|string $name
     *
     * @throws BadMethodCallException
     */
    public function __construct(array|string $name = [])
    {
        $data = $name;
        if (is_string($data)) {
            $data = ['name' => $data];
        }
        if (! isset($data['value']) && ! isset($data['name'])) {
            throw new BadMethodCallException('The #[Right] attribute must be passed a right name. For instance: "#[Right(\'my_right\')]"');
        }
        $this->name = $data['value'] ?? $data['name'];
    }

    public function getName(): string
    {
        return $this->name;
    }
}
