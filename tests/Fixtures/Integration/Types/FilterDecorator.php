<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\Integration\Types;

use TheCodingMachine\GraphQLite\Annotations\Decorate;
use TheCodingMachine\GraphQLite\Annotations\UseInputType;
use TheCodingMachine\GraphQLite\Fixtures\Integration\Models\Filter;

class FilterDecorator
{
    /** @param int[] $moreValues */
    #[Decorate(inputTypeName: 'FilterInput')]
    public function decorate(Filter $filter, array $moreValues = []): Filter
    {
        $filter->mergeValues($moreValues);
        return $filter;
    }

    /** @param int[] $evenMoreValues */
    #[Decorate(inputTypeName: 'FilterInput')]
    public static function staticDecorate(Filter $filter, array $evenMoreValues = []): Filter
    {
        $filter->mergeValues($evenMoreValues);
        return $filter;
    }

    #[Decorate(inputTypeName: 'FilterInput')]
    public static function recursiveDecorate(
        Filter $filter,
        #[UseInputType(inputType: 'FilterInput')]
        Filter|null $innerFilter = null,
    ): Filter
    {
        return $filter;
    }
}
