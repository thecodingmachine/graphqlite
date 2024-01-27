<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Annotations;

use Attribute;

/**
 * @Annotation
 * @Target({"METHOD"})
 * @Attributes({
 *   @Attribute("outputType", type = "string"),
 * })
 */
#[Attribute(Attribute::TARGET_METHOD)]
class Subscription extends AbstractRequest
{
}
