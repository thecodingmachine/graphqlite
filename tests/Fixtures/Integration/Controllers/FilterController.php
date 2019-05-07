<?php


namespace TheCodingMachine\GraphQLite\Fixtures\Integration\Controllers;


use GraphQL\Type\Definition\ResolveInfo;
use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\GraphQLite\Fixtures\Integration\Models\Filter;

class FilterController
{
    /**
     * @Query()
     * @return string[]
     */
    public function echoFilters(Filter $filter): array
    {
        return $filter->getValues();
    }

    /**
     * @Query()
     * @return string
     */
    public function echoResolveInfo(ResolveInfo $info): string
    {
        return $info->fieldName;
    }
}
