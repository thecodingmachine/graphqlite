<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Directives;

/**
 * Marker for a directive on an object type (`OBJECT` location). This alone is metadata only;
 * implement {@see BehavioralObjectTypeDirective} to act on the type at build time.
 */
interface ObjectTypeDirective extends TypeSystemDirective
{
}
