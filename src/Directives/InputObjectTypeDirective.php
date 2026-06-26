<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Directives;

/**
 * Marker for a directive on an input object type (`INPUT_OBJECT` location). Metadata only;
 * implement {@see BehavioralInputObjectTypeDirective} to change the built input type (e.g. its
 * `isOneOf` flag).
 */
interface InputObjectTypeDirective extends TypeSystemDirective
{
}
