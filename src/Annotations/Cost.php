<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Annotations;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD)]
class Cost implements MiddlewareAnnotationInterface
{
    /**
     * @param int $complexity Complexity for that field
     * @param string[] $multipliers Names of fields by value of which complexity will be multiplied
     * @param ?int $defaultMultiplier Default multiplier value if all multipliers are missing/null
     */
    public function __construct(
        public readonly int $complexity = 1,
        public readonly array $multipliers = [],
        public readonly int|null $defaultMultiplier = null,
    ) {
    }
}
