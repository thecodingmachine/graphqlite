<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\Integration\Controllers;

use GraphQL\Type\Definition\ResolveInfo;
use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\GraphQLite\Fixtures\Integration\Models\Filter;

use function array_map;

class FilterController
{
    /** @return string[] */
    #[Query]
    public function echoFilters(Filter $filter): array
    {
        return array_map(static function ($item) {
            return (string) $item;
        }, $filter->getValues());
    }

    /** @return string[]|null */
    #[Query]
    public function echoNullableFilters(Filter|null $filter): array|null
    {
        if ($filter === null) {
            return null;
        }
        return $this->echoFilters($filter);
    }

    #[Query]
    public function echoResolveInfo(ResolveInfo $info): string
    {
        return $info->fieldName;
    }
}
