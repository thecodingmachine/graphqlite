<?php

namespace TheCodingMachine\GraphQLite\Fixtures\Integration\Models;

use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Input;
use TheCodingMachine\GraphQLite\Annotations\Security;

#[Input]
class UpdateArticleInput
{
    public function __construct(
        #[Field]
        #[Security("magazine != 'NYTimes'")]
        public readonly string|null $magazine,
        public readonly string $summary = 'default',
    )
    {
    }
}