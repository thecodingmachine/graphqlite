<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\DirectivesIntegration\Inputs;

use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Input;
use TheCodingMachine\GraphQLite\Fixtures\DirectivesIntegration\Directives\SanitizedDirective;
use TheCodingMachine\GraphQLite\Fixtures\DirectivesIntegration\Directives\VersionedDirective;

#[Input]
#[VersionedDirective(version: 2)]
final class WidgetLookup
{
    public function __construct(
        #[Field]
        #[SanitizedDirective]
        public string|null $sku = null,
        #[Field]
        public int|null $id = null,
    ) {
    }
}
