<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\DirectivesIntegration\Controllers;

use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\GraphQLite\Fixtures\DirectivesIntegration\Directives\UppercaseDirective;
use TheCodingMachine\GraphQLite\Fixtures\DirectivesIntegration\Inputs\OneOfLookup;
use TheCodingMachine\GraphQLite\Fixtures\DirectivesIntegration\Inputs\WidgetLookup;
use TheCodingMachine\GraphQLite\Fixtures\DirectivesIntegration\Models\Widget;

final class WidgetController
{
    #[Query]
    #[UppercaseDirective]
    public function tagline(): string
    {
        return 'hello world';
    }

    #[Query]
    public function findWidget(WidgetLookup $lookup): Widget
    {
        return new Widget($lookup->sku ?? ('id-' . ($lookup->id ?? 0)));
    }

    #[Query]
    public function findOneOf(OneOfLookup $lookup): Widget
    {
        return new Widget($lookup->sku ?? ('id-' . ($lookup->id ?? 0)));
    }
}
