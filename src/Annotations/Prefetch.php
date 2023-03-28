<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Annotations;

use Attribute;
use TheCodingMachine\GraphQLite\GraphQLRuntimeException;

#[Attribute(Attribute::TARGET_PARAMETER)]
class Prefetch implements ParameterAnnotationInterface
{
    /** @param string|(callable&array{class-string, string}) $callable */
    public function __construct(public readonly string|array $callable)
    {
    }

    public function getTarget(): string
    {
        // This is only needed for using #[Prefetch] as a Doctrine attribute, which it doesn't support.
        throw new GraphQLRuntimeException();
    }
}
