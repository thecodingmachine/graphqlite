<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Directives;

/**
 * Root marker for every GraphQLite custom directive — both type-system directives (this branch)
 * and executable directives (future). The single contract is the static {@see definition} method
 * that returns the directive's declarative metadata.
 */
interface DirectiveInterface
{
    public static function definition(): DirectiveDefinition;
}
