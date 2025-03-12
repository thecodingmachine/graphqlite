<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures;

class TestObjectWithDeprecatedField
{

    /** @deprecated this is deprecated */
    public function getDeprecatedField(): string
    {
        return 'deprecatedField';
    }

    public function getName(): string
    {
        return 'Foo';
    }

}
