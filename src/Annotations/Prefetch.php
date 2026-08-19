<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Annotations;

use Attribute;
use TheCodingMachine\GraphQLite\GraphQLRuntimeException;

#[Attribute(Attribute::TARGET_PARAMETER)]
class Prefetch implements ParameterAnnotationInterface
{
    /**
     * @param string|(callable&array{class-string, string})|object $callable The prefetch method: a bare method name on
     *                                                                      the declaring class, an array callable, an
     *                                                                      invokable object, or — from PHP 8.5 —
     *                                                                      first-class callable syntax. A non-static
     *                                                                      method named by the array form is resolved
     *                                                                      through the container; first-class callable
     *                                                                      syntax cannot express that form.
     */
    public function __construct(public readonly string|array|object $callable)
    {
    }

    public function getTarget(): string
    {
        // This is only needed for using #[Prefetch] as a Doctrine attribute, which it doesn't support.
        throw new GraphQLRuntimeException();
    }
}
