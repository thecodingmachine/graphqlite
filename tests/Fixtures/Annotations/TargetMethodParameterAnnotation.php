<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\Annotations;

use TheCodingMachine\GraphQLite\Annotations\ParameterAnnotationInterface;

#[\Attribute(\Attribute::TARGET_METHOD)]
final class TargetMethodParameterAnnotation implements ParameterAnnotationInterface
{
    public function __construct(private readonly string $target)
    {
    }

    public function getTarget(): string
    {
        return $this->target;
    }
}
