<?php


namespace TheCodingMachine\GraphQLite\Fixtures\Integration\Types;


use TheCodingMachine\GraphQLite\Annotations\Decorate;
use TheCodingMachine\GraphQLite\Fixtures\Integration\Models\Filter;

class FilterDecorator
{

    /**
     * @Decorate(inputTypeName="FilterInput")
     * @param Filter $filter
     * @param int[] $moreValues
     * @return Filter
     */
    public function decorate(Filter $filter, array $moreValues): Filter
    {
        $filter->mergeValues($moreValues);
        return $filter;
    }
}
