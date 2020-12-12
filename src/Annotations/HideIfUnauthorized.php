<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Annotations;

use Attribute;

/**
 * Fields/Queries/Mutations annotated with this annotation will be hidden from the schema if the user is not logged
 * or has no right associated.
 *
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
#[Attribute(Attribute::TARGET_METHOD)]
class HideIfUnauthorized implements MiddlewareAnnotationInterface
{
}
