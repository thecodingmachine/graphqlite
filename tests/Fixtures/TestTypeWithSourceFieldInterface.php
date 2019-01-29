<?php


namespace TheCodingMachine\GraphQLite\Fixtures;

use TheCodingMachine\GraphQLite\Annotations\SourceField;
use TheCodingMachine\GraphQLite\Annotations\SourceFieldInterface;
use TheCodingMachine\GraphQLite\Annotations\Type;
use TheCodingMachine\GraphQLite\FromSourceFieldsInterface;

/**
 * @Type(class=TestObject::class)
 */
class TestTypeWithSourceFieldInterface implements FromSourceFieldsInterface
{
    /**
     * Dynamically returns the array of source fields to be fetched from the original object.
     *
     * @return SourceFieldInterface[]
     */
    public function getSourceFields(): array
    {
        return [
            new SourceField(['name'=>'test']),
        ];
    }
}
