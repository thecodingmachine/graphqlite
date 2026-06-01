<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Directives;

use TheCodingMachine\GraphQLite\Annotations\MiddlewareAnnotationInterface;

/**
 * Marker contract for a custom directive that decorates an input object field (GraphQL
 * `INPUT_FIELD_DEFINITION` location). Pure metadata — implement {@see BehavioralInputFieldDirective}
 * to act on the input field at build time.
 */
interface InputFieldDirective extends TypeSystemDirective, MiddlewareAnnotationInterface
{
}
