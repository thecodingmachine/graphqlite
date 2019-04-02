<?php


namespace TheCodingMachine\GraphQLite\Fixtures\Integration\Controllers;


use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\GraphQLite\Fixtures\Integration\Models\Filter;
use function var_export;

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
}
