<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\Integration\Models;

use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Input;
use TheCodingMachine\GraphQLite\Annotations\Security;
use TheCodingMachine\GraphQLite\Undefined;

#[Input]
class UpdateArticleInput
{
    /** @param list<string>|Undefined|null $tags */
    public function __construct(
        #[Field]
        #[Security("magazine != 'NYTimes'")]
        public readonly string|Undefined|null $magazine = Undefined::VALUE,
        #[Field]
        public readonly string $summary = 'default',
        #[Field]
        public readonly array|Undefined|null $tags = Undefined::VALUE,
    )
    {
    }
}
