<?php

namespace TheCodingMachine\GraphQLite\Fixtures\Integration\Controllers;

use TheCodingMachine\GraphQLite\Annotations\Query;

class DefaultValueController
{
    /**
     * @Query()
     */
    public function defaultValue(string $default = 'value'): string
    {
        return $default;
    }
}
