<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Annotations;

/**
 * @Annotation
 * @Target({"METHOD"})
 * @Attributes({
 *   @Attribute("outputType", type = "string"),
 * })
 */
class Mutation extends AbstractRequest
{
}
