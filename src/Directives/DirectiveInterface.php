<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Directives;

/**
 * Base marker for any GraphQLite custom directive. The one requirement is the static
 * {@see definition} method returning the directive's metadata.
 */
interface DirectiveInterface
{
    public static function definition(): DirectiveDefinition;
}
