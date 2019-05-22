<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Annotations;

/**
 * @Annotation
 * @Target({"METHOD", "ANNOTATION"})
 */
class Logged implements MiddlewareAnnotationInterface
{
}
