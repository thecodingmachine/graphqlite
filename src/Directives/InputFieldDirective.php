<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Directives;

use TheCodingMachine\GraphQLite\Annotations\MiddlewareAnnotationInterface;

/**
 * Marker for a directive on an input object field (`INPUT_FIELD_DEFINITION` location). Metadata
 * only; implement {@see BehavioralInputFieldDirective} to act on the input field at build time.
 */
interface InputFieldDirective extends TypeSystemDirective, MiddlewareAnnotationInterface
{
}
