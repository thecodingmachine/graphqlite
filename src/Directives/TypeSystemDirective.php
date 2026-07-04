<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Directives;

/**
 * Marker for directives that annotate the schema, rather than executable directives used in client
 * queries. The four family interfaces ({@see FieldDirective}, {@see InputFieldDirective},
 * {@see ObjectTypeDirective}, {@see InputObjectTypeDirective}) extend it so discovery can find them
 * with one interface check.
 */
interface TypeSystemDirective extends DirectiveInterface
{
}
