<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Middlewares;

/**
 * Interface for resolving field value with source.
 */
interface SourceResolverInterface extends ResolverInterface
{
    /**
     * Set source object for which field value should be resolved.
     */
    public function setObject(object $object): void;
}
