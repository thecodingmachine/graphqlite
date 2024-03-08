<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Middlewares;

use GraphQL\Type\Definition\FieldDefinition;
use TheCodingMachine\GraphQLite\Annotations\Cost;
use TheCodingMachine\GraphQLite\QueryFieldDescriptor;

use function implode;
use function is_int;

/**
 * Reference implementation: https://github.com/ChilliCream/graphql-platform/blob/388f5c988bbb806e46e2315f1844ea5bb63096f2/src/HotChocolate/Core/src/Execution/Options/ComplexityAnalyzerSettings.cs#L58
 */
class CostFieldMiddleware implements FieldMiddlewareInterface
{
    public function process(QueryFieldDescriptor $queryFieldDescriptor, FieldHandlerInterface $fieldHandler): FieldDefinition|null
    {
        $costAttribute = $queryFieldDescriptor->getMiddlewareAnnotations()->getAnnotationByType(Cost::class);

        if (! $costAttribute) {
            return $fieldHandler->handle($queryFieldDescriptor);
        }

        $field = $fieldHandler->handle(
            $queryFieldDescriptor->withAddedCommentLines($this->buildQueryComment($costAttribute)),
        );

        if (! $field) {
            return $field;
        }

        $field->complexityFn = static function (int $childrenComplexity, array $fieldArguments) use ($costAttribute): int {
            if (! $costAttribute->multipliers) {
                return $costAttribute->complexity + $childrenComplexity;
            }

            $cost = $costAttribute->complexity + $childrenComplexity;
            $needsDefaultMultiplier = true;

            foreach ($costAttribute->multipliers as $multiplier) {
                $value = $fieldArguments[$multiplier] ?? null;

                if (! is_int($value)) {
                    continue;
                }

                $cost *= $value;
                $needsDefaultMultiplier = false;
            }

            if ($needsDefaultMultiplier && $costAttribute->defaultMultiplier !== null) {
                $cost *= $costAttribute->defaultMultiplier;
            }

            return $cost;
        };

        return $field;
    }

    private function buildQueryComment(Cost $costAttribute): string
    {
        return "\nCost: " .
            implode(', ', [
                'complexity = ' . $costAttribute->complexity,
                'multipliers = [' . implode(', ', $costAttribute->multipliers) . ']',
                'defaultMultiplier = ' . ($costAttribute->defaultMultiplier ?? 'null'),
            ]);
    }
}
