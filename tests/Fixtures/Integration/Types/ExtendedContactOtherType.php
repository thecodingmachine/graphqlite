<?php


namespace TheCodingMachine\GraphQLite\Fixtures\Integration\Types;

use function array_search;
use function strtoupper;
use TheCodingMachine\GraphQLite\Annotations\ExtendType;
use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\SourceField;
use TheCodingMachine\GraphQLite\Annotations\Type;
use TheCodingMachine\GraphQLite\Fixtures\Integration\Models\Contact;

/**
 * @ExtendType(name="ContactOther")
 * @SourceField(name="name", phpType="string")
 */
class ExtendedContactOtherType
{
    /**
     * @Field()
     */
    public function phone(Contact $contact): string
    {
        return "0123456789";
    }
}
