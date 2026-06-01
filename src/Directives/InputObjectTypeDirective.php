<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Directives;

/**
 * Marker contract for a custom directive that decorates an input object type (GraphQL
 * `INPUT_OBJECT` location). Pure metadata — implement {@see BehavioralInputObjectTypeDirective} to
 * mutate the built input type (e.g. flip its `isOneOf` flag).
 */
interface InputObjectTypeDirective extends TypeSystemDirective
{
}
