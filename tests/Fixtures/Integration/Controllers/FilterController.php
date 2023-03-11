<?php

namespace TheCodingMachine\GraphQLite\Fixtures\Integration\Controllers;

use GraphQL\Type\Definition\ResolveInfo;
use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\GraphQLite\Fixtures\Integration\Models\Filter;

use function array_map;

class FilterController
{
    /**
     * @Query()
     * @return string[]
     */
    public function echoFilters(Filter $filter): array
    {
        return array_map(static function ($item) {
            return (string) $item;
        }, $filter->getValues());
    }

    /**
     * @Query()
     * @return string[]|null
     */
    public function echoNullableFilters(?Filter $filter): ?array
    {
        if ($filter === null) {
            return null;
        }

        return $this->echoFilters($filter);
    }

    /**
     * @Query()
     */
    public function echoResolveInfo(ResolveInfo $info): string
    {
        return $info->fieldName;
    }
}
