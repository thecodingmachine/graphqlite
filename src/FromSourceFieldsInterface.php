<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite;

use TheCodingMachine\GraphQLite\Annotations\SourceFieldInterface;

/**
 * This interface exposes dynamically a list of SourceFields.
 * It can be used as an alternative to the SourceField annotation when the list of source fields is dynamic.
 *
 * Note: whenever possible, it is advised to NOT use this interface and instead rely on the SourceField annotation.
 */
interface FromSourceFieldsInterface
{
    /**
     * Dynamically returns the array of source fields to be fetched from the original object.
     *
     * @return SourceFieldInterface[]
     */
    public function getSourceFields(): array;
}
