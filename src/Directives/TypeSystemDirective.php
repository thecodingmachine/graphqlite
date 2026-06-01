<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Directives;

/**
 * Marker for directives that decorate the schema (as opposed to executable directives that decorate
 * client queries). All four family interfaces — {@see FieldDirective}, {@see InputFieldDirective},
 * {@see ObjectTypeDirective}, {@see InputObjectTypeDirective} — extend this marker so discovery can
 * find every type-system directive with a single interface check.
 */
interface TypeSystemDirective extends DirectiveInterface
{
}
