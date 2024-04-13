<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Annotations;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Mutation extends AbstractRequest
{
}
