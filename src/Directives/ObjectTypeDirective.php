<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Directives;

/**
 * Marker contract for a custom directive that decorates an object type (GraphQL `OBJECT`
 * location). Pure metadata — implement {@see BehavioralObjectTypeDirective} to mutate or wrap the
 * type at build time.
 */
interface ObjectTypeDirective extends TypeSystemDirective
{
}
