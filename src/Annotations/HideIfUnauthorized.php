<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Annotations;

/**
 * Fields/Queries/Mutations annotated with this annotation will be hidden from the schema if the user is not logged
 * or has no right associated.
 *
 * @Annotation
 * @Target({"METHOD", "ANNOTATION"})
 */
class HideIfUnauthorized implements MiddlewareAnnotationInterface
{
}
