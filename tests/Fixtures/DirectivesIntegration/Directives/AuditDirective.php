<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\DirectivesIntegration\Directives;

use Attribute;
use TheCodingMachine\GraphQLite\Directives\DirectiveDefinition;
use TheCodingMachine\GraphQLite\Directives\DirectiveLocation;
use TheCodingMachine\GraphQLite\Directives\FieldDirective;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
final class AuditDirective implements FieldDirective
{
    public function __construct(public readonly string $reason)
    {
    }

    public static function definition(): DirectiveDefinition
    {
        return new DirectiveDefinition(
            name: 'audit',
            locations: [DirectiveLocation::FIELD_DEFINITION],
            description: 'Marks a field for audit-log tracking.',
        );
    }
}
