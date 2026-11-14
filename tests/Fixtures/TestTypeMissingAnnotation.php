<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Tests\Fixtures;

use TheCodingMachine\GraphQLite\Annotations\SourceField;

#[SourceField(name: 'test')]
class TestTypeMissingAnnotation
{
}
