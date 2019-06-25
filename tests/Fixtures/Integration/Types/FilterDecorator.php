<?php


namespace TheCodingMachine\GraphQLite\Fixtures\Integration\Types;


use TheCodingMachine\GraphQLite\Annotations\Decorate;
use TheCodingMachine\GraphQLite\Annotations\Parameter;
use TheCodingMachine\GraphQLite\Annotations\UseInputType;
use TheCodingMachine\GraphQLite\Fixtures\Integration\Models\Filter;

class FilterDecorator
{

    /**
     * @Decorate(inputTypeName="FilterInput")
     * @param Filter $filter
     * @param int[] $moreValues
     * @return Filter
     */
    public function decorate(Filter $filter, array $moreValues = []): Filter
    {
        $filter->mergeValues($moreValues);
        return $filter;
    }

    /**
     * @Decorate(inputTypeName="FilterInput")
     * @param Filter $filter
     * @param int[] $evenMoreValues
     * @return Filter
     */
    public static function staticDecorate(Filter $filter, array $evenMoreValues = []): Filter
    {
        $filter->mergeValues($evenMoreValues);
        return $filter;
    }

    /**
     * @Decorate(inputTypeName="FilterInput")
     * @UseInputType(for="innerFilter", inputType="FilterInput")
     * @param Filter $filter
     * @param Filter|null $innerFilter
     * @return Filter
     */
    public static function recursiveDecorate(Filter $filter, ?Filter $innerFilter = null): Filter
    {
        return $filter;
    }
}
