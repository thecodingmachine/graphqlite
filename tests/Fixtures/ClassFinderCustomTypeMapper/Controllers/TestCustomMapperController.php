<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\ClassFinderCustomTypeMapper\Controllers;

use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\GraphQLite\Fixtures\ClassFinderCustomTypeMapper\Types\TestCustomMapperObject;

/**
 * Pretty standard controller. It is separated from the other controllers to test discovering via added as
 * a separated independent mapper in Schema.
 */
final class TestCustomMapperController
{
    #[Query]
    public function getCustomMapperObject(): TestCustomMapperObject
    {
        return new TestCustomMapperObject();
    }
}
