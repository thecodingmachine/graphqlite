<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Directives;

use TheCodingMachine\GraphQLite\Annotations\MiddlewareAnnotationInterface;

/**
 * Marker contract for a custom directive that decorates a field, query, mutation or subscription
 * (GraphQL `FIELD_DEFINITION` location).
 *
 * Implementing this interface alone registers the directive with the schema and renders it on any
 * field it's applied to, but adds no PHP behavior — pure metadata. To wrap the resolver or
 * otherwise act on the field, implement {@see BehavioralFieldDirective} instead.
 *
 * Because the interface extends {@see MiddlewareAnnotationInterface}, instances are collected by
 * the existing {@see \TheCodingMachine\GraphQLite\AnnotationReader::getMiddlewareAnnotations()}.
 */
interface FieldDirective extends TypeSystemDirective, MiddlewareAnnotationInterface
{
}
