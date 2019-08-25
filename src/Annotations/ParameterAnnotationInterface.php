<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Annotations;

/**
 * Annotations implementing this interface are targeting a single parameter.
 */
interface ParameterAnnotationInterface
{
    /**
     * Returns the name of the targeted parameter (without the leading "$")
     */
    public function getTarget(): string;
}
