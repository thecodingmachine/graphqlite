<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\DirectivesIntegration\Directives;

use Attribute;
use GraphQL\Type\Definition\FieldDefinition;
use TheCodingMachine\GraphQLite\Directives\BehavioralFieldDirective;
use TheCodingMachine\GraphQLite\Directives\DirectiveDefinition;
use TheCodingMachine\GraphQLite\Directives\DirectiveLocation;
use TheCodingMachine\GraphQLite\Middlewares\FieldHandlerInterface;
use TheCodingMachine\GraphQLite\QueryFieldDescriptor;

use function is_string;
use function strtoupper;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PROPERTY)]
final class UppercaseDirective implements BehavioralFieldDirective
{
    public static function definition(): DirectiveDefinition
    {
        return new DirectiveDefinition(
            name: 'uppercase',
            locations: [DirectiveLocation::FIELD_DEFINITION],
        );
    }

    public function applyToField(QueryFieldDescriptor $descriptor, FieldHandlerInterface $next): FieldDefinition|null
    {
        $resolver = $descriptor->getResolver();
        $descriptor = $descriptor->withResolver(static function (...$args) use ($resolver): mixed {
            $value = $resolver(...$args);
            return is_string($value) ? strtoupper($value) : $value;
        });

        return $next->handle($descriptor);
    }
}
