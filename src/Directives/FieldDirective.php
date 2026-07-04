<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Directives;

use TheCodingMachine\GraphQLite\Annotations\MiddlewareAnnotationInterface;

/**
 * Marker for a directive on a field, query, mutation or subscription (`FIELD_DEFINITION` location).
 *
 * This alone registers the directive and prints it on the field, but adds no behavior. Implement
 * {@see BehavioralFieldDirective} to wrap the resolver or otherwise act on the field.
 *
 * It extends {@see MiddlewareAnnotationInterface}, so instances are picked up by
 * {@see \TheCodingMachine\GraphQLite\AnnotationReader::getMiddlewareAnnotations()}.
 */
interface FieldDirective extends TypeSystemDirective, MiddlewareAnnotationInterface
{
}
