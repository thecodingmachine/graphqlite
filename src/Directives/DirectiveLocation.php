<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Directives;

/**
 * GraphQL directive locations from the spec, both type-system and executable.
 *
 * Only FIELD_DEFINITION, INPUT_FIELD_DEFINITION, OBJECT and INPUT_OBJECT have apply hooks so far;
 * the rest are listed but not yet wired. Backing values match the spec strings, so they line up
 * with webonyx's location strings without conversion.
 */
enum DirectiveLocation: string
{
    // Executable locations
    case QUERY = 'QUERY';
    case MUTATION = 'MUTATION';
    case SUBSCRIPTION = 'SUBSCRIPTION';
    case FIELD = 'FIELD';
    case FRAGMENT_DEFINITION = 'FRAGMENT_DEFINITION';
    case FRAGMENT_SPREAD = 'FRAGMENT_SPREAD';
    case INLINE_FRAGMENT = 'INLINE_FRAGMENT';
    case VARIABLE_DEFINITION = 'VARIABLE_DEFINITION';

    // Type-system locations
    case SCHEMA = 'SCHEMA';
    case SCALAR = 'SCALAR';
    case OBJECT = 'OBJECT';
    case FIELD_DEFINITION = 'FIELD_DEFINITION';
    case ARGUMENT_DEFINITION = 'ARGUMENT_DEFINITION';
    case INTERFACE = 'INTERFACE';
    case UNION = 'UNION';
    case ENUM = 'ENUM';
    case ENUM_VALUE = 'ENUM_VALUE';
    case INPUT_OBJECT = 'INPUT_OBJECT';
    case INPUT_FIELD_DEFINITION = 'INPUT_FIELD_DEFINITION';

    public function isExecutable(): bool
    {
        return match ($this) {
            self::QUERY, self::MUTATION, self::SUBSCRIPTION, self::FIELD,
            self::FRAGMENT_DEFINITION, self::FRAGMENT_SPREAD, self::INLINE_FRAGMENT,
            self::VARIABLE_DEFINITION => true,
            default => false,
        };
    }

    public function isTypeSystem(): bool
    {
        return ! $this->isExecutable();
    }
}
