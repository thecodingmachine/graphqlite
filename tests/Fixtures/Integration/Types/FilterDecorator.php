<?php

namespace TheCodingMachine\GraphQLite\Fixtures\Integration\Types;

use TheCodingMachine\GraphQLite\Annotations\Decorate;
use TheCodingMachine\GraphQLite\Annotations\UseInputType;
use TheCodingMachine\GraphQLite\Fixtures\Integration\Models\Filter;

class FilterDecorator
{
    /**
     * @Decorate(inputTypeName="FilterInput")
     * @param int[] $moreValues
     */
    public function decorate(Filter $filter, array $moreValues = []): Filter
    {
        $filter->mergeValues($moreValues);

        return $filter;
    }

    /**
     * @Decorate(inputTypeName="FilterInput")
     * @param int[] $evenMoreValues
     */
    public static function staticDecorate(Filter $filter, array $evenMoreValues = []): Filter
    {
        $filter->mergeValues($evenMoreValues);

        return $filter;
    }

    /**
     * @Decorate(inputTypeName="FilterInput")
     * @UseInputType(for="innerFilter", inputType="FilterInput")
     */
    public static function recursiveDecorate(Filter $filter, Filter $innerFilter = null): Filter
    {
        return $filter;
    }
}
