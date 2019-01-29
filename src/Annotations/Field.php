<?php


namespace TheCodingMachine\GraphQLite\Annotations;

/**
 * @Annotation
 * @Target({"METHOD"})
 * @Attributes({
 *   @Attribute("outputType", type = "string"),
 * })
 */
class Field extends AbstractRequest
{
}
