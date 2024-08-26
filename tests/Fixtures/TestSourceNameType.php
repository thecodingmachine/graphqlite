<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures;

use TheCodingMachine\GraphQLite\Annotations\MagicField;
use TheCodingMachine\GraphQLite\Annotations\SourceField;
use TheCodingMachine\GraphQLite\Annotations\Type;

#[Type(class: TestSourceName::class)]
#[MagicField(name: 'foo2', outputType: 'String!', sourceName: 'foo')]
#[SourceField(name: 'bar2', sourceName: 'bar')]
class TestSourceNameType
{
}
