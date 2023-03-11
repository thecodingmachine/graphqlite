<?php

namespace TheCodingMachine\GraphQLite\Fixtures\Attributes;

use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Security;
use TheCodingMachine\GraphQLite\Annotations\SourceField;
use TheCodingMachine\GraphQLite\Annotations\Type;

#[SourceField(name: 'baz')]
class ParentType
{
}

#[Type(class: TestType::class)]
#[SourceField(name: 'foo')]
#[SourceField(name: 'bar')]
class TestType extends ParentType
{
    #[Field]
    #[Security('foo')]
    #[Security(expression: 'bar=42', failWith: null)]
    public function getField(): string
    {
        return '';
    }
}
