<?php


namespace TheCodingMachine\GraphQL\Controllers\Annotations;

/**
 * @Annotation
 * @Target({"METHOD"})
 * @Attributes({
 *   @Attribute("outputType", type = "string"),
 * })
 */
class Query extends AbstractRequest
{
}
