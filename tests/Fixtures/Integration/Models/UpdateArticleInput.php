<?php

namespace TheCodingMachine\GraphQLite\Fixtures\Integration\Models;

use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Input;
use TheCodingMachine\GraphQLite\Annotations\Security;
use TheCodingMachine\GraphQLite\Undefined;

#[Input]
class UpdateArticleInput
{
    /** @param list<string>|null $tags */
    public function __construct(
        #[Field]
        #[Security("magazine != 'NYTimes'")]
        public readonly string|null|Undefined $magazine = Undefined::VALUE,
        #[Field]
        public readonly string $summary = 'default',
        #[Field]
        public readonly array|null|Undefined $tags = Undefined::VALUE,
    )
    {
    }
}