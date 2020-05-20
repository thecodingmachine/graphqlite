<?php

namespace TheCodingMachine\GraphQLite\Middlewares;

/**
 * Interface for resolving field value with source.
 */
interface SourceResolverInterface extends ResolverInterface
{

    /**
     * Set source object for which field value should be resolved.
     *
     * @param object $object
     */
    public function setObject(object $object): void;
}
